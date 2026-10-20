<?php

namespace App\Http\Controllers;

use App\Models\TpmPart;
use App\Models\TpmTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TPMController extends Controller
{
    // 1. Halaman Utama TPM (Untuk melihat daftar barang & stok)
    public function index()
    {
        $tpmParts = TpmPart::orderBy('tpm_part_number')->get();

        return view('tpm.index', compact('tpmParts'));
    }

    // 2. Form Tambah Master Barang TPM Baru (Khusus Admin)
    public function create()
    {
        return view('tpm.create');
    }

    // 3. Proses Simpan Barang Baru & Generate Kode 999x Otomatis
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:50',
        ]);

        // Logika cerdas untuk Generate Kode 9991, 9992, dst.
        $latestPart = TpmPart::orderBy('id', 'desc')->first();
        if ($latestPart && str_starts_with($latestPart->tpm_part_number, '999')) {
            // Ambil angka setelah '999' dan tambah 1
            $lastNumber = (int) substr($latestPart->tpm_part_number, 3);
            $newNumber = '999'.($lastNumber + 1);
        } else {
            $newNumber = '9991'; // Awalan jika tabel masih kosong
        }

        TpmPart::create([
            'tpm_part_number' => $newNumber,
            'name' => strtoupper(trim($request->name)),
            'location' => strtoupper(trim($request->location)),
            'stock' => 0, // Stok awal selalu 0
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
        ]);

        return redirect()->route('tpm.index')->with('success', "Barang TPM baru berhasil didaftarkan dengan kode Barcode: $newNumber");
    }

    // 4. Halaman khusus Teknisi untuk Scan Barcode (IN / OUT Stok)
    public function scan()
    {
        return view('tpm.scan');
    }

    // 5. Proses Update Stok dari Scanner
    public function processScan(Request $request)
    {
        $request->validate([
            'tpm_part_number' => 'required|string',
            'transaction_type' => 'required|in:IN,OUT',
            'qty' => 'required|integer|min:1',
        ]);

        $part = TpmPart::where('tpm_part_number', $request->tpm_part_number)->first();

        // Cek jika teknisi scan barcode yang tidak terdaftar
        if (! $part) {
            return back()->with('error', 'GAGAL! Part dengan barcode tersebut tidak ditemukan di database TPM!');
        }

        // Cek jika teknisi ambil barang (OUT) tapi stoknya kurang
        if ($request->transaction_type == 'OUT' && $part->stock < $request->qty) {
            return back()->with('error', "GAGAL! Stok tidak mencukupi! Stok {$part->name} saat ini hanya sisa: {$part->stock}");
        }

        DB::transaction(function () use ($request, $part) {
            // Catat history transaksinya
            TpmTransaction::create([
                'tpm_part_id' => $part->id,
                'transaction_type' => $request->transaction_type,
                'qty' => $request->qty,
                'operator_name' => auth()->user()->name ?? 'Teknisi',
            ]);

            // Update stok utamanya
            if ($request->transaction_type == 'IN') {
                $part->stock += $request->qty;
            } else {
                $part->stock -= $request->qty;
            }
            $part->save();
        });

        $status = $request->transaction_type == 'IN' ? 'ditambahkan' : 'diambil';

        return back()->with('success', "BERHASIL! Stok {$part->name} berhasil $status sebanyak {$request->qty}. (Sisa Stok Sekarang: {$part->stock})");
    }

    // 6. Hapus Barang TPM secara permanen
    public function destroy($id)
    {
        $part = TpmPart::findOrFail($id);
        $namaPart = $part->name;

        // Hapus part (history transaksi akan ikut terhapus otomatis karena cascade)
        $part->delete();

        return redirect()->route('tpm.index')->with('success', "Barang TPM '{$namaPart}' berhasil dihapus secara permanen.");
    }
}
