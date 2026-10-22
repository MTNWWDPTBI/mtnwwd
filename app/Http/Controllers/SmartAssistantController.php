<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SmartAssistantController extends Controller
{
    /**
     * Tampilkan halaman Chat UI Smart Assistant dengan riwayat chat akun yang aktif.
     */
    public function index(): View
    {
        $userId = auth()->id();
        $histories = ChatHistory::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->take(50)
            ->get();

        return view('assistant.index', compact('histories'));
    }

    /**
     * Ambil riwayat chat terbaru untuk akun saat ini (AJAX).
     */
    public function getHistory(): JsonResponse
    {
        $userId = auth()->id();
        $histories = ChatHistory::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->take(50)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $histories,
        ]);
    }

    /**
     * Bersihkan riwayat chat khusus untuk akun saat ini.
     */
    public function clearHistory(): JsonResponse
    {
        $userId = auth()->id();
        ChatHistory::where('user_id', $userId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Riwayat obrolan Anda berhasil dibersihkan.',
        ]);
    }

    /**
     * Memproses pesan dari user ke AI Gateway menggunakan Claude/Anthropic Messages API
     * dengan dukungan Multi-turn Memory, Function Calling, dan Visualisasi Grafik.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->string('message')->trim()->value();
        $user = auth()->user();
        $userId = $user->id;

        // Ambil riwayat chat akun ini untuk Contextual Memory (maks 8 percakapan terakhir)
        $pastChats = ChatHistory::where('user_id', $userId)
            ->latest('id')
            ->take(8)
            ->get()
            ->reverse();

        $messages = [];
        foreach ($pastChats as $chat) {
            $messages[] = [
                'role' => 'user',
                'content' => mb_substr($chat->user_message, 0, 500),
            ];

            // Bersihkan tag CHART dan batasi panjang respons AI sebelumnya agar konteks tidak bloated
            $cleanAiResponse = preg_replace('/\[CHART\].*?\[\/CHART\]/is', '[Grafik]', $chat->ai_response);
            if (mb_strlen($cleanAiResponse) > 500) {
                $cleanAiResponse = mb_substr($cleanAiResponse, 0, 500).'...';
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $cleanAiResponse,
            ];
        }

        // Tambahkan pesan user saat ini
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        $systemInstruction = $this->buildSystemPrompt();
        $tools = $this->getToolsDefinition();

        // 1. Panggil AI Gateway (Step 1: Analisis Intensi & Tool Calling)
        $aiResult = $this->callAnthropicApi($systemInstruction, $messages, $tools);

        $contentBlocks = $aiResult['content'] ?? [];
        $toolUseBlock = null;

        foreach ($contentBlocks as $block) {
            if (($block['type'] ?? '') === 'tool_use' && ! empty($block['name']) && $toolUseBlock === null) {
                $toolUseBlock = $block;
            }
        }

        $initialText = $this->extractCleanAiResponse($contentBlocks);
        $finalReply = '';
        $extractedSql = null;
        $chartData = null;

        // 2. Deteksi kueri SQL dari blok tool_use
        if ($toolUseBlock) {
            $toolName = $toolUseBlock['name'] ?? '';
            $toolInput = $toolUseBlock['input'] ?? [];

            if ($toolName === 'query_sap_database') {
                $extractedSql = trim($toolInput['sql'] ?? '');
            } elseif ($toolName === 'render_chart') {
                $chartData = $toolInput;
            }
        }

        // 3. Deteksi kueri SQL dari tag [SQL] di respons teks jika tool_use tidak dipakai
        if (! $extractedSql && preg_match('/\[SQL\](.*?)\[\/SQL\]/is', $initialText, $matches)) {
            $extractedSql = trim($matches[1]);
        }

        // 4. Fallback cerdas: Jika user jelas meminta data mesin tapi AI tidak menghasilkan SQL
        if (! $extractedSql) {
            $extractedSql = $this->inferQueryFromIntent($userMessage, $pastChats);
        }

        // 5. Eksekusi Kueri jika ada (Arsitektur Two-Step Calling)
        if ($extractedSql) {
            $queryResult = $this->executeSafeQuery($extractedSql);

            if (isset($queryResult['error'])) {
                $finalReply = '⚠️ Kendala Kueri: '.$queryResult['error']."\n\nKueri: `{$extractedSql}`";
            } else {
                // ========================================================
                // ARSITEKTUR TWO-STEP CALLING:
                // Mengirimkan hasil riil database kembali ke AI Gateway via tool_result
                // agar AI dapat menalar, merangkum angka, dan berkomunikasi
                // secara natural, hangat, dan solutif seperti manusia.
                // ========================================================
                $step2Messages = $messages;

                if ($toolUseBlock) {
                    $step2Messages[] = [
                        'role' => 'assistant',
                        'content' => $contentBlocks,
                    ];
                    $step2Messages[] = [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'tool_result',
                                'tool_use_id' => $toolUseBlock['id'] ?? 'call_query_sap',
                                'content' => json_encode([
                                    'query_executed' => $extractedSql,
                                    'row_count' => count($queryResult),
                                    'data' => $queryResult,
                                ]),
                            ],
                        ],
                    ];
                } else {
                    $step2Messages[] = [
                        'role' => 'assistant',
                        'content' => "Saya mencari data tersebut dari database SAP dengan kueri: `{$extractedSql}`",
                    ];
                    $step2Messages[] = [
                        'role' => 'user',
                        'content' => 'Berikut data riwayat SAP yang ditemukan ('.count($queryResult)." baris):\n".json_encode($queryResult)."\n\nAnalisis data ini dan sampaikan hasilnya secara ramah, komunikatif, dan alami dalam Bahasa Indonesia layaknya engineer berpengalaman.",
                    ];
                }

                $step2Instruction = $systemInstruction."\n\n".
                'PANDUAN STEP 2 (SINTESIS DATA & KOMUNIKASI MANUSIAWI):
Data maintenance dari database SAP sudah didapatkan di atas. Sekarang tugasmu adalah memberikan jawaban akhir kepada user secara natural, manusiawi, ramah, dan solutif seperti engineer maintenance berpengalaman:
1. Sampaikan poin inti / angka utama secara lugas dengan gaya bahasa santai namun profesional (jangan kaku seperti template).
2. Tampilkan rincian data (gunakan tabel Markdown atau daftar poin ringkas bila ada lebih dari 1 baris).
3. Berikan insight engineering praktis (analisis potensi akar penyebab dan rekomendasi pencegahan atau pemeriksaan).
4. Jika user meminta grafik visual atau data berupa perbandingan/ranking, sertakan blok [CHART]{"chart_type":"bar"|"line"|"pie","title":"...","labels":[...],"dataset_label":"...","data":[...]} [/CHART] di baris paling akhir.
5. JANGAN memanggil tool lagi. Langsung sampaikan jawaban lengkapmu dalam Bahasa Indonesia.';

                $step2Result = $this->callAnthropicApi($step2Instruction, $step2Messages, []);

                if (! empty($step2Result['content'])) {
                    $extractedText = $this->extractCleanAiResponse($step2Result['content']);
                    // Pastikan respons Step 2 bukan hanya pantulan tag [SQL] mentah
                    if (! preg_match('/^\s*\[SQL\].*?\[\/SQL\]\s*$/is', $extractedText)) {
                        $finalReply = $extractedText;
                    }
                }

                // Fallback jika panggilan Step 2 timeout atau menghasilkan teks kosong
                if (empty(trim($finalReply))) {
                    $finalReply = $this->formatDatabaseReport($extractedSql, $queryResult, $userMessage);
                } else {
                    // Enrich chart otomatis jika user minta grafik/ranking tapi AI belum menyertakannya
                    $finalReply = $this->enrichChartIfAppropriate($finalReply, $userMessage, $queryResult);
                }
            }
        } elseif (! empty($chartData)) {
            $cleanText = trim($initialText) ?: 'Berikut grafik visualisasi data yang diminta:';
            $finalReply = $cleanText."\n\n[CHART]".json_encode($chartData).'[/CHART]';
        } else {
            $finalReply = trim($initialText);
        }

        if (empty(trim($finalReply))) {
            $finalReply = 'Halo! Saya **MTN-AI Bekaert Indonesia**. Ada data mesin, breakdown, atau log maintenance SAP yang bisa saya bantu analisa hari ini?';
        }

        // 6. Simpan ke Database ChatHistory khusus untuk akun yang sedang login
        ChatHistory::create([
            'user_id' => $userId,
            'user_name' => $user->name,
            'user_message' => $userMessage,
            'ai_response' => $finalReply,
        ]);

        return response()->json([
            'reply' => $finalReply,
        ]);
    }

    /**
     * Deteksi intensi pencarian database jika AI gagal menghasilkan SQL atau query kosong.
     */
    private function inferQueryFromIntent(string $userMessage, $pastChats): ?string
    {
        // 1. Cari nomor mesin dari pesan saat ini atau dari konteks riwayat obrolan
        $machine = null;
        if (preg_match('/(?:mesin|unit)?\s*(KA-WWD-NDB-[0-9]{4}|NDB-[0-9]{4}|[0-9]{4})/i', $userMessage, $m)) {
            $val = strtoupper($m[1]);
            if ($val !== '2026' && $val !== '2025' && $val !== '2024') {
                $machine = $val;
            }
        }

        if (! $machine && $pastChats) {
            foreach ($pastChats->reverse() as $past) {
                if (preg_match('/(?:mesin|unit)?\s*(KA-WWD-NDB-[0-9]{4}|NDB-[0-9]{4}|[0-9]{4})/i', $past->user_message.' '.$past->ai_response, $pm)) {
                    $val = strtoupper($pm[1]);
                    if ($val !== '2026' && $val !== '2025' && $val !== '2024') {
                        $machine = $val;
                        break;
                    }
                }
            }
        }

        // Intensi A: Menanyakan teknisi / executor
        if (preg_match('/(teknisi|executor|siapa.*perbaiki|siapa.*ngerjain|personel)/i', $userMessage)) {
            if ($machine) {
                return "SELECT executor, COUNT(*) as frekuensi, SUM(actual_hrs) as total_jam FROM sap_histories WHERE functional_location LIKE '%{$machine}%' AND executor IS NOT NULL AND executor != '' GROUP BY executor ORDER BY frekuensi DESC LIMIT 20";
            }

            return 'SELECT executor, COUNT(*) as frekuensi, SUM(actual_hrs) as total_jam FROM sap_histories WHERE executor IS NOT NULL AND executor != \'\' GROUP BY executor ORDER BY frekuensi DESC LIMIT 10';
        }

        // Intensi B: Menanyakan breakdown mesin tertentu
        if ($machine && preg_match('/(breakdown|rusak|masalah|problem|log|riwayat|downtime)/i', $userMessage)) {
            return "SELECT date, functional_location, maint_order, maint_order_desc, object_part, problem, creator, executor, bm_hrs, actual_hrs, status FROM sap_histories WHERE functional_location LIKE '%{$machine}%' ORDER BY date DESC LIMIT 50";
        }

        // Intensi C: Top mesin breakdown / ranking breakdown
        if (preg_match('/(top\s*\d*|paling\s*sering|terlama|tertinggi|ranking.*mesin)/i', $userMessage)) {
            return 'SELECT functional_location, SUM(bm_hrs) as total_bm, COUNT(*) as frekuensi FROM sap_histories GROUP BY functional_location ORDER BY total_bm DESC LIMIT 5';
        }

        // Intensi D: Kerusakan komponen part (object_part)
        if (preg_match('/(komponen|part|object_part)/i', $userMessage)) {
            if ($machine) {
                return "SELECT object_part, COUNT(*) as frekuensi, SUM(bm_hrs) as total_bm FROM sap_histories WHERE functional_location LIKE '%{$machine}%' AND object_part IS NOT NULL AND object_part != '' GROUP BY object_part ORDER BY frekuensi DESC LIMIT 10";
            }

            return 'SELECT object_part, COUNT(*) as frekuensi, SUM(bm_hrs) as total_bm FROM sap_histories WHERE object_part IS NOT NULL AND object_part != \'\' GROUP BY object_part ORDER BY frekuensi DESC LIMIT 10';
        }

        return null;
    }

    /**
     * Format hasil query database menjadi laporan Markdown rapi dengan metrik, tabel, dan grafik Chart.js.
     */
    private function formatDatabaseReport(string $sql, array $results, string $userQuestion = ''): string
    {
        if (empty($results)) {
            return "🔍 **Hasil Pencarian Database:**\n\nTidak ditemukan data riwayat di database `sap_histories` untuk kueri tersebut.\n\n*Kueri SQL:* `{$sql}`";
        }

        $firstRow = (array) $results[0];
        $keys = array_keys($firstRow);

        // Cek apakah query berupa agregasi (GROUP BY)
        $hasAggregation = false;
        foreach ($keys as $k) {
            if (preg_match('/(total|sum|count|avg|frekuensi|breakdown_count)/i', $k)) {
                $hasAggregation = true;
                break;
            }
        }

        if ($hasAggregation) {
            return $this->formatAggregationReport($sql, $results, $userQuestion);
        }

        return $this->formatDetailedRecordsReport($sql, $results, $userQuestion);
    }

    /**
     * Format laporan untuk query agregasi / perbandingan (GROUP BY).
     */
    private function formatAggregationReport(string $sql, array $results, string $userQuestion): string
    {
        $firstRow = (array) $results[0];
        $keys = array_keys($firstRow);

        $labelKey = $keys[0];
        $valKey = null;
        foreach ($keys as $k) {
            if (is_numeric($firstRow[$k])) {
                $valKey = $k;
                break;
            }
        }

        $headers = array_map(fn ($k) => ucwords(str_replace('_', ' ', $k)), $keys);
        $headerLine = '| No | '.implode(' | ', $headers).' |';
        $separatorLine = '|:---|'.implode('|', array_fill(0, count($headers), ':---')).'|';

        $rowsMarkdown = [];
        $chartLabels = [];
        $chartValues = [];

        foreach ($results as $idx => $item) {
            $rowArr = (array) $item;
            $rowValues = array_map(fn ($k) => $rowArr[$k] ?? '-', $keys);
            $rowsMarkdown[] = '| '.($idx + 1).' | '.implode(' | ', $rowValues).' |';

            if ($valKey && count($chartLabels) < 8) {
                $chartLabels[] = (string) ($rowArr[$labelKey] ?? 'Item '.($idx + 1));
                $chartValues[] = (float) ($rowArr[$valKey] ?? 0);
            }
        }

        $output = "### 📊 Hasil Analisis Data Maintenance (MTN-AI Bekaert Indonesia)\n\n";
        $output .= "Berikut ringkasan data hasil penelusuran database SAP:\n\n";
        $output .= $headerLine."\n".$separatorLine."\n".implode("\n", $rowsMarkdown)."\n\n";

        if (! empty($chartLabels)) {
            $chartTitle = 'Grafik Perbandingan '.ucwords(str_replace('_', ' ', $valKey ?? 'Metrik'));
            $chartJson = json_encode([
                'chart_type' => 'bar',
                'title' => $chartTitle,
                'labels' => $chartLabels,
                'dataset_label' => ucwords(str_replace('_', ' ', $valKey ?? 'Nilai')),
                'data' => $chartValues,
            ]);
            $output .= "[CHART]{$chartJson}[/CHART]\n\n";
        }

        $topLabel = $chartLabels[0] ?? '';
        if ($topLabel) {
            $output .= "> 💡 **Insight MTN-AI:** Nilai tertinggi tercatat pada **{$topLabel}** sebesar **{$chartValues[0]}**. Disarankan untuk memprioritaskan tindakan pencegahan pada unit ini.\n";
        }

        return $output;
    }

    /**
     * Format laporan untuk query riwayat kerusakan / detail mesin.
     */
    private function formatDetailedRecordsReport(string $sql, array $results, string $userQuestion): string
    {
        $machineName = $results[0]->functional_location ?? null;
        if (! $machineName) {
            if (preg_match('/(?:mesin|unit)\s*([0-9]{4}|NDB-[0-9]{4}|KA-WWD-NDB-[0-9]{4})/i', $userQuestion, $m)) {
                $machineName = strtoupper($m[1]);
                if (! str_contains($machineName, 'NDB-') && strlen($machineName) === 4) {
                    $machineName = "KA-WWD-NDB-{$machineName}";
                }
            } elseif (preg_match('/(KA-WWD-NDB-[0-9]{4}|NDB-[0-9]{4})/i', $userQuestion, $m)) {
                $machineName = strtoupper($m[1]);
            } else {
                $machineName = 'Mesin Terkait';
            }
        }

        $totalBm = 0;
        $totalActual = 0;
        $partsCount = [];
        $ordersCount = [];
        $tableRows = [];
        $chartLabels = [];
        $chartData = [];

        foreach ($results as $row) {
            $r = (array) $row;
            $bm = (float) ($r['bm_hrs'] ?? 0);
            $act = (float) ($r['actual_hrs'] ?? 0);
            $totalBm += $bm;
            $totalActual += $act;

            $part = $r['object_part'] ?? '-';
            if ($part && $part !== '-') {
                $partsCount[$part] = ($partsCount[$part] ?? 0) + 1;
            }

            $order = $r['maint_order'] ?? '-';
            if ($order !== '-') {
                $ordersCount[$order] = true;
            }

            if (count($tableRows) < 15) {
                $date = $r['date'] ?? '-';
                $desc = $r['maint_order_desc'] ?? ($r['problem'] ?? '-');
                $exec = ($r['executor'] ?? null) ?: (($r['creator'] ?? null) ?: '-');
                $bmStr = $bm > 0 ? "{$bm} jam" : '-';
                $status = $r['status'] ?? '-';
                $tableRows[] = "| {$date} | `{$order}` | {$part} | {$desc} | {$exec} | **{$bmStr}** | {$status} |";
            }

            if ($bm > 0 && count($chartLabels) < 8) {
                $chartLabels[] = ($r['date'] ?? '').' ('.($r['object_part'] ?? 'Part').')';
                $chartData[] = $bm;
            }
        }

        arsort($partsCount);
        $topPart = ! empty($partsCount) ? array_key_first($partsCount) : 'Beragam';

        $output = "### 📋 Laporan Riwayat Kerusakan & Breakdown: **{$machineName}**\n\n";
        $output .= '- ⏱️ **Total Jam Breakdown (Downtime):** '.round($totalBm, 2)." jam\n";
        $output .= '- 🔧 **Total Jam Kerja Perbaikan:** '.round($totalActual, 2)." jam\n";
        $output .= '- 📑 **Total Order Maintenance:** '.count($ordersCount).' order ('.count($results)." baris catatan)\n";
        $output .= "- ⚠️ **Komponen Paling Sering Bermasalah:** `{$topPart}`\n\n";

        if (! empty($chartLabels)) {
            $chartJson = json_encode([
                'chart_type' => 'bar',
                'title' => "Downtime Breakdown (Jam) — {$machineName}",
                'labels' => $chartLabels,
                'dataset_label' => 'Jam Breakdown (Jam)',
                'data' => $chartData,
            ]);
            $output .= "[CHART]{$chartJson}[/CHART]\n\n";
        }

        $output .= "#### 📑 Riwayat Kejadian Terakhir:\n\n";
        $output .= "| Tanggal | No. Order | Komponen Part | Deskripsi / Problem | Teknisi / Pelapor | Breakdown | Status |\n";
        $output .= "|:---|:---|:---|:---|:---|:---|:---|\n";
        $output .= implode("\n", $tableRows)."\n\n";

        $output .= "> 💡 **Rekomendasi MTN-AI Bekaert Indonesia:** Pastikan pemeriksaan rutin difokuskan pada area **{$topPart}** dan verifikasi pelumasan atau penyetelan pada saat preventive maintenance.";

        return $output;
    }

    /**
     * Eksekusi query SELECT dengan proteksi keamanan.
     */
    private function executeSafeQuery(string $sql): array
    {
        if (empty($sql)) {
            return ['error' => 'Query SQL kosong.'];
        }

        // Auto-correct pencarian nomor mesin dari functional_location = 'xxx' menjadi LIKE '%xxx%'
        $sql = preg_replace("/functional_location\s*=\s*['\"]([^'\"]+)['\"]/i", "functional_location LIKE '%$1%'", $sql);

        // Auto-correct alias kolom umum yang sering digenerate oleh AI
        $sql = str_ireplace(['equipment_id', 'equipment', 'machine_id', 'mesin_id'], 'functional_location', $sql);
        $sql = str_ireplace(['created_at'], 'date', $sql);
        $sql = str_ireplace(['technician'], 'executor', $sql);

        if (stripos($sql, 'SELECT') !== 0) {
            return ['error' => 'Hanya operasi SELECT yang diizinkan untuk keamanan data.'];
        }

        // Cegah statement berbahaya
        $disallowed = ['DROP', 'TRUNCATE', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'GRANT', 'REVOKE'];
        foreach ($disallowed as $keyword) {
            if (preg_match('/\b'.$keyword.'\b/i', $sql)) {
                return ['error' => "Operasi {$keyword} tidak diizinkan demi keamanan database."];
            }
        }

        try {
            $results = DB::select($sql);

            return array_slice($results, 0, 80);
        } catch (\Throwable $e) {
            Log::error('SmartAssistant SQL execution error: '.$e->getMessage(), ['query' => $sql]);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Definisi tools untuk Anthropic Function Calling.
     */
    private function getToolsDefinition(): array
    {
        return [
            [
                'name' => 'query_sap_database',
                'description' => 'Mengeksekusi perintah SQL SELECT pada tabel sap_histories MySQL untuk mencari data historis maintenance pabrik Bekaert. Kolom mesin: functional_location. Kolom tanggal: date. Kolom teknisi: executor. Kolom komponen: object_part. Kolom downtime: bm_hrs.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'sql' => [
                            'type' => 'string',
                            'description' => 'Perintah SELECT SQL valid untuk tabel sap_histories. Pastikan selalu gunakan LIMIT maksimal 50-80.',
                        ],
                    ],
                    'required' => ['sql'],
                ],
            ],
            [
                'name' => 'render_chart',
                'description' => 'Menghasilkan konfigurasi grafik visual (Chart.js) untuk memvisualisasikan data maintenance, seperti perbandingan total jam breakdown antar mesin, frekuensi kerusakan komponen, atau tren per bulan.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'chart_type' => [
                            'type' => 'string',
                            'enum' => ['bar', 'line', 'pie', 'doughnut'],
                            'description' => 'Jenis visualisasi grafik',
                        ],
                        'title' => [
                            'type' => 'string',
                            'description' => 'Judul grafik yang informatif',
                        ],
                        'labels' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar label kategori pada sumbu X atau segmen',
                        ],
                        'dataset_label' => [
                            'type' => 'string',
                            'description' => 'Nama/keterangan dataset metrik (contoh: Total Jam Breakdown)',
                        ],
                        'data' => [
                            'type' => 'array',
                            'items' => ['type' => 'number'],
                            'description' => 'Daftar angka numerik sesuai urutan labels',
                        ],
                    ],
                    'required' => ['chart_type', 'title', 'labels', 'dataset_label', 'data'],
                ],
            ],
        ];
    }

    /**
     * Susun instruksi sistem komprehensif untuk Smart Assistant.
     */
    private function buildSystemPrompt(): string
    {
        return "Kamu adalah 'MTN-AI Bekaert Indonesia', Asisten AI Cerdas Maintenance Pabrik Bekaert Indonesia.
Kamu berkomunikasi dengan teknisi, supervisor, dan engineer dengan gaya natural, hangat, solutif, dan profesional layaknya rekan kerja manusia yang ahli di bidang maintenance.
Gunakan Bahasa Indonesia yang luwes, komunikatif, dan mudah dipahami. Hindari bahasa kaku atau mekanis seperti template robotik.

TABEL DATABASE: `sap_histories`
KOLOM LENGKAP:
- Waktu: `date` (YYYY-MM-DD), `month` (MM.YYYY, misal '01.2026'), `week`
- Mesin: `functional_location` (kode mesin lengkap: 'KA-WWD-NDB-XXXX', misal 'KA-WWD-NDB-3006'), `indicator`
- Order SAP: `maint_order` (nomor order), `maint_order_desc` (deskripsi masalah/pekerjaan)
- Part & Kerusakan: `object_part` (komponen part, misal 'MOTOR', 'BEARING', 'PULLEY'), `problem`, `code_cause`, `code_group`, `error_code`
- Personel: `creator` (pelapor), `executor` (teknisi perbaikan), `activity`, `confirmation_text` (catatan teknisi), `status`
- Pabrik: `plant` ('KA'), `area` ('WWD'), `dept` ('NDB'), `type`, `cat`
- Metrik Numerik: `bm_hrs` (jam breakdown/downtime mesin mati), `actual_hrs` (durasi kerja teknisi), `response_hrs` (waktu respon), `notfis` (frekuensi/jumlah kejadian)

PANDUAN TOOL CALLING & PENCARIAN DATA:
1. Jika pertanyaan membutuhkan data historis maintenance, panggil tool `query_sap_database` ATAU sertakan perintah SQL SELECT di dalam tag [SQL] dan [/SQL].
   PENTING:
   - Format kode mesin di database adalah 'KA-WWD-NDB-XXXX', jadi SELALU gunakan LIKE '%XXXX%' untuk kolom `functional_location` (misal: LIKE '%3006%').
   - Gunakan kolom `date` untuk tanggal (YYYY-MM-DD) dan `month` untuk bulan (MM.YYYY, misal '01.2026').
   - Gunakan kolom `executor` untuk nama teknisi perbaikan.
   - Jangan gunakan kueri berbahaya (DROP, DELETE, UPDATE, INSERT).
2. Jika pertanyaan TIDAK membutuhkan query database (misal: salam, tanya teori TPM, SOP umum, rumus OEE, saran perbaikan umum), langsung jawab secara natural dan ramah tanpa perlu memanggil tool.";
    }

    /**
     * Ekstrak teks bersih user-facing dari content blocks AI,
     * menghilangkan blok internal monologue/reasoning berbahasa Inggris dari model (seperti DeepSeek).
     */
    private function extractCleanAiResponse(array $contentBlocks): string
    {
        $textBlocks = [];
        foreach ($contentBlocks as $block) {
            if (($block['type'] ?? '') === 'text' && ! empty(trim($block['text'] ?? ''))) {
                $textBlocks[] = trim($block['text']);
            }
        }

        if (empty($textBlocks)) {
            return '';
        }

        // Jika hanya ada 1 blok teks
        if (count($textBlocks) === 1) {
            $text = $textBlocks[0];
            $text = preg_replace('/<think>.*?<\/think>/is', '', $text);
            $text = preg_replace('/<｜DSML｜.*?<\/｜DSML｜.*?>/is', '', $text);

            return trim($text);
        }

        // Jika ada beberapa blok teks (misal block 0 adalah internal thinking DeepSeek):
        // Ambil blok user-facing dari urutan terakhir yang bukan internal reasoning
        for ($i = count($textBlocks) - 1; $i >= 0; $i--) {
            $candidate = $textBlocks[$i];
            if (str_contains($candidate, '<｜DSML｜')) {
                continue;
            }
            if (preg_match('/^(The user|Need to|Let me check|Empty result|Data has duplicates|I should|Provide summary|Let me analyze)[^A-Za-z0-9]/i', $candidate)) {
                continue;
            }

            $candidate = preg_replace('/<think>.*?<\/think>/is', '', $candidate);
            $candidate = preg_replace('/<｜DSML｜.*?<\/｜DSML｜.*?>/is', '', $candidate);
            $trimmed = trim($candidate);
            if (! empty($trimmed)) {
                return $trimmed;
            }
        }

        // Fallback ke blok terakhir jika semua terfilter
        $last = end($textBlocks);
        $last = preg_replace('/<think>.*?<\/think>/is', '', $last);

        return trim($last);
    }

    /**
     * Tambahkan tag [CHART] otomatis jika user meminta visualisasi / perbandingan ranking
     * dan hasil query mengandung data agregasi numerik, tetapi AI belum menyertakannya di teks respons.
     */
    private function enrichChartIfAppropriate(string $reply, string $userMessage, array $results): string
    {
        if (str_contains($reply, '[CHART]') || empty($results) || count($results) < 2) {
            return $reply;
        }

        $wantsChart = (bool) preg_match('/(grafik|chart|visual|top\s*\d*|ranking|terbanyak|tertinggi|paling sering|perbandingan)/i', $userMessage);

        $firstRow = (array) $results[0];
        $keys = array_keys($firstRow);

        $hasAggregation = false;
        $valKey = null;
        $labelKey = $keys[0];

        foreach ($keys as $k) {
            if (preg_match('/(total|sum|count|avg|frekuensi|bm_hrs|actual_hrs)/i', $k) && is_numeric($firstRow[$k] ?? null)) {
                $hasAggregation = true;
                $valKey = $k;
                break;
            }
        }

        if (! $wantsChart && ! $hasAggregation) {
            return $reply;
        }

        if ($valKey) {
            $labels = [];
            $values = [];
            foreach (array_slice($results, 0, 8) as $idx => $row) {
                $r = (array) $row;
                $labels[] = (string) ($r[$labelKey] ?? 'Item '.($idx + 1));
                $values[] = (float) ($r[$valKey] ?? 0);
            }

            if (! empty($labels)) {
                $chartTitle = 'Grafik Perbandingan '.ucwords(str_replace('_', ' ', $valKey));
                $chartJson = json_encode([
                    'chart_type' => 'bar',
                    'title' => $chartTitle,
                    'labels' => $labels,
                    'dataset_label' => ucwords(str_replace('_', ' ', $valKey)),
                    'data' => $values,
                ]);

                return $reply."\n\n[CHART]{$chartJson}[/CHART]";
            }
        }

        return $reply;
    }

    /**
     * Kirim permintaan ke Anthropic / Claude Messages API via Laravel Http Client dengan IPv4 enforce.
     */
    private function callAnthropicApi(string $systemInstruction, array $messages, array $tools = []): array
    {
        $baseUrl = config('services.ai_gateway.base_url', 'https://api.relink-gateway.biz.id/v1');
        $apiKey = config('services.ai_gateway.api_key');
        $model = config('services.ai_gateway.model', 'auto');
        $timeout = (int) config('services.ai_gateway.timeout', 45);

        if (empty($apiKey)) {
            return ['error' => 'API Key AI belum dikonfigurasi di file .env.'];
        }

        // Normalisasi endpoint ke /v1/messages
        $cleanBase = rtrim($baseUrl, '/');
        if (! str_ends_with($cleanBase, '/v1')) {
            $cleanBase .= '/v1';
        }
        $endpoint = $cleanBase.'/messages';

        $payload = [
            'model' => $model,
            'max_tokens' => 2048,
            'system' => $systemInstruction,
            'messages' => $messages,
        ];

        if (! empty($tools)) {
            $payload['tools'] = $tools;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->withOptions([
                'force_ip_resolve' => 'v4', // Mencegah timeout IPv6 di Windows/Laragon
                'verify' => false,
            ])->timeout($timeout)->post($endpoint, $payload);

            if ($response->successful()) {
                $decoded = $response->json();

                return is_array($decoded) ? $decoded : ['error' => 'Format respons AI tidak valid.'];
            }

            $errMsg = $response->json('error.message') ?? $response->json('error') ?? "HTTP Status {$response->status()}";
            Log::warning('AI Gateway error response', ['status' => $response->status(), 'body' => $response->body()]);

            return ['error' => $errMsg];
        } catch (\Throwable $e) {
            Log::error('AI Gateway exception: '.$e->getMessage());

            return ['error' => 'Koneksi ke AI Gateway gagal: '.$e->getMessage()];
        }
    }
}
