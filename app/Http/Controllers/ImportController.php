<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use App\Models\Machine;
use App\Models\Assembly;
use App\Models\Sparepart;
use App\Models\AssemblySparepart;
use App\Models\Inventory;

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
                        ['part_id' => $row['A']],
                        ['sap_code' => $row['B'], 'part_name' => $row['C']]
                    );
                }
            }

            // 4. Import Sheet: Assembly_BOM
            $bomSheet = $spreadsheet->getSheetByName('Assembly_BOM');
            if ($bomSheet) {
                $rows = $bomSheet->toArray(null, true, true, true);
                foreach ($rows as $index => $row) {
                    if ($index == 1 || empty($row['A']) || empty($row['D'])) continue;
                    $assembly = Assembly::where('assembly_code', $row['A'])->first();
                    $sparepart = Sparepart::where('part_id', $row['D'])->first();

                    if ($assembly && $sparepart) {
                        AssemblySparepart::updateOrCreate(
                            ['assembly_id' => $assembly->id, 'sparepart_id' => $sparepart->id],
                            ['qty' => $row['E'], 'unit' => $row['F'] ?? 'pc']
                        );
                    }
                }
            }

            // 5. Import Sheet: Inventory
            $inventorySheet = $spreadsheet->getSheetByName('Inventory');
            if ($inventorySheet) {
                $rows = $inventorySheet->toArray(null, true, true, true);
                foreach ($rows as $index => $row) {
                    if ($index == 1 || empty($row['B'])) continue;
                    $sparepart = Sparepart::where('part_id', $row['B'])->first();
                    if ($sparepart) {
                        Inventory::updateOrCreate(
                            ['sparepart_id' => $sparepart->id, 'bin_location' => $row['C']]
                        );
                    }
                }
            }

            // Jika semua lancar, simpan ke database permanen
            DB::commit();
            return back()->with('success', 'Selamat! Seluruh data Excel berhasil masuk ke Database.');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika ada error
            return back()->withErrors(['file_excel' => 'Gagal memproses data: ' . $e->getMessage()]);
        }
    }
}