<?php

namespace Tests\Feature;

use App\Models\ChatHistory;
use App\Models\SapHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmartAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_unauthenticated_user_from_smart_assistant(): void
    {
        $response = $this->get(route('assistant.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_it_renders_smart_assistant_page_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('assistant.index'));

        $response->assertOk();
        $response->assertSee('Smart Assistant');
    }

    public function test_it_successfully_chats_with_ai_gateway(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://api.relink-gateway.biz.id/v1/messages' => Http::response([
                'id' => 'msg_test_1',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Halo! Ada yang bisa saya bantu terkait mesin hari ini?',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('assistant.chat'), [
            'message' => 'Halo MTN-AI',
        ]);

        $response->assertOk();
        $response->assertJson([
            'reply' => 'Halo! Ada yang bisa saya bantu terkait mesin hari ini?',
        ]);

        // Verifikasi chat tersimpan di database dengan user_id yang sesuai
        $this->assertDatabaseHas('chat_histories', [
            'user_id' => $user->id,
            'user_message' => 'Halo MTN-AI',
        ]);
    }

    public function test_it_executes_native_function_calling_for_database_query(): void
    {
        $user = User::factory()->create();

        SapHistory::create([
            'date' => '2026-01-10',
            'month' => '01.2026',
            'week' => 'W2',
            'functional_location' => 'KA-WWD-NDB-3006',
            'maint_order_desc' => 'Motor trip',
            'code_cause' => 'Overheat',
            'problem' => 'Motor mati mendadak',
            'activity' => 'Penggantian bearing',
            'status' => 'COMP',
            'bm_hrs' => 4.2,
        ]);

        Http::fake([
            'https://api.relink-gateway.biz.id/v1/messages' => Http::response([
                'id' => 'msg_tool_call',
                'type' => 'message',
                'role' => 'assistant',
                'stop_reason' => 'tool_use',
                'content' => [
                    [
                        'type' => 'tool_use',
                        'id' => 'tool_123',
                        'name' => 'query_sap_database',
                        'input' => [
                            'sql' => "SELECT date, functional_location, maint_order_desc, bm_hrs FROM sap_histories WHERE functional_location LIKE '%NDB-3006%'",
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('assistant.chat'), [
            'message' => 'Cek breakdown mesin NDB-3006',
        ]);

        $response->assertOk();
        $reply = $response->json('reply');
        $this->assertStringContainsString('KA-WWD-NDB-3006', $reply);
        $this->assertStringContainsString('4.2 jam', $reply);
    }

    public function test_it_executes_sql_and_returns_summary_when_ai_generates_sql_tags(): void
    {
        $user = User::factory()->create();

        SapHistory::create([
            'date' => '2026-01-10',
            'month' => '01.2026',
            'week' => 'W2',
            'functional_location' => 'KA-WWD-NDB-3006',
            'maint_order_desc' => 'Motor trip',
            'code_cause' => 'Overheat',
            'problem' => 'Motor mati mendadak',
            'activity' => 'Penggantian bearing',
            'status' => 'COMP',
            'bm_hrs' => 2.5,
        ]);

        Http::fake([
            'https://api.relink-gateway.biz.id/v1/messages' => Http::response([
                'id' => 'msg_sql_tag',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => "[SQL]SELECT date, functional_location, maint_order_desc, bm_hrs FROM sap_histories WHERE month = '01.2026'[/SQL]",
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('assistant.chat'), [
            'message' => 'Tampilkan data breakdown bulan 01.2026',
        ]);

        $response->assertOk();
        $reply = $response->json('reply');
        $this->assertStringContainsString('KA-WWD-NDB-3006', $reply);
        $this->assertStringContainsString('2.5 jam', $reply);
    }

    public function test_each_user_has_isolated_chat_history(): void
    {
        $user1 = User::factory()->create(['name' => 'Teknisi 1']);
        $user2 = User::factory()->create(['name' => 'Teknisi 2']);

        ChatHistory::create([
            'user_id' => $user1->id,
            'user_name' => $user1->name,
            'user_message' => 'Pertanyaan User 1',
            'ai_response' => 'Jawaban Khusus User 1',
        ]);

        ChatHistory::create([
            'user_id' => $user2->id,
            'user_name' => $user2->name,
            'user_message' => 'Pertanyaan User 2',
            'ai_response' => 'Jawaban Khusus User 2',
        ]);

        // User 1 melihat halamannya sendiri
        $response1 = $this->actingAs($user1)->get(route('assistant.index'));
        $response1->assertSee('Pertanyaan User 1');
        $response1->assertDontSee('Pertanyaan User 2');

        // User 2 melihat halamannya sendiri
        $response2 = $this->actingAs($user2)->get(route('assistant.index'));
        $response2->assertSee('Pertanyaan User 2');
        $response2->assertDontSee('Pertanyaan User 1');
    }

    public function test_user_can_clear_their_chat_history(): void
    {
        $user = User::factory()->create();

        ChatHistory::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_message' => 'Pesan yang akan dihapus',
            'ai_response' => 'Jawaban',
        ]);

        $this->actingAs($user)->postJson(route('assistant.clear'))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('chat_histories', [
            'user_id' => $user->id,
            'user_message' => 'Pesan yang akan dihapus',
        ]);
    }

    public function test_it_completes_two_step_calling_architecture_with_natural_ai_synthesis(): void
    {
        $user = User::factory()->create();

        SapHistory::create([
            'date' => '2026-01-15',
            'month' => '01.2026',
            'week' => 'W3',
            'functional_location' => 'KA-WWD-NDB-3006',
            'maint_order_desc' => 'Bearing spindle aus',
            'problem' => 'Vibrasi tinggi pada poros utama',
            'object_part' => 'BEARING',
            'executor' => 'Slamet',
            'status' => 'COMP',
            'bm_hrs' => 3.5,
        ]);

        // Simulasikan Two-Step Calling menggunakan Http::fakeSequence
        Http::fake([
            'https://api.relink-gateway.biz.id/v1/messages' => Http::sequence()
                // Step 1: AI memutuskan memanggil tool query_sap_database
                ->push([
                    'id' => 'msg_step1',
                    'type' => 'message',
                    'role' => 'assistant',
                    'stop_reason' => 'tool_use',
                    'content' => [
                        [
                            'type' => 'tool_use',
                            'id' => 'call_tool_ndb3006',
                            'name' => 'query_sap_database',
                            'input' => [
                                'sql' => "SELECT date, functional_location, maint_order_desc, object_part, bm_hrs FROM sap_histories WHERE functional_location LIKE '%3006%'",
                            ],
                        ],
                    ],
                ], 200)
                // Step 2: AI menerima tool_result dari database dan merangkum secara natural & komunikatif
                ->push([
                    'id' => 'msg_step2',
                    'type' => 'message',
                    'role' => 'assistant',
                    'stop_reason' => 'end_turn',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'The user asked in Indonesian. Tool result contains 3.5 hrs breakdown on bearing.',
                        ],
                        [
                            'type' => 'text',
                            'text' => "Halo Mas! Dari hasil pengecekan log SAP untuk mesin **KA-WWD-NDB-3006**, tercatat ada 1 kali breakdown pada tanggal **15 Januari 2026** dengan durasi **3,5 jam** karena bearing spindle aus.\n\nSaran teknis: Sebaiknya lakukan greasing berkala dan monitoring vibrasi setiap shift agar tidak berulang.",
                        ],
                    ],
                ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('assistant.chat'), [
            'message' => 'Tolong cek riwayat kerusakan mesin NDB-3006 di bulan Januari 2026',
        ]);

        $response->assertOk();
        $reply = $response->json('reply');

        // Pastikan internal monologue tersaring dan jawaban natural AI tampil
        $this->assertStringNotContainsString('The user asked in Indonesian', $reply);
        $this->assertStringContainsString('KA-WWD-NDB-3006', $reply);
        $this->assertStringContainsString('3,5 jam', $reply);
        $this->assertStringContainsString('Saran teknis', $reply);

        // Verifikasi tepat 2 kali panggilan API (Two-Step Calling) dilakukan
        Http::assertSentCount(2);

        // Verifikasi tersimpan ke chat history
        $this->assertDatabaseHas('chat_histories', [
            'user_id' => $user->id,
            'user_message' => 'Tolong cek riwayat kerusakan mesin NDB-3006 di bulan Januari 2026',
            'ai_response' => $reply,
        ]);
    }
}
