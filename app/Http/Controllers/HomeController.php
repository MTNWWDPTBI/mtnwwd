<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Sparepart;
use App\Models\Assembly;
use App\Models\TpmPart; // Pastikan Model TpmPart di-import di atas

class HomeController extends Controller
{
    public function index()
    {
        // Mengambil semua data mesin dengan hitungan assembly
        $machines = Machine::withCount('assemblies')->get();

        // Hitung jumlah sparepart yang terhubung per mesin
        foreach ($machines as $machine) {
            $machine->spareparts_count = Sparepart::whereHas('assemblies.machine', function ($q) use ($machine) {
                $q->where('machines.id', $machine->id);
            })->count();
        }

        // Statistik ringkasan untuk kartu metrik Material Design
        $stats = [
            'total_machines'    => $machines->count(),
            'total_assemblies'  => Assembly::count(),
            'total_spareparts'  => Sparepart::count(),
            'total_locations'   => Sparepart::whereNotNull('bin_location')->where('bin_location', '!=', '')->count(),
        ];

        // TAMBAHAN: Ambil barang TPM yang stoknya <= min_stock
        $lowStockTpmParts = TpmPart::whereColumn('stock', '<=', 'min_stock')->get();

        // Kirimkan $lowStockTpmParts ke view dashboard
        return view('dashboard', compact('stats', 'machines', 'lowStockTpmParts'));
    }
}