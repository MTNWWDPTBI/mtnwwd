<?php

namespace App\Http\Controllers;

use App\Imports\PicMachineImport;
use App\Models\SapHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class PicReportController extends Controller
{
    /**
     * Tampilkan halaman utama Dashboard Laporan Bulanan PIC Mesin.
     */
    public function index(): View
    {
        $user = auth()->user();
        $machines = $user->picMachines()
            ->orderBy('functional_location')
            ->pluck('functional_location')
            ->unique()
            ->values();

        return view('pic-report.index', compact('machines'));
    }

    /**
     * Generate data laporan Top 5 komponen sering rusak & jam downtime untuk Chart.js (AJAX).
     */
    public function generateReport(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|string',
            'machines' => 'nullable',
            'machine' => 'nullable|string',
        ]);

        $monthInput = $request->string('month')->trim()->value();
        $user = auth()->user();
        $userMachines = $user->picMachines()->pluck('functional_location')->toArray();

        if (empty($userMachines)) {
            return response()->json([
                'status' => 'empty',
                'labels' => [],
                'frequencies' => [],
                'downtimes' => [],
                'message' => 'Anda belum memiliki mesin yang ditugaskan sebagai PIC.',
            ]);
        }

        // Tentukan daftar mesin yang difilter (bisa 1 mesin, beberapa mesin, atau semua mesin milik PIC)
        $filterMachines = $this->resolveFilteredMachines($request, $userMachines);

        // Parsing filter bulan (Mendukung format 'YYYY-MM' dan format SAP 'MM.YYYY')
        $dashMonth = $monthInput;
        $dotMonth = $this->formatToSapMonth($monthInput);

        // Query Top 5 Object Part berdasarkan frekuensi kerusakan beserta total bm_hrs
        $topParts = SapHistory::whereIn('functional_location', $filterMachines)
            ->where(function ($q) use ($dashMonth, $dotMonth) {
                $q->where('date', 'like', "{$dashMonth}%")
                    ->orWhere('month', $dotMonth);
            })
            ->whereNotNull('object_part')
            ->where('object_part', '!=', '')
            ->select(
                'object_part',
                DB::raw('COUNT(*) as frequency'),
                DB::raw('ROUND(SUM(CAST(bm_hrs AS DECIMAL(10,2))), 2) as total_bm_hrs')
            )
            ->groupBy('object_part')
            ->orderByDesc('frequency')
            ->limit(5)
            ->get();

        $labels = $topParts->pluck('object_part')->toArray();
        $frequencies = $topParts->pluck('frequency')->map(fn ($val) => (int) $val)->toArray();
        $downtimes = $topParts->pluck('total_bm_hrs')->map(fn ($val) => (float) $val)->toArray();

        return response()->json([
            'status' => 'success',
            'labels' => $labels,
            'frequencies' => $frequencies,
            'downtimes' => $downtimes,
            'total_events' => array_sum($frequencies),
            'total_downtime' => round(array_sum($downtimes), 2),
            'machine_count' => count($filterMachines),
            'filtered_machines' => $filterMachines,
        ]);
    }

    /**
     * Generate ringkasan 3 poin PPT menggunakan AI Gateway berdasarkan SELURUH riwayat kerusakan bulanan (AJAX).
     */
    public function generateAiSummary(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|string',
            'machines' => 'nullable',
            'machine' => 'nullable|string',
        ]);

        $monthInput = $request->string('month')->trim()->value();
        $user = auth()->user();
        $userMachines = $user->picMachines()->pluck('functional_location')->toArray();

        if (empty($userMachines)) {
            return response()->json([
                'status' => 'error',
                'summary' => 'Anda belum memiliki mesin yang ditugaskan sebagai PIC.',
            ], 422);
        }

        // Tentukan daftar mesin yang difilter dan susun label mesin untuk prompt AI
        $filterMachines = $this->resolveFilteredMachines($request, $userMachines);

        if (count($filterMachines) === 1) {
            $machineLabel = $filterMachines[0];
        } elseif (count($filterMachines) === count($userMachines)) {
            $machineLabel = 'Semua Mesin PIC Anda ('.count($userMachines).' Unit)';
        } else {
            $machineLabel = count($filterMachines).' Mesin PIC Terpilih ('.implode(', ', $filterMachines).')';
        }

        $dashMonth = $monthInput;
        $dotMonth = $this->formatToSapMonth($monthInput);

        // Query dasar seluruh riwayat kerusakan pada bulan dan mesin terpilih (tanpa batasan limit 10)
        $baseQuery = SapHistory::whereIn('functional_location', $filterMachines)
            ->where(function ($q) use ($dashMonth, $dotMonth) {
                $q->where('date', 'like', "{$dashMonth}%")
                    ->orWhere('month', $dotMonth);
            });

        // 1. Total Order / WO Kerusakan dalam sebulan
        $totalOrders = (clone $baseQuery)->count();

        if ($totalOrders === 0) {
            return response()->json([
                'status' => 'success',
                'summary' => "ℹ️ **Tidak ada data kerusakan** ditemukan untuk {$machineLabel} pada periode {$monthInput}. Pastikan histori data SAP untuk bulan ini telah diimpor.",
            ]);
        }

        // 2. Total Akumulasi Jam Breakdown (SUM bm_hrs)
        $totalBmHours = round((float) (clone $baseQuery)->sum('bm_hrs'), 2);

        // 3. Top 3 Mesin dengan Downtime & WO Terbanyak (Bad Actor Machines)
        $topMachines = (clone $baseQuery)
            ->select(
                'functional_location',
                DB::raw('COUNT(*) as wo_count'),
                DB::raw('ROUND(SUM(CAST(bm_hrs AS DECIMAL(10,2))), 2) as total_bm')
            )
            ->groupBy('functional_location')
            ->orderByDesc('total_bm')
            ->orderByDesc('wo_count')
            ->limit(3)
            ->get();

        $topMachinesLines = [];
        foreach ($topMachines as $idx => $m) {
            $topMachinesLines[] = sprintf(
                '%d. Mesin %s: Total Downtime %s Jam (%d Work Order)',
                $idx + 1,
                $m->functional_location,
                $m->total_bm,
                $m->wo_count
            );
        }
        $topMachinesSummary = ! empty($topMachinesLines) ? implode("\n", $topMachinesLines) : '- Data mesin tidak tersedia';

        // 4. Rincian seluruh komponen yang rusak (object_part) beserta frekuensi dan total downtime
        $partStats = (clone $baseQuery)
            ->select(
                'object_part',
                DB::raw('COUNT(*) as frequency'),
                DB::raw('ROUND(SUM(CAST(bm_hrs AS DECIMAL(10,2))), 2) as total_bm')
            )
            ->whereNotNull('object_part')
            ->where('object_part', '!=', '')
            ->groupBy('object_part')
            ->orderByDesc('frequency')
            ->get();

        $partLines = [];
        foreach ($partStats as $p) {
            $partLines[] = sprintf('- %s: %d kali (Downtime: %s Jam)', $p->object_part, $p->frequency, $p->total_bm);
        }
        $partSummary = ! empty($partLines) ? implode("\n", $partLines) : '- Komponen tidak terspesifikasi pada order SAP';

        // 5. Rincian Kode Mesin per Komponen Dominan (Mesin penyumbang kerusakan terbanyak untuk tiap komponen dominan)
        $topPartNames = $partStats->take(3)->pluck('object_part')->toArray();
        $partMachineStats = (clone $baseQuery)
            ->select(
                'object_part',
                'functional_location',
                DB::raw('COUNT(*) as frequency'),
                DB::raw('ROUND(SUM(CAST(bm_hrs AS DECIMAL(10,2))), 2) as total_bm')
            )
            ->whereIn('object_part', $topPartNames)
            ->groupBy('object_part', 'functional_location')
            ->orderBy('object_part')
            ->orderByDesc('frequency')
            ->get()
            ->groupBy('object_part');

        $partMachineLines = [];
        foreach ($partMachineStats as $partName => $records) {
            $machineSubLines = [];
            foreach ($records->take(3) as $rec) {
                $machineSubLines[] = "{$rec->functional_location} ({$rec->frequency}x, {$rec->total_bm} Jam)";
            }
            $partMachineLines[] = "- Komponen [{$partName}]: Terjadi paling sering pada mesin ".implode(', ', $machineSubLines);
        }
        $partMachineSummary = ! empty($partMachineLines) ? implode("\n", $partMachineLines) : '- Data rincian mesin per komponen tidak tersedia';

        // 6. Daftar seluruh deskripsi masalah (maint_order_desc / problem) yang terjadi selama bulan tersebut
        $problemStats = (clone $baseQuery)
            ->select(
                DB::raw("COALESCE(NULLIF(problem, ''), maint_order_desc) as problem_desc"),
                DB::raw('COUNT(*) as frequency'),
                DB::raw('ROUND(SUM(CAST(bm_hrs AS DECIMAL(10,2))), 2) as sum_bm')
            )
            ->where(function ($q) {
                $q->whereNotNull('problem')->where('problem', '!=', '')
                    ->orWhereNotNull('maint_order_desc')->where('maint_order_desc', '!=', '');
            })
            ->groupBy('problem_desc')
            ->orderByDesc('frequency')
            ->get();

        $problemLines = [];
        foreach ($problemStats as $pr) {
            $problemLines[] = sprintf('- %s (%d kejadian, Total BM: %s Jam)', $pr->problem_desc, $pr->frequency, $pr->sum_bm);
        }
        $problemSummary = ! empty($problemLines) ? implode("\n", $problemLines) : '- Tidak ada catatan deskripsi masalah spesifik';

        // 7. Susun prompt teks untuk AI sesuai instruksi spesifikasi analisis penuh 1 bulan
        $prompt = "Kamu adalah Senior Maintenance Engineer pabrik Bekaert. Diberikan data riwayat kerusakan mesin sebulan penuh (Periode: {$monthInput}) untuk {$machineLabel}.\n"
            ."WAJIB: Di setiap poin analisis (terutama Fokus Masalah dan Action Plan), sebutkan secara spesifik KODE MESIN (functional_location) utama yang menjadi biang kerok (bad actor).\n\n"
            ."=== DATA STATISTIK BULANAN ({$monthInput}) ===\n"
            ."- Total Order / WO Kerusakan: {$totalOrders} kejadian\n"
            ."- Total Akumulasi Jam Breakdown (bm_hrs): {$totalBmHours} Jam\n\n"
            ."=== TOP 3 MESIN PALING BANYAK DOWNTIME & WO (BAD ACTOR MACHINES) ===\n"
            .$topMachinesSummary."\n\n"
            ."=== RINCIAN KODE MESIN PER KOMPONEN DOMINAN ===\n"
            .$partMachineSummary."\n\n"
            ."=== RINCIAN SELURUH KOMPONEN RUSAK (OBJECT PART) & FREKUENSINYA ===\n"
            .$partSummary."\n\n"
            ."=== DAFTAR DESKRIPSI MASALAH YANG TERJADI DALAM BULAN INI ===\n"
            .$problemSummary."\n\n"
            ."=== INSTRUKSI FORMAT OUTPUT 3 POIN SLIDE PPT ===\n"
            ."Buatkan analisis terstruktur dengan format 3 poin utama berikut:\n"
            ."1. **Fokus Masalah Terbanyak**:\n"
            ."   - Sebutkan Top Mesin dengan downtime/WO tertinggi bulan ini (cantumkan KODE MESIN spesifik dan jam downtime-nya).\n"
            ."   - Komponen dominan beserta KODE MESIN SPESIFIK tempat komponen tersebut paling sering rusak.\n"
            ."   - Deskripsi masalah terbanyak yang dialami.\n"
            ."2. **Root Cause / Analisis Akar Masalah Utama**:\n"
            ."   - Analisis kenapa mesin-mesin spesifik tersebut mengalami kegagalan berulang (kondisi operasional, beban, degradasi komponen).\n"
            ."3. **Action Plan & TPM Preventif**:\n"
            ."   - Buat rekomendasi aksi prioritas yang menyebutkan KODE MESIN tujuan (contoh: 'Prioritaskan audit alignment Shaft khusus pada mesin KA-WWD-SHX-4016', pengecekan pelumasan berkala, atau penyesuaian checklist TPM shift).\n\n"
            ."Pedoman Format:\n"
            ."- Pastikan SELALU menyebutkan KODE MESIN (functional_location) riil dari data di atas.\n"
            ."- Tulis ringkas, padat data statistik, profesional, dan gunakan Bahasa Indonesia teknis yang formal namun komunikatif.\n"
            .'- Gunakan format bullet points Markdown yang langsung siap disalin (Ctrl+C) ke slide presentasi PowerPoint.';

        $fallbackData = [
            'total_orders' => $totalOrders,
            'total_bm' => $totalBmHours,
            'top_machine' => $topMachines->first()?->functional_location ?? 'KA-WWD-NDB-3006',
            'top_machine_bm' => $topMachines->first()?->total_bm ?? $totalBmHours,
            'top_machine_wo' => $topMachines->first()?->wo_count ?? $totalOrders,
            'top_part' => $partStats->first()?->object_part ?? 'Komponen Utama',
            'top_part_count' => $partStats->first()?->frequency ?? 0,
            'top_problem' => $problemStats->first()?->problem_desc ?? 'Keausan operasional',
            'month' => $monthInput,
        ];

        $summary = $this->callAiGateway($prompt, $fallbackData);

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
        ]);
    }

    /**
     * Import file Excel pemetaan PIC mesin secara massal (Khusus Admin).
     */
    public function importPic(Request $request): RedirectResponse
    {
        // Cek apakah user berstatus admin (role === 'admin' atau is_admin === true)
        if (auth()->user()?->role !== 'admin' && ! (bool) (auth()->user()?->is_admin ?? false)) {
            abort(403, 'Akses Ditolak: Hanya Admin yang memiliki wewenang untuk mengunggah pemetaan mesin PIC.');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:10240',
        ], [
            'file.required' => 'Silakan pilih file Excel/CSV terlebih dahulu.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau .csv.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            Excel::import(new PicMachineImport, $request->file('file'));

            return back()->with('success', 'Pemetaan PIC mesin berhasil diimpor!');
        } catch (\Throwable $e) {
            Log::error('Import PIC Machine Error: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat mengimpor data: '.$e->getMessage());
        }
    }

    /**
     * Panggil AI Gateway / Anthropic Messages API dengan fallback cerdas.
     *
     * @param  array<int, mixed>  $fallbackData
     */
    private function callAiGateway(string $prompt, array $fallbackData = []): string
    {
        $baseUrl = config('services.ai_gateway.base_url', 'https://api.relink-gateway.biz.id/v1');
        $apiKey = config('services.ai_gateway.api_key');
        $model = config('services.ai_gateway.model', 'auto');
        $timeout = (int) config('services.ai_gateway.timeout', 45);

        if (! empty($apiKey)) {
            $cleanBase = rtrim($baseUrl, '/');
            if (! str_ends_with($cleanBase, '/v1')) {
                $cleanBase .= '/v1';
            }
            $endpoint = $cleanBase.'/messages';

            try {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ])->withOptions([
                    'force_ip_resolve' => 'v4',
                    'verify' => false,
                ])->timeout($timeout)->post($endpoint, [
                    'model' => $model,
                    'max_tokens' => 1500,
                    'system' => 'Kamu adalah Senior Maintenance Engineer di PT Bekaert Indonesia yang ahli dalam menyusun rangkuman PPT problem solving mesin pabrik. Kamu WAJIB selalu menyebutkan kode mesin spesifik (functional_location) di setiap poin analisis dan rekomendasi.',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $contentBlocks = $response->json('content') ?? [];
                    $textParts = [];

                    foreach ($contentBlocks as $block) {
                        if (($block['type'] ?? '') === 'text' && ! empty($block['text'])) {
                            $cleanText = preg_replace('/<think>.*?<\/think>/is', '', $block['text']);
                            $cleanText = preg_replace('/<｜DSML｜.*?<\/｜DSML｜.*?>/is', '', $cleanText);
                            $textParts[] = trim($cleanText);
                        }
                    }

                    $combined = implode("\n\n", array_filter($textParts));
                    if (! empty($combined)) {
                        return $combined;
                    }
                }

                Log::warning('PicReport AI Gateway response unhandled', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('PicReport AI Gateway call failed: '.$e->getMessage());
            }
        }

        // Fallback cerdas jika koneksi AI offline atau tidak tersedia API key
        return $this->generateFallbackSummary($fallbackData);
    }

    /**
     * Fallback perangkum jika API AI offline, menyusun 3 poin PPT berbasis analisis data riil bulanan.
     *
     * @param  array<string, mixed>  $data
     */
    private function generateFallbackSummary(array $data): string
    {
        $totalOrders = $data['total_orders'] ?? 0;
        $totalBm = $data['total_bm'] ?? 0;
        $topMachine = $data['top_machine'] ?? 'KA-WWD-NDB-3006';
        $topMachineBm = $data['top_machine_bm'] ?? $totalBm;
        $topMachineWo = $data['top_machine_wo'] ?? $totalOrders;
        $topPart = $data['top_part'] ?? 'Komponen Utama';
        $topPartCount = $data['top_part_count'] ?? 0;
        $topProblem = $data['top_problem'] ?? 'Kendala operasional mesin';
        $month = $data['month'] ?? 'Bulan Terpilih';

        if ($totalOrders === 0) {
            return "1. **Fokus Masalah Terbanyak**\n- Tidak ada riwayat kerusakan mesin pada periode yang dipilih.\n\n2. **Root Cause / Analisis Akar Masalah Utama**\n- Mesin beroperasi secara stabil tanpa catatan breakdown signifikan.\n\n3. **Action Plan & TPM Preventif**\n- Pertahankan inspeksi berkala dan pembersihan rutin TPM harian.";
        }

        return "### Ringkasan Laporan Bulanan PIC (Presentasi PPT - Periode {$month})\n\n"
            ."1. **Fokus Masalah Terbanyak**:\n"
            ."- Mesin dengan kontribusi downtime tertinggi (Bad Actor): **{$topMachine}** dengan akumulasi **{$topMachineBm} Jam Downtime** ({$topMachineWo} Work Order dari total {$totalOrders} WO bulanan).\n"
            ."- Komponen paling dominan mengalami kendala: **{$topPart}** ({$topPartCount} kejadian), terpusat di mesin **{$topMachine}**.\n"
            ."- Deskripsi masalah utama yang sering dialami: **{$topProblem}**.\n\n"
            ."2. **Root Cause / Analisis Akar Masalah Utama**:\n"
            ."- Mesin **{$topMachine}** mengalami degradasi mekanis berulang akibat beban operasional kontinu dan getaran tinggi.\n"
            ."- Penurunan performa pelumasan pada area kritis yang memicu keausan prematur pada komponen **{$topPart}**.\n\n"
            ."3. **Action Plan & TPM Preventif**:\n"
            ."- **Prioritas Mesin {$topMachine}**: Lakukan overhaul dan alignment menyeluruh pada komponen **{$topPart}** sebelum batas lifetime tercapai.\n"
            ."- **Checklist TPM Khusus {$topMachine}**: Perketat inspeksi harian autonomous maintenance pada pengecekan suhu bearing dan jadwal pelumasan berkala.\n"
            .'- Verifikasi ulang setting clearance dan kalibrasi sensor pada jadwal shutdown maintenance bulanan.';
    }

    /**
     * Tentukan daftar mesin yang difilter, terisolasi ketat hanya dalam lingkup PIC user.
     *
     * @param  array<int, string>  $userMachines
     * @return array<int, string>
     */
    private function resolveFilteredMachines(Request $request, array $userMachines): array
    {
        $selected = [];

        if ($request->has('machines')) {
            $raw = $request->input('machines');
            if (is_array($raw)) {
                $selected = array_values(array_filter($raw, fn ($m) => ! empty($m) && is_string($m)));
            } elseif (is_string($raw) && ! empty(trim($raw))) {
                $selected = [trim($raw)];
            }
        } elseif ($request->filled('machine')) {
            $selected = [trim((string) $request->string('machine'))];
        }

        // Filter ketat: pastikan hanya mesin yang benar-benar milik user (anti-kebocoran)
        $validSelected = array_values(array_intersect($selected, $userMachines));

        // Jika user memilih 1 atau beberapa mesin valid, gunakan pilihan tersebut.
        // Jika tidak ada yang dipilih (atau memilih opsi "Semua"), gunakan seluruh mesin milik user.
        if (! empty($validSelected)) {
            return $validSelected;
        }

        return $userMachines;
    }

    /**
     * Ubah format input HTML bulan 'YYYY-MM' menjadi format SAP 'MM.YYYY'.
     */
    private function formatToSapMonth(string $yearMonth): string
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $yearMonth, $m)) {
            return "{$m[2]}.{$m[1]}";
        }

        return $yearMonth;
    }
}
