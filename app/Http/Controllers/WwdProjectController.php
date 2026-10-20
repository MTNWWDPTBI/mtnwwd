<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineUnit;
use App\Models\WwdProject;
use App\Models\WwdProjectDetail;
use Illuminate\Http\Request;

class WwdProjectController extends Controller
{
    // 1. Menampilkan Daftar Project (Dashboard Project)
    public function index()
    {
        // Ambil semua project beserta jumlah detailnya untuk progress bar
        $projects = WwdProject::withCount([
            'details as total_machines',
            'details as closed_machines' => function ($query) {
                $query->where('status', 'CLOSE');
            },
        ])->latest()->get();

        return view('wwd-projects.index', compact('projects'));
    }

    // 2. Menampilkan Form Buat Project Baru
    public function create()
    {
        $machines = Machine::all(); // Untuk dropdown pilihan tipe mesin (NDE, SHX, dll)

        return view('wwd-projects.create', compact('machines'));
    }

    // 3. Menyimpan Project & Generate Checklist Mesin Otomatis
    public function store(Request $request)
    {
        // 1. Ubah validasi machine_id menjadi 'array'
        $request->validate([
            'project_name' => 'required|string|max:255',
            'machine_id' => 'required|array|min:1', // Harus berupa array dan minimal pilih 1
            'machine_id.*' => 'exists:machines,id',
            'column_headers' => 'required|array',
        ]);

        $project = WwdProject::create([
            'project_name' => $request->project_name,
            'description' => $request->description,
            'pic_leader' => $request->pic_leader,
            'target_date' => $request->target_date,
            'column_headers' => $request->column_headers,
            'status' => 'OPEN',
        ]);

        // 2. UBAH BAGIAN INI: Gunakan whereIn untuk mencari banyak ID mesin sekaligus
        $machineUnits = MachineUnit::whereIn('machine_id', $request->machine_id)
            ->where('status', 'ACTIVE')
            ->get();

        $emptyDynamicData = array_fill_keys($request->column_headers, null);

        $detailsData = [];
        foreach ($machineUnits as $unit) {
            $detailsData[] = [
                'wwd_project_id' => $project->id,
                'machine_unit_id' => $unit->id,
                'dynamic_data' => json_encode($emptyDynamicData),
                'status' => 'OPEN',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        WwdProjectDetail::insert($detailsData);

        return redirect()->route('wwd-projects.index')
            ->with('success', 'Project berhasil dibuat! '.count($detailsData).' checklist mesin telah di-generate.');
    }

    // 4. Menampilkan Detail Project & Tabel Laporan (Excel-like)
    public function show($id)
    {
        $project = WwdProject::with(['details.machineUnit.machine'])->findOrFail($id);

        return view('wwd-projects.show', compact('project'));
    }

    // 5. Update Laporan oleh Teknisi (via HP)
    public function updateDetail(Request $request, $id)
    {
        $detail = WwdProjectDetail::findOrFail($id);

        // Simpan jawaban teknisi ke dalam JSON
        $detail->update([
            'dynamic_data' => $request->dynamic_data,
            'status' => $request->status ?? 'OPEN',
        ]);

        return back()->with('success', 'Data mesin '.$detail->machineUnit->machine_number.' berhasil diupdate!');
    }
}
