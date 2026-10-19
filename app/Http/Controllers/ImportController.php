<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use App\Models\Machine;
use App\Models\Assembly;
use App\Models\Sparepart;
use App\Models\AssemblySparepart;

class ImportController extends Controller
{
    public function index()
    {
        return view('import-data');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
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
                    if ($index == 1 || empty($row['A'])) continue; // Lewati header atau baris kosong
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
                    if ($index == 1 || empty($row['A'])) continue;
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
                    if ($index == 1 || empty($row['A'])) continue;
                    
                    Sparepart::updateOrCreate(
                        ['part_id' => trim($row['A'])], // Gunakan trim agar kebal spasi
                        [
                            'sap_code' => trim($row['B']), 
                            'part_name' => trim($row['C']),
                            'bin_location' => isset($row['D']) ? trim($row['D']) : null // Gunakan trim
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
                    if ($index == 1 || empty($row['A']) || empty($row['D'])) continue;
                    
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
                            'description' => $row['G'] ?? null
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
            return back()->withErrors(['file_excel' => 'Gagal memproses data: ' . $e->getMessage()]);
        }
    }
}