<?php

namespace Tests\Feature;

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
            'https://api.relink-gateway.biz.id/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Halo! Ada yang bisa saya bantu terkait mesin hari ini?',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('assistant.chat'), [
            'message' => 'Halo Bagus-AI',
        ]);

        $response->assertOk();
        $response->assertJson([
            'reply' => 'Halo! Ada yang bisa saya bantu terkait mesin hari ini?',
        ]);
    }

    public function test_it_executes_sql_and_returns_summary_when_ai_generates_sql(): void
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
            'https://api.relink-gateway.biz.id/v1/chat/completions' => Http::sequence()
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'content' => "[SQL]SELECT functional_location, code_cause, bm_hrs FROM sap_histories WHERE month = '01.2026'[/SQL]",
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Mesin KA-WWD-NDB-3006 mengalami breakdown selama 2.5 jam akibat Overheat.',
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('assistant.chat'), [
            'message' => 'Tampilkan data breakdown bulan 01.2026',
        ]);

        $response->assertOk();
        $response->assertJson([
            'reply' => 'Mesin KA-WWD-NDB-3006 mengalami breakdown selama 2.5 jam akibat Overheat.',
        ]);
    }
}
