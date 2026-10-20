<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblySparepart;
use App\Models\Machine;
use App\Models\SapHistory;
use App\Models\Sparepart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- 1. Tambahkan ini
use PhpOffice\PhpSpreadsheet\IOFactory;         // <-- 2. Tambahkan ini

class ImportController extends Controller
{
    public function index()
    {
        return view('import-data');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls',
        ]);

        try {
            // Kita gunakan Database Transaction. Jika ada 1 sheet gagal, semua akan dibatalkan (aman)
            DB::beginTransaction();

            $spreadsheet = IOFactory::load($request->file('file_excel')->getPathname());

            // 1. Import Sheet: Machine_Master
            $machineSheet = $spreadsheet->getSheetByName('Machine_Master');
            if ($machineSheet) {
                $rows = $machineSheet->toArray(null, true, true, true);
                foreach ($rows as $index => $row) {
                    if ($index == 1 || empty($row['A'])) {
                        continue;
                    } // Lewati header atau baris kosong
                    Machine::updateOrCreate(
                        ['machine_code' => $row['A']], // Cari berdasarkan ID
                        ['machine_name' => $row['B']]
                    );
                }
            }

            // 2. Import Sheet: Assembly_Master
            $assemblySheet = $spreadsheet->getSheetByName('Assembly_Master');
            if ($assemblySheet) {
                $rows = $assemblySheet->toArray(null, true, true, true);
                foreach ($rows as $index => $row) {
                    if ($index == 1 || empty($row['A'])) {
                        continue;
                    }
                    $machine = Machine::where('machine_code', $row['C'])->first();
                    if ($machine) {
                        Assembly::updateOrCreate(
                            ['assembly_code' => $row['A']],
                            ['assembly_name' => $row['B'], 'machine_id' => $machine->id]
                        );
                    }
                }
            }

            // 3. Import Sheet: Sparepart_Master
            $sparepartSheet = $spreadsheet->getSheetByName('Sparepart_Master');
            if ($sparepartSheet) {
                $rows = $sparepartSheet->toArray(null, true, true, true);
                foreach ($rows as $index => $row) {
                    if ($index == 1 || empty($row['A'])) {
                        continue;
                    }

                    Sparepart::updateOrCreate(
                        ['part_id' => trim($row['A'])], // Gunakan trim agar kebal spasi
                        [
                            'sap_code' => trim($row['B']),
                            'part_name' => trim($row['C']),
                            'bin_location' => isset($row['D']) ? trim($row['D']) : null, // Gunakan trim
                        ]
                    );
                }
            }

            // 4. Import Sheet: Assembly_BOM
            $bomSheet = $spreadsheet->getSheetByName('Assembly_BOM');
            if ($bomSheet) {
                // KOSONGKAN TABEL RELASI LAMA SEBELUM IMPORT YANG BARU
                // (Gunakan truncate atau delete agar data lama yang sudah tidak ada di excel ikut terhapus)
                AssemblySparepart::query()->delete();

                $rows = $bomSheet->toArray(null, true, true, true);
                foreach ($rows as $index => $row) {
                    if ($index == 1 || empty($row['A']) || empty($row['D'])) {
                        continue;
                    }

                    $assemblyCode = trim($row['A']);
                    $partId = trim($row['D']);

                    // PERBAIKAN KECIL: Gunakan variabel $assemblyCode & $partId yang sudah di-trim agar pencarian akurat
                    $assembly = Assembly::where('assembly_code', $assemblyCode)->first();
                    $sparepart = Sparepart::where('part_id', $partId)->first();

                    if ($assembly && $sparepart) {
                        // Karena tabel sudah dikosongkan dengan truncate, kita bisa langsung create atau tetap pakai updateorticreate
                        AssemblySparepart::create([
                            'assembly_id' => $assembly->id,
                            'sparepart_id' => $sparepart->id,
                            'qty' => $row['E'],
                            'unit' => $row['F'] ?? 'pc',
                            'description' => $row['G'] ?? null,
                        ]);
                    }
                }
            }

            // Catatan: Sheet 'Inventory' sudah ditiadakan karena Bin sudah digabung ke Sparepart_Master.

            // Jika semua lancar, simpan ke database permanen
            DB::commit();

            return back()->with('success', 'Selamat! Seluruh data Excel berhasil masuk ke Database.');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika ada error

            return back()->withErrors(['file_excel' => 'Gagal memproses data: '.$e->getMessage()]);
        }
    }

    // ---------------------------------------------------------
    // 2. IMPORT CSV HISTORY SAP (Khusus format CSV UTF-8)
    // ---------------------------------------------------------
    public function importSap(Request $request)
    {
        // Cegah timeout dan kehabisan memori untuk ribuan baris
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '-1');

        // Validasi file (longgarkan mimes agar aman di Windows)
        $request->validate([
            'file' => 'required',
        ]);

        $file = $request->file('file');

        // Cek manual ekstensi
        if (strtolower($file->getClientOriginalExtension()) !== 'csv') {
            return back()->with('error', 'Gagal! Pastikan file yang di-upload berakhiran .csv');
        }

        $fileHandle = fopen($file->getPathname(), 'r');

        // Deteksi delimiter otomatis (koma atau titik koma)
        $firstLine = fgets($fileHandle);
        $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
        rewind($fileHandle);

        fgetcsv($fileHandle, 10000, $delimiter); // Lewati baris header

        $dataToInsert = [];

        // Fungsi helper untuk membersihkan spasi berlebih
        $cleanText = function ($val) {
            if ($val === null || $val === '') {
                return null;
            }

            return trim($val);
        };

        // Fungsi helper untuk membersihkan angka (ubah koma desimal jadi titik)
        $cleanNumber = function ($val) {
            $val = trim($val ?? '');
            if ($val === '') {
                return null;
            }

            return str_replace(',', '.', $val);
        };

        while (($row = fgetcsv($fileHandle, 10000, $delimiter)) !== false) {
            // Lewati jika baris tersebut benar-benar kosong
            if (count(array_filter($row)) === 0) {
                continue;
            }

            // Parsing tanggal (aman jika formatnya berantakan)
            $dateParsed = null;
            if (! empty($row[2])) {
                try {
                    $dateParsed = Carbon::parse($row[2])->format('Y-m-d');
                } catch (\Exception $e) {
                    $dateParsed = null;
                }
            }

            $dataToInsert[] = [
                'month' => $cleanText($row[0] ?? null),
                'week' => $cleanText($row[1] ?? null),
                'date' => $dateParsed,
                'cat' => $cleanText($row[3] ?? null),
                'type' => $cleanText($row[4] ?? null),
                'plant' => $cleanText($row[5] ?? null),
                'area' => $cleanText($row[6] ?? null),
                'dept' => $cleanText($row[7] ?? null),
                'functional_location' => $cleanText($row[8] ?? null),
                'indicator' => $cleanText($row[9] ?? null),
                'maint_order' => $cleanText($row[10] ?? null),
                'maint_order_desc' => $cleanText($row[11] ?? null),
                'code_cause' => $cleanText($row[12] ?? null),
                'error_code' => $cleanText($row[13] ?? null),
                'creator' => $cleanText($row[14] ?? null),
                'executor' => $cleanText($row[15] ?? null),
                'code_group' => $cleanText($row[16] ?? null),
                'object_part' => $cleanText($row[17] ?? null),
                'problem' => $cleanText($row[18] ?? null),
                'activity' => $cleanText($row[19] ?? null),
                'status' => $cleanText($row[20] ?? null),
                'confirmation_text' => $cleanText($row[21] ?? null),

                // Casting data angka
                'notfis' => is_numeric($cleanNumber($row[22] ?? '')) ? (int) $cleanNumber($row[22]) : null,
                'response_hrs' => is_numeric($cleanNumber($row[23] ?? '')) ? (float) $cleanNumber($row[23]) : null,
                'actual_hrs' => is_numeric($cleanNumber($row[24] ?? '')) ? (float) $cleanNumber($row[24]) : null,
                'bm_hrs' => is_numeric($cleanNumber($row[25] ?? '')) ? (float) $cleanNumber($row[25]) : null,

                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }
        fclose($fileHandle);

        try {
            // Pecah data per 250 baris agar ringan masuk ke database
            $chunks = array_chunk($dataToInsert, 250);
            foreach ($chunks as $chunk) {
                SapHistory::insert($chunk);
            }

            return back()->with('success', count($dataToInsert).' Baris Data History SAP berhasil di-import!');

        } catch (\Throwable $e) {
            // Menangkap semua bentuk error MySQL
            return back()->with('error', 'Terjadi error saat menyimpan ke MySQL: '.$e->getMessage());
        }
    }
}
