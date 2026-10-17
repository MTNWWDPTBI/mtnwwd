<?php

namespace App\Http\Controllers;

use App\Models\TPMPart;
use App\Models\Handover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    // 1. Export Data Stok TPM ke Format Excel (.xls)
    public function exportTpm()
    {
        $fileName = 'Laporan_Stok_TPM_' . date('Y-m-d') . '.xls';
        $parts = TPMPart::all();

        $headers = [
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $html = '<html lang="id">
        <head><meta charset="UTF-8"></head>
        <body>
            <h3 style="color: #333;">Laporan Stok Barang TPM</h3>
            <table border="1" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr style="background-color: #242E49; color: #FDA481; text-align: center; font-weight: bold;">
                        <th>Part Number</th>
                        <th>Nama Barang</th>
                        <th>Lokasi Rak</th>
                        <th>Stok Minimal</th>
                        <th>Stok Maksimal</th>
                        <th>Stok Saat Ini</th>
                        <th>Tanggal Dibuat</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($parts as $part) {
            $html .= '<tr>
                <td style="mso-number-format:\@;">' . $part->tpm_part_id . '</td>
                <td>' . htmlspecialchars($part->name) . '</td>
                <td style="text-align: center;">' . htmlspecialchars($part->location) . '</td>
                <td style="text-align: center;">' . $part->min_stock . '</td>
                <td style="text-align: center;">' . $part->max_stock . '</td>
                <td style="text-align: center; font-weight: bold;">' . $part->stock . '</td>
                <td>' . ($part->created_at ? $part->created_at->format('Y-m-d H:i') : '-') . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, $headers);
    }

    // 2. Export Riwayat Transaksi Masuk/Keluar (IN/OUT) TPM
    public function exportTpmTransactions()
    {
        $fileName = 'Riwayat_Transaksi_TPM_' . date('Y-m-d') . '.xls';
        
        // Menggabungkan tabel tpm_transactions dengan tpm_parts untuk mengambil nama barang & part number secara otomatis
        $logs = DB::table('tpm_transactions')
                    ->join('tpm_parts', 'tpm_transactions.tpm_part_id', '=', 'tpm_parts.id')
                    ->select(
                        'tpm_transactions.*', 
                        'tpm_parts.tpm_part_number', 
                        'tpm_parts.name as item_name'
                    )
                    ->orderBy('tpm_transactions.created_at', 'desc')
                    ->get();

        $headers = [
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $html = '<html lang="id">
        <head><meta charset="UTF-8"></head>
        <body>
            <h3 style="color: #333;">Riwayat Transaksi Stok TPM (IN / OUT)</h3>
            <table border="1" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr style="background-color: #242E49; color: #FDA481; text-align: center; font-weight: bold;">
                        <th>Waktu Transaksi</th>
                        <th>ID Part TPM</th>
                        <th>Nama Barang</th>
                        <th>Tipe (IN/OUT)</th>
                        <th>Jumlah (Qty)</th>
                        <th>Dilakukan Oleh</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($logs as $log) {
            // Menggunakan tpm_part_id sesuai struktur database-mu
            $partId   = $log->tpm_part_id ?? ($log->part_id ?? '-');
            $itemName = $log->item_name ?? ($log->name ?? '-');
            $typeName = $log->transaction_type ?? ($log->type ?? 'IN');
            $qtyVal   = $log->qty ?? ($log->quantity ?? 0);
            $userName = $log->operator_name ?? ($log->user ?? 'Sistem');
            $timeVal  = $log->created_at ?? '-';

            $typeColor = $typeName == 'IN' ? 'color: green; font-weight: bold;' : 'color: red; font-weight: bold;';
            
            $html .= '<tr>
                <td style="text-align: center;">' . $timeVal . '</td>
                <td style="text-align: center;">' . $partId . '</td>
                <td>' . htmlspecialchars($itemName) . '</td>
                <td style="text-align: center; ' . $typeColor . '">' . $typeName . '</td>
                <td style="text-align: center; font-weight: bold;">' . $qtyVal . '</td>
                <td>' . htmlspecialchars($userName) . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, $headers);
    }

    // 3. Export Data Shift Handover ke Format Excel (.xls)
    public function exportHandover()
    {
        $fileName = 'Laporan_Shift_Handover_' . date('Y-m-d') . '.xls';
        $handovers = Handover::orderBy('handover_date', 'desc')->get();

        $headers = [
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $html = '<html lang="id">
        <head><meta charset="UTF-8"></head>
        <body>
            <h3 style="color: #333;">Laporan Shift Handover Logbook</h3>
            <table border="1" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr style="background-color: #242E49; color: #FDA481; text-align: center; font-weight: bold;">
                        <th>Tanggal Shift</th>
                        <th>Shift</th>
                        <th>Tim</th>
                        <th>Nama Mesin</th>
                        <th>Detail Problem</th>
                        <th>Status</th>
                        <th>Dilaporkan Oleh</th>
                        <th>Diselesaikan Oleh</th>
                        <th>Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($handovers as $log) {
            $statusColor = $log->status == 'OPEN' ? 'color: red; font-weight: bold;' : 'color: green; font-weight: bold;';
            $html .= '<tr>
                <td style="text-align: center;">' . $log->handover_date . '</td>
                <td style="text-align: center;">Shift ' . $log->shift . '</td>
                <td style="text-align: center; font-weight: bold;">Tim ' . $log->team . '</td>
                <td>' . htmlspecialchars($log->machine_name) . '</td>
                <td>' . htmlspecialchars($log->problem_description) . '</td>
                <td style="text-align: center; ' . $statusColor . '">' . $log->status . '</td>
                <td>' . htmlspecialchars($log->reported_by) . '</td>
                <td>' . htmlspecialchars($log->resolved_by ?? '-') . '</td>
                <td>' . ($log->resolved_at ?? '-') . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, $headers);
    }
}