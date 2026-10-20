<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Sparepart;
use App\Models\Assembly;
use App\Models\TpmPart; 
use App\Models\WwdProject; 
use App\Models\Handover;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Mengambil semua data mesin dengan hitungan assembly
        $machines = Machine::withCount('assemblies')->get();

        // Hitung jumlah sparepart yang terhubung per mesin
        foreach ($machines as $machine) {
            $machine->spareparts_count = Sparepart::whereHas('assemblies.machine', function ($q) use ($machine) {
                $q->where('machines.id', $machine->id);
            })->count();
        }

        // 2. Statistik ringkasan untuk kartu metrik Material Design
        $stats = [
            'total_machines'    => $machines->count(),
            'total_assemblies'  => Assembly::count(),
            'total_spareparts'  => Sparepart::count(),
            'total_locations'   => Sparepart::whereNotNull('bin_location')->where('bin_location', '!=', '')->count(),
        ];

        // 3. Ambil barang TPM yang stoknya <= min_stock
        $lowStockTpmParts = TpmPart::whereColumn('stock', '<=', 'min_stock')->get();

        // 4. Ambil 4 project WWD terbaru beserta perhitungan progress-nya
        $wwdProjects = WwdProject::withCount([
            'details as total_machines',
            'details as closed_machines' => function ($query) {
                $query->where('status', 'CLOSE');
            }
        ])->latest()->take(4)->get();

        // 2. TAMBAHKAN KODE INI: Ambil 4 Handover terbaru yang masih OPEN
        $openHandovers = Handover::where('status', 'OPEN')->latest()->take(4)->get();

        // 3. JANGAN LUPA: Tambahkan 'openHandovers' ke dalam compact
        return view('dashboard', compact('stats', 'machines', 'lowStockTpmParts', 'wwdProjects', 'openHandovers'));
    }
    
    
    
}