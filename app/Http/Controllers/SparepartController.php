<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Assembly;
use App\Models\Sparepart;

class SparepartController extends Controller
{
    public function index(Request $request)
    {
        $machines = Machine::all();
        
        // Memulai query Sparepart
        $query = Sparepart::query();

        // 1. Filter dari Text Input (Part Name atau SAP)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('sap_code', 'like', "%{$search}%");
            });
        }

        // 2. Filter berdasarkan Assembly Name
        if ($request->filled('assembly_id')) {
            $query->whereHas('assemblies', function($q) use ($request) {
                $q->where('assemblies.id', $request->assembly_id);
            });
        } 
        // 3. Filter berdasarkan Machine Name (jika mesin dipilih, tapi assembly belum)
        elseif ($request->filled('machine_id')) {
            $query->whereHas('assemblies.machine', function($q) use ($request) {
                $q->where('machines.id', $request->machine_id);
            });
        }

        // Ambil data beserta lokasi (inventory) dan assembly-nya
        $spareparts = $query->with(['assemblies'])->paginate(12);

        // Pertahankan input pencarian lama
        $filters = $request->only(['search', 'machine_id', 'assembly_id']);

        return view('sparepart-catalog', compact('machines', 'spareparts', 'filters'));
    }

    // Fungsi API untuk mengambil daftar Assembly saat Mesin dipilih (Dropdown Dinamis)
    public function getAssemblies($machineId)
    {
        $assemblies = Assembly::where('machine_id', $machineId)->get();
        return response()->json($assemblies);
    }
}