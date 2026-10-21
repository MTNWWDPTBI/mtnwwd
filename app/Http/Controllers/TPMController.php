<?php

namespace App\Http\Controllers;

use App\Models\TpmPart;
use App\Models\TpmTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TPMController extends Controller
{
    /**
     * Halaman Utama TPM (Untuk melihat daftar barang & stok per lokasi).
     */
    public function index(Request $request): View
    {
        $selectedLocation = $request->query('location');
        $search = $request->query('search');

        // Ambil semua lokasi unik untuk tab/pilihan filter
        $locations = TpmPart::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        // Hitung jumlah part per lokasi untuk badge angka
        $locationCounts = TpmPart::whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location', DB::raw('count(*) as total'))
            ->groupBy('location')
            ->pluck('total', 'location');

        $totalParts = TpmPart::count();

        $query = TpmPart::query();

        if ($request->filled('location')) {
            $query->where('location', $selectedLocation);
        }

        if ($request->filled('search')) {
            $trimmed = trim($search);
            $query->where(function ($q) use ($trimmed) {
                $q->where('name', 'like', "%{$trimmed}%")
                    ->orWhere('tpm_part_number', 'like', "%{$trimmed}%");
            });
        }

        $tpmParts = $query->orderBy('location')->orderBy('tpm_part_number')->get();

        return view('tpm.index', compact(
            'tpmParts',
            'locations',
            'locationCounts',
            'selectedLocation',
            'totalParts'
        ));
    }

    /**
     * Form Tambah Master Barang TPM Baru (Khusus Admin).
     */
    public function create(): View
    {
        return view('tpm.create');
    }

    /**
     * Proses Simpan Barang Baru & Generate Kode 999x Otomatis.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        $latestPart = TpmPart::orderBy('id', 'desc')->first();
        if ($latestPart && str_starts_with($latestPart->tpm_part_number, '999')) {
            $lastNumber = (int) substr($latestPart->tpm_part_number, 3);
            $newNumber = '999'.($lastNumber + 1);
        } else {
            $newNumber = '9991';
        }

        TpmPart::create([
            'tpm_part_number' => $newNumber,
            'name' => strtoupper(trim($request->name)),
            'location' => strtoupper(trim($request->location)),
            'stock' => 0,
            'min_stock' => $request->min_stock ?? 0,
            'max_stock' => $request->max_stock ?? 0,
        ]);

        return redirect()->route('tpm.index')->with('success', "Barang TPM baru berhasil didaftarkan dengan kode Barcode: {$newNumber}");
    }

    /**
     * Halaman khusus Teknisi untuk Eksekusi Stok (Scan Barcode / Pilih Manual per Lokasi & Part).
     */
    public function scan(Request $request): View
    {
        $locations = TpmPart::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $parts = TpmPart::orderBy('location')
            ->orderBy('name')
            ->get(['id', 'tpm_part_number', 'name', 'location', 'stock', 'min_stock', 'max_stock']);

        $preselectedPartNumber = $request->query('part_number');
        $preselectedLocation = $request->query('location');

        if ($preselectedPartNumber && ! $preselectedLocation) {
            $matchedPart = $parts->firstWhere('tpm_part_number', $preselectedPartNumber);
            if ($matchedPart) {
                $preselectedLocation = $matchedPart->location;
            }
        }

        return view('tpm.scan', compact(
            'locations',
            'parts',
            'preselectedPartNumber',
            'preselectedLocation'
        ));
    }

    /**
     * Proses Update Stok dari Scanner atau Pilihan Manual.
     */
    public function processScan(Request $request): RedirectResponse
    {
        $request->validate([
            'tpm_part_number' => 'nullable|string',
            'part_id' => 'nullable|integer',
            'transaction_type' => 'required|in:IN,OUT',
            'qty' => 'required|integer|min:1',
        ]);

        $part = null;

        if ($request->filled('part_id')) {
            $part = TpmPart::find($request->part_id);
        }

        if (! $part && $request->filled('tpm_part_number')) {
            $part = TpmPart::where('tpm_part_number', trim($request->tpm_part_number))->first();
        }

        if (! $part) {
            return back()->withInput()->with('error', 'GAGAL! Part TPM tidak ditemukan. Silakan pilih lokasi & part, atau scan ulang barcode.');
        }

        if ($request->transaction_type === 'OUT' && $part->stock < $request->qty) {
            return back()->withInput()->with('error', "GAGAL! Stok tidak mencukupi! Stok {$part->name} saat ini hanya sisa: {$part->stock}");
        }

        DB::transaction(function () use ($request, $part) {
            TpmTransaction::create([
                'tpm_part_id' => $part->id,
                'transaction_type' => $request->transaction_type,
                'qty' => $request->qty,
                'operator_name' => auth()->user()->name ?? 'Teknisi',
            ]);

            if ($request->transaction_type === 'IN') {
                $part->stock += $request->qty;
            } else {
                $part->stock -= $request->qty;
            }
            $part->save();
        });

        $status = $request->transaction_type === 'IN' ? 'ditambahkan' : 'diambil';

        return back()->with('success', "BERHASIL! Stok <strong>{$part->name}</strong> ({$part->tpm_part_number} - Lokasi {$part->location}) berhasil {$status} sebanyak <strong>{$request->qty}</strong>. (Sisa Stok Sekarang: <strong>{$part->stock}</strong>)");
    }

    /**
     * Hapus Barang TPM secara permanen.
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $part = TpmPart::findOrFail($id);
        $namaPart = $part->name;

        $part->delete();

        return redirect()->route('tpm.index')->with('success', "Barang TPM '{$namaPart}' berhasil dihapus secara permanen.");
    }
}
