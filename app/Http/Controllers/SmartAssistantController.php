<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SmartAssistantController extends Controller
{
    // 1. Menampilkan halaman Chat UI
    public function index()
    {
        return view('assistant.index');
    }

    // 2. Memproses pesan dari user ke Gemini API
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $userMessage = $request->message;
        $apiKey      = env('GEMINI_API_KEY');

        // Endpoint terbaru: v1beta + key via header X-goog-api-key
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent";

        // 1. SYSTEM CONTEXT (Beri tahu AI tentang skema tabel database)
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

        // Eksekusi Panggilan Pertama ke AI
        $aiReply = $this->callGemini($url, $apiKey, $systemContext, $userMessage);

        if (strpos($aiReply, 'ERROR_API:') !== false) {
            return response()->json(['reply' => 'Gagal terhubung ke API: ' . str_replace('ERROR_API:', '', $aiReply)], 500);
        }

        // 2. DETEKSI APAKAH AI MENGELUARKAN KUERI SQL
        // Fix: tanda [ dan ] di-escape agar tidak dianggap karakter class regex
        if (preg_match('/\[SQL\](.*?)\[\/SQL\]/is', $aiReply, $matches)) {
            $sqlQuery = trim($matches[1]);

            try {
                // Batasi eksekusi hanya untuk kueri SELECT demi keamanan (mencegah DROP/DELETE)
                if (stripos($sqlQuery, 'SELECT') !== 0) {
                    throw new \Exception("Hanya operasi SELECT yang diizinkan.");
                }

                // Eksekusi SQL di Laravel
                $queryResults = DB::select($sqlQuery);

                // Ubah hasil query jadi format JSON (dibatasi agar tidak terlalu berat)
                $dataJson = json_encode(array_slice($queryResults, 0, 100)); // Maksimal 100 baris untuk AI

                // 3. PANGGILAN KEDUA: Minta AI merangkum datanya
                $followUpContext = "Kamu adalah Bagus-AI. User bertanya: '$userMessage'.
Berikut adalah data mentah dari database (format JSON):
$dataJson

Tugasmu: Buatlah summary atau jawaban dari data tersebut.
Jelaskan dengan rapi menggunakan list atau tabel (Markdown). Jika datanya kosong, beri tahu user bahwa data tidak ditemukan di SAP.";

                $finalReply = $this->callGemini($url, $apiKey, $followUpContext, "Tolong rangkum data tersebut.");

                return response()->json(['reply' => $finalReply]);

            } catch (\Exception $e) {
                // Jika SQL error atau data gagal ditarik
                return response()->json([
                    'reply' => "Saya mencoba mencari di database namun terjadi kesalahan kueri.<br><br><b>Detail Error System:</b> " . $e->getMessage() . "<br><br><b>Kueri yang dicoba:</b> <code>$sqlQuery</code>"
                ]);
            }
        }

        // Jika balasan pertama bukan SQL (hanya chat biasa)
        return response()->json(['reply' => $aiReply]);
    }

    /**
     * Helper Function untuk mengirim request ke Gemini (agar kode lebih rapi)
     * API key dikirim via header X-goog-api-key (format autentikasi terbaru Google)
     */
    private function callGemini($url, $apiKey, $instruction, $userText)
    {
        $payload = [
            "system_instruction" => [
                "parts" => [["text" => $instruction]]
            ],
            "contents" => [
                [
                    "role"  => "user",
                    "parts" => [["text" => $userText]]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.1 // Temperature rendah agar AI tidak mengarang kueri SQL
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type'   => 'application/json',
                'X-goog-api-key' => $apiKey,
            ])->timeout(60)->post($url, $payload);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? "Maaf, saya tidak bisa memproses jawaban saat ini.";
            } else {
                return 'ERROR_API:' . $response->body();
            }
        } catch (\Exception $e) {
            return 'ERROR_API:' . $e->getMessage();
        }
    }
}
