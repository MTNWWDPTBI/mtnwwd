<?php

namespace App\Http\Controllers;

use App\Imports\PicMachineImport;
use App\Models\PicMachine;
use App\Models\SapHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PicReportController extends Controller
{
    /**
     * Tampilkan halaman utama Dashboard Laporan Bulanan PIC Mesin.
     */
    public function index(): View
    {
        $user = auth()->user();
        $isAdmin = ($user->role === 'admin' || (bool) ($user->is_admin ?? false));

        // Ambil HANYA daftar mesin PIC yang ditugaskan ke akun yang sedang login
        $machines = $user->picMachines()
            ->orderBy('functional_location')
            ->pluck('functional_location')
            ->unique()
            ->values();

        // Tentukan default bulan dari data historis SAP terbaru yang riil (agar tidak kosong)
        $latestDate = SapHistory::whereNotNull('date')
            ->where('date', '<=', now()->toDateString())
            ->max('date');

        if ($latestDate && preg_match('/^(\d{4}-\d{2})/', $latestDate, $m)) {
            $defaultMonth = $m[1];
        } else {
            $latestMonth = SapHistory::whereNotNull('month')
                ->where('month', '!=', 'Total')
                ->max('month');
            if ($latestMonth && preg_match('/^(\d{1,2})\.(\d{4})$/', $latestMonth, $mm)) {
                $defaultMonth = sprintf('%04d-%02d', $mm[2], $mm[1]);
            } else {
                $defaultMonth = date('Y-m');
            }
        }

        return view('pic-report.index', compact('machines', 'defaultMonth', 'isAdmin'));
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

        // Ambil HANYA mesin PIC milik akun user yang sedang login
        $userMachines = $user->picMachines()
            ->pluck('functional_location')
            ->unique()
            ->values()
            ->toArray();

        if (empty($userMachines)) {
            return response()->json([
                'status' => 'empty',
                'labels' => [],
                'frequencies' => [],
                'downtimes' => [],
                'message' => 'Anda belum memiliki mesin yang ditugaskan sebagai PIC. Silakan hubungi Administrator untuk pemetaan mesin PIC akun Anda.',
            ]);
        }

        // Tentukan daftar mesin yang difilter (terisolasi ketat hanya dalam lingkup PIC user ini)
        $filterMachines = $this->resolveFilteredMachines($request, $userMachines);

        // Parsing filter bulan (Mendukung format 'YYYY-MM' dan format SAP 'MM.YYYY' atau 'M.YYYY')
        $dashMonth = $monthInput;
        $sapMonths = $this->formatToSapMonths($monthInput);

        // Query Top 5 Object Part berdasarkan frekuensi kerusakan beserta total bm_hrs
        $topParts = SapHistory::whereIn('functional_location', $filterMachines)
            ->where(function ($q) use ($dashMonth, $sapMonths) {
                $q->where('date', 'like', "{$dashMonth}%")
                    ->orWhereIn('month', $sapMonths);
            })
            ->whereNotNull('object_part')
            ->where('object_part', '!=', '')
            ->where('object_part', '!=', 'Total')
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
     * Generate ringkasan 3 poin PPT menggunakan AI Gateway berdasarkan riwayat kerusakan bulanan (AJAX).
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

        // Ambil HANYA mesin PIC milik akun user yang sedang login
        $userMachines = $user->picMachines()
            ->pluck('functional_location')
            ->unique()
            ->values()
            ->toArray();

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
            $machineLabel = count($filterMachines).' Mesin PIC Terpilih ('.implode(', ', array_slice($filterMachines, 0, 3)).(count($filterMachines) > 3 ? '...' : '').')';
        }

        $dashMonth = $monthInput;
        $sapMonths = $this->formatToSapMonths($monthInput);

        // Query dasar seluruh riwayat kerusakan pada bulan dan mesin terpilih
        $baseQuery = SapHistory::whereIn('functional_location', $filterMachines)
            ->where(function ($q) use ($dashMonth, $sapMonths) {
                $q->where('date', 'like', "{$dashMonth}%")
                    ->orWhereIn('month', $sapMonths);
            })
            ->where('functional_location', '!=', 'Total');

        // 1. Total Order / WO Kerusakan dalam sebulan
        $totalOrders = (clone $baseQuery)->count();

        if ($totalOrders === 0) {
            return response()->json([
                'status' => 'success',
                'summary' => "ℹ️ **Tidak ada data kerusakan** ditemukan untuk {$machineLabel} pada periode {$monthInput}. Silakan pilih periode bulan lain yang memiliki riwayat kerusakan di SAP (contoh: 2026-10).",
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
            ->where('object_part', '!=', 'Total')
            ->groupBy('object_part')
            ->orderByDesc('frequency')
            ->get();

        $partLines = [];
        foreach ($partStats as $p) {
            $partLines[] = sprintf('- %s: %d kali (Downtime: %s Jam)', $p->object_part, $p->frequency, $p->total_bm);
        }
        $partSummary = ! empty($partLines) ? implode("\n", $partLines) : '- Komponen tidak terspesifikasi pada order SAP';

        // 5. Rincian Kode Mesin per Komponen Dominan
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

        // 6. Daftar seluruh deskripsi masalah
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

        // 7. Susun prompt teks untuk AI
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
            'top_machine' => $topMachines->first()?->functional_location ?? ($filterMachines[0] ?? 'Mesin Pabrik'),
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
            $import = new PicMachineImport();
            Excel::import($import, $request->file('file'));

            if ($import->importedCount > 0) {
                $msg = "Berhasil mengimpor {$import->importedCount} pemetaan mesin PIC!";
                if ($import->skippedCount > 0) {
                    $msg .= " ({$import->skippedCount} baris dilewati karena teknisi/mesin tidak sesuai).";
                }

                return back()->with('success', $msg);
            }

            $errMsg = 'Tidak ada data pemetaan PIC yang berhasil diimpor. Pastikan file Excel memuat kolom Nama/Email Teknisi dan Kode Mesin/Functional Location yang sesuai.';
            if (! empty($import->skippedDetails)) {
                $errMsg .= ' Rincian: '.implode(' | ', array_slice($import->skippedDetails, 0, 3));
            }

            return back()->with('error', $errMsg);

        } catch (\Throwable $e) {
            Log::error('Import PIC Machine Error: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat mengimpor data: '.$e->getMessage());
        }
    }

    /**
     * Download template CSV contoh pemetaan PIC mesin (Khusus Admin).
     */
    public function downloadTemplate(): StreamedResponse
    {
        if (auth()->user()?->role !== 'admin' && ! (bool) (auth()->user()?->is_admin ?? false)) {
            abort(403);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_pemetaan_pic_mesin.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM agar rapi di Microsoft Excel Windows
            fputs($handle, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($handle, ['nama_teknisi', 'email', 'functional_location']);

            // Baris contoh dari data riil di sistem
            $sampleUsers = User::take(3)->get();
            $sampleFlocs = SapHistory::whereNotNull('functional_location')
                ->where('functional_location', '!=', 'Total')
                ->distinct()
                ->take(3)
                ->pluck('functional_location');

            foreach ($sampleUsers as $idx => $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $sampleFlocs[$idx] ?? 'KA-WWD-NDB-3006',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Panggil AI Gateway (gpt-6-sol OpenAI Chat Completions API) dengan fallback Gemini dan statistik cerdas.
     *
     * @param  array<string, mixed>  $fallbackData
     */
    private function callAiGateway(string $prompt, array $fallbackData = []): string
    {
        // 1. Prioritas 1: Gunakan AI Gateway (gpt-6-sol / OpenAI Chat Completions API)
        $baseUrl = config('services.ai_gateway.base_url') ?: config('services.ai_gateway.url') ?: env('AI_GATEWAY_URL', 'https://ai.helipod.io/v1');
        $apiKey = config('services.ai_gateway.api_key') ?: config('services.ai_gateway.key') ?: env('AI_API_KEY');
        $model = config('services.ai_gateway.model') ?: env('AI_MODEL', 'gpt-6-sol');
        $timeout = (int) (config('services.ai_gateway.timeout') ?: env('AI_TIMEOUT', 30));

        if (! empty($apiKey)) {
            $cleanBase = rtrim($baseUrl, '/');
            if (! str_ends_with($cleanBase, '/v1')) {
                $cleanBase .= '/v1';
            }
            $endpoint = $cleanBase.'/chat/completions';

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ])->withOptions([
                    'force_ip_resolve' => 'v4',
                    'verify' => false,
                ])->timeout($timeout)->post($endpoint, [
                    'model' => $model,
                    'max_tokens' => 1500,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Kamu adalah Senior Maintenance Engineer di PT Bekaert Indonesia yang ahli dalam menyusun rangkuman PPT problem solving mesin pabrik. Kamu WAJIB selalu menyebutkan kode mesin spesifik (functional_location) di setiap poin analisis dan rekomendasi.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $aiText = $response->json('choices.0.message.content');
                    if (! empty(trim((string) $aiText))) {
                        $cleanText = preg_replace('/<think>.*?<\/think>/is', '', $aiText);
                        $cleanText = preg_replace('/<｜DSML｜.*?<\/｜DSML｜.*?>/is', '', $cleanText);

                        return trim($cleanText);
                    }
                } else {
                    Log::warning('PicReport AI Gateway response error', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Throwable $e) {
                Log::info('PicReport AI Gateway call failed/timeout: '.$e->getMessage());
            }
        }

        // 2. Prioritas 2: Fallback ke Gemini API jika tersedia
        $geminiKey = env('GEMINI_API_KEY') ?: config('services.gemini.key');
        if (! empty($geminiKey)) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->withOptions([
                    'verify' => false,
                ])->timeout(12)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$geminiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $aiText = $response->json('candidates.0.content.parts.0.text');
                    if (! empty(trim((string) $aiText))) {
                        $cleanText = preg_replace('/<think>.*?<\/think>/is', '', $aiText);
                        $cleanText = preg_replace('/<｜DSML｜.*?<\/｜DSML｜.*?>/is', '', $cleanText);

                        return trim($cleanText);
                    }
                }
            } catch (\Throwable $e) {
                Log::info('PicReport Gemini API attempt failed/timeout: '.$e->getMessage());
            }
        }

        // 3. Prioritas 3: Fallback cerdas berbasis data statistik riil mesin bulanan
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
        $topMachine = $data['top_machine'] ?? 'Mesin Pabrik';
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
     * Tentukan daftar mesin yang difilter, terisolasi ketat hanya dalam lingkup PIC akun user yang login.
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

        if (! empty($selected)) {
            // Filter ketat: pastikan hanya mesin yang benar-benar ada di daftar mesin PIC akun login (anti-kebocoran)
            $validSelected = array_values(array_intersect($selected, $userMachines));
            if (! empty($validSelected)) {
                return $validSelected;
            }
        }

        return $userMachines;
    }

    /**
     * Ubah format input HTML bulan 'YYYY-MM' menjadi format SAP 'MM.YYYY' dan 'M.YYYY'.
     *
     * @return array<int, string>
     */
    private function formatToSapMonths(string $yearMonth): array
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $yearMonth, $m)) {
            $y = $m[1];
            $mPadded = $m[2];
            $mUnpadded = (string) (int) $m[2];

            if ($mPadded !== $mUnpadded) {
                return ["{$mPadded}.{$y}", "{$mUnpadded}.{$y}"];
            }

            return ["{$mPadded}.{$y}"];
        }

        return [$yearMonth];
    }
}
