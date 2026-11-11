<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappHandoverJob;
use App\Models\Handover;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HandoverController extends Controller
{
    /**
     * Tampilkan halaman input problem shift dan 10 riwayat logbook terakhir.
     */
    public function index(): View
    {
        $handovers = Handover::orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('handover_date', 'desc')
            ->orderBy('shift', 'desc')
            ->take(10)
            ->get();

        return view('handover.index', compact('handovers'));
    }

    /**
     * Simpan Data Problem Shift (Logbook).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'handover_date' => 'required|date',
            'shift' => 'required|string',
            'team' => 'nullable|string',
            'machine_name' => 'required|string',
            'problem_description' => 'required|string',
            'lototo_status' => 'required|in:Sudah,Belum',
        ]);

        $handover = Handover::create([
            'handover_date' => $validated['handover_date'],
            'shift' => $validated['shift'],
            'team' => $validated['team'] ?? null,
            'machine_name' => strtoupper(trim($validated['machine_name'])),
            'problem_description' => $validated['problem_description'],
            'lototo_status' => $validated['lototo_status'],
            'status' => 'OPEN',
            'reported_by' => auth()->user()?->name ?? 'Teknisi',
        ]);

        SendWhatsappHandoverJob::dispatch($handover);

        return redirect()->back(fallback: route('handover.index'))
            ->with('success', 'Data logbook berhasil disimpan & notifikasi WA terkirim!');
    }

    /**
     * Konfirmasi Problem Selesai.
     */
    public function resolve(int|string $id): RedirectResponse
    {
        $handover = Handover::findOrFail($id);

        $handover->update([
            'status' => 'CLOSED',
            'resolved_by' => auth()->user()?->name ?? 'Teknisi Shift Lanjutan',
            'resolved_at' => Carbon::now(),
        ]);

        return redirect()->back(fallback: route('handover.index'))
            ->with('success', 'Problem mesin berhasil dikonfirmasi SELESAI!');
    }

    /**
     * Hapus Laporan Problem Handover (Khusus Admin).
     */
    public function destroy(int|string $id): RedirectResponse
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Akses ditolak! Hanya Admin yang berhak menghapus laporan handover.');
        }

        $handover = Handover::findOrFail($id);
        $machineName = $handover->machine_name;
        $handover->delete();

        return redirect()->route('handover.index')
            ->with('success', "Laporan problem mesin '{$machineName}' berhasil dihapus!");
    }
}
