<?php

namespace App\Http\Controllers;

use App\Models\Handover;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HandoverController extends Controller
{
    // 1. Tampilkan riwayat Handover (OPEN di atas)
    public function index()
    {
        $handovers = Handover::orderByRaw("FIELD(status, 'OPEN', 'CLOSED')")
            ->orderBy('handover_date', 'desc')
            ->orderBy('shift', 'desc')
            ->get();

        return view('handovers.index', compact('handovers'));
    }

    // 2. Halaman Input Handover Baru
    public function create()
    {
        return view('handovers.create');
    }

    // / 3. Simpan Data Handover Baru (Ketik Manual)
    public function store(Request $request)
    {
        $request->validate([
            'handover_date' => 'required|date',
            'shift' => 'required|in:1,2,3',
            'team' => 'required|in:A,B,C,D', // Tambahan validasi tim
            'machine_name' => 'required|string|max:255',
            'problem_description' => 'required|string',
        ]);

        Handover::create([
            'handover_date' => $request->handover_date,
            'shift' => $request->shift,
            'team' => $request->team, // Simpan data tim
            'machine_name' => strtoupper(trim($request->machine_name)),
            'problem_description' => $request->problem_description,
            'status' => 'OPEN',
            'reported_by' => auth()->user()->name ?? 'Teknisi',
        ]);

        return redirect()->route('handover.index')->with('success', 'Laporan Handover berhasil ditambahkan!');
    }

    // 4. Konfirmasi Problem Selesai
    public function resolve($id)
    {
        $handover = Handover::findOrFail($id);

        $handover->update([
            'status' => 'CLOSED',
            'resolved_by' => auth()->user()->name ?? 'Teknisi Shift Lanjutan',
            'resolved_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Problem mesin berhasil dikonfirmasi SELESAI!');
    }
}
