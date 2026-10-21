<?php

namespace App\Http\Controllers;

use App\Models\Handover;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HandoverController extends Controller
{
    /**
     * Tampilkan riwayat Handover (OPEN di atas).
     */
    public function index(): View
    {
        $handovers = Handover::orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('handover_date', 'desc')
            ->orderBy('shift', 'desc')
            ->get();

        return view('handovers.index', compact('handovers'));
    }

    /**
     * Halaman Input Handover Baru.
     */
    public function create(): View
    {
        return view('handovers.create');
    }

    /**
     * Simpan Data Handover Baru (Ketik Manual).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'handover_date' => 'required|date',
            'shift' => 'required|in:1,2,3',
            'team' => 'required|in:A,B,C,D',
            'machine_name' => 'required|string|max:255',
            'problem_description' => 'required|string',
            'lototo_status' => 'nullable|in:Sudah,Belum',
        ]);

        $data = [
            'handover_date' => $request->handover_date,
            'shift' => $request->shift,
            'team' => $request->team,
            'machine_name' => strtoupper(trim($request->machine_name)),
            'problem_description' => $request->problem_description,
            'status' => 'OPEN',
            'reported_by' => auth()->user()->name ?? 'Teknisi',
        ];

        // Pengaman: Hanya sertakan lototo_status jika kolomnya memang sudah ada di database
        if (Schema::hasColumn('handovers', 'lototo_status')) {
            $data['lototo_status'] = $request->lototo_status ?? 'Belum';
        }

        Handover::create($data);

        return redirect()->route('handover.index')->with('success', 'Laporan Handover berhasil ditambahkan!');
    }

    /**
     * Konfirmasi Problem Selesai.
     */
    public function resolve(int|string $id): RedirectResponse
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
