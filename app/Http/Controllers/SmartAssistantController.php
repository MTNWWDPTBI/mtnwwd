<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SmartAssistantController extends Controller
{
    /**
     * Tampilkan halaman Chat UI Smart Assistant.
     */
    public function index(): View
    {
        return view('assistant.index');
    }

    /**
     * Memproses pesan dari user ke AI Gateway dan database SAP.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->string('message')->trim()->value();

        $systemContext = "Kamu adalah 'Bagus-AI', Asisten Cerdas Maintenance Pabrik Bekaert.
Kamu memiliki akses ke tabel MySQL bernama `sap_histories` yang berisi riwayat kerusakan mesin (ribuan baris).
Kolom penting di tabel `sap_histories`:
- `date` (format YYYY-MM-DD), `month` (misal 01.2026), `week`
- `functional_location` (kode/nama mesin, misal KA-WWD-NDB-3006)
- `maint_order_desc` (deskripsi laporan singkat)
- `code_cause` (penyebab utama kerusakan)
- `problem` (detail masalah)
- `activity` (tindakan perbaikan)
- `status` (status pekerjaan, misal COMP)
- `bm_hrs` (angka desimal total jam mesin breakdown/mati)

ATURAN SANGAT PENTING:
Jika pertanyaan user memerlukan pencarian data, perhitungan, atau summary dari riwayat mesin, kamu WAJIB membalas HANYA dengan kueri MySQL yang diapit oleh tag [SQL] dan [/SQL]. Jangan tambahkan teks lain.
Gunakan klausa LIKE '%...%' untuk pencarian nama mesin.
Contoh balasan jika butuh data:
[SQL]SELECT functional_location, code_cause, bm_hrs FROM sap_histories WHERE functional_location LIKE '%NDB-3006%' AND month = '01.2026' ORDER BY bm_hrs DESC LIMIT 50[/SQL]

Jika pertanyaan user hanya sapaan santai atau tidak butuh data database, jawablah menggunakan bahasa Indonesia yang natural, profesional, dan ringkas layaknya engineer.";

        $aiReply = $this->callAi($systemContext, $userMessage);

        if (str_starts_with($aiReply, 'ERROR_API:')) {
            return response()->json([
                'reply' => 'Gagal terhubung ke AI Service: '.str_replace('ERROR_API:', '', $aiReply),
            ], 500);
        }

        if (preg_match('/\[SQL\](.*?)\[\/SQL\]/is', $aiReply, $matches)) {
            $sqlQuery = trim($matches[1]);

            try {
                if (stripos($sqlQuery, 'SELECT') !== 0) {
                    throw new \Exception('Hanya operasi SELECT yang diizinkan untuk keamanan data.');
                }

                $queryResults = DB::select($sqlQuery);
                $dataJson = json_encode(array_slice($queryResults, 0, 100));

                $followUpContext = "Kamu adalah Bagus-AI. User bertanya: '{$userMessage}'.
Berikut adalah data mentah dari database (format JSON):
{$dataJson}

Tugasmu: Buatlah summary atau jawaban dari data tersebut.
Jelaskan dengan rapi menggunakan list atau tabel (Markdown). Jika datanya kosong, beri tahu user bahwa data tidak ditemukan di SAP.";

                $finalReply = $this->callAi($followUpContext, 'Tolong rangkum data tersebut.');

                return response()->json(['reply' => $finalReply]);
            } catch (\Throwable $e) {
                Log::error('SmartAssistant SQL error: '.$e->getMessage(), [
                    'query' => $sqlQuery,
                ]);

                return response()->json([
                    'reply' => "Saya mencoba mencari di database namun terjadi kesalahan kueri.<br><br><b>Detail Error System:</b> {$e->getMessage()}<br><br><b>Kueri yang dicoba:</b> <code>{$sqlQuery}</code>",
                ]);
            }
        }

        return response()->json(['reply' => $aiReply]);
    }

    /**
     * Kirim request ke AI Gateway (kompatibel dengan OpenAI Chat Completions API).
     */
    private function callAi(string $systemInstruction, string $userText): string
    {
        $baseUrl = config('services.ai_gateway.base_url', 'https://api.relink-gateway.biz.id/v1');
        $apiKey = config('services.ai_gateway.api_key');
        $model = config('services.ai_gateway.model', 'deepseek-v4-flash');
        $timeout = (int) config('services.ai_gateway.timeout', 45);

        if (empty($apiKey)) {
            return 'ERROR_API: AI_API_KEY belum dikonfigurasi di file .env.';
        }

        $endpoint = rtrim($baseUrl, '/').'/chat/completions';

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemInstruction,
                ],
                [
                    'role' => 'user',
                    'content' => $userText,
                ],
            ],
            'temperature' => 0.1,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($timeout)->post($endpoint, $payload);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                return ! empty($content) ? trim($content) : 'Maaf, saya tidak menerima respons yang valid saat ini.';
            }

            Log::warning('AI Gateway error response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'ERROR_API:'.($response->json('error.message') ?? $response->body());
        } catch (\Throwable $e) {
            Log::error('AI Gateway exception: '.$e->getMessage());

            return 'ERROR_API:'.$e->getMessage();
        }
    }
}
