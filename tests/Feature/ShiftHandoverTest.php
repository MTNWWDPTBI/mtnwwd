<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsappHandoverJob;
use App\Models\Handover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ShiftHandoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('handover.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_it_displays_handover_index_with_single_input_form_and_recent_records(): void
    {
        $user = User::factory()->create([
            'name' => 'Bagus Teknisi',
            'role' => 'teknisi',
        ]);

        Handover::create([
            'handover_date' => '2026-11-12',
            'shift' => '1',
            'team' => 'A',
            'machine_name' => 'MESIN DRAWING 4001',
            'problem_description' => 'Motor overheat dan vibrasi bearing tinggi',
            'lototo_status' => 'Sudah',
            'status' => 'OPEN',
            'reported_by' => 'Bagus Teknisi',
        ]);

        $response = $this->actingAs($user)->get(route('handover.index'));

        $response->assertOk();
        $response->assertSee('Input Problem Shift');
        $response->assertSee('Logbook');
        $response->assertSee('TANGGAL SHIFT');
        $response->assertSee('SHIFT KE-');
        $response->assertSee('GRUP TIM');
        $response->assertSee('NAMA MESIN / FASILITAS');
        $response->assertSee('DETAIL PROBLEM / TEMUAN');
        $response->assertSee('LOTOTO CHECKIN/CHECKOUT');
        $response->assertSee('Simpan & Oper', false);
        $response->assertSee('MESIN DRAWING 4001');
        $response->assertSee('Motor overheat dan vibrasi bearing tinggi');
        $response->assertSee('SUDAH');
        $response->assertSee('Tim A');
    }

    public function test_it_stores_single_input_problem_and_dispatches_whatsapp_job(): void
    {
        Queue::fake([SendWhatsappHandoverJob::class]);

        $user = User::factory()->create([
            'name' => 'Agus Handover',
            'role' => 'teknisi',
        ]);

        $payload = [
            'handover_date' => '2026-11-12',
            'shift' => '2',
            'team' => 'B',
            'machine_name' => 'NDB 3006',
            'problem_description' => 'Belt conveyor slip dan bearing aus.',
            'lototo_status' => 'Sudah',
        ];

        $response = $this->actingAs($user)->post(route('handover.store'), $payload);

        $response->assertRedirect(route('handover.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('handovers', [
            'machine_name' => 'NDB 3006',
            'shift' => '2',
            'team' => 'B',
            'problem_description' => 'Belt conveyor slip dan bearing aus.',
            'lototo_status' => 'Sudah',
            'status' => 'OPEN',
            'reported_by' => 'Agus Handover',
        ]);

        $saved = Handover::where('machine_name', 'NDB 3006')->first();
        $this->assertNotNull($saved);

        Queue::assertPushed(SendWhatsappHandoverJob::class, function ($job) use ($saved) {
            return $job->handover->id === $saved->id;
        });
    }

    public function test_whatsapp_job_formats_logbook_message_and_sends_http_post(): void
    {
        Config::set('services.wa_bot.url', 'http://103.169.206.184:3000/api/send-handover');
        Config::set('services.wa_bot.secret', 'TEST_SECRET_KEY');
        Config::set('services.wa_bot.group_id', '120363022406952619@g.us');

        Http::fake([
            'http://103.169.206.184:3000/api/send-handover' => Http::response(['status' => 'success'], 200),
        ]);

        $handover = Handover::create([
            'handover_date' => '2026-11-12',
            'shift' => '3',
            'team' => 'C',
            'machine_name' => 'POMPA SENTRIFUGAL 02',
            'problem_description' => 'Gland packing bocor tekanan drop',
            'lototo_status' => 'Sudah',
            'status' => 'OPEN',
            'reported_by' => 'Bagus Senior',
        ]);

        $job = new SendWhatsappHandoverJob($handover);
        $job->handle();

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'http://103.169.206.184:3000/api/send-handover'
                && ($data['secret_token'] ?? null) === 'TEST_SECRET_KEY'
                && ($data['group_id'] ?? null) === '120363022406952619@g.us'
                && str_contains($data['message'], '🚨 *LOGBOOK & PROBLEM SHIFT MAINTENANCE (WWD)*')
                && str_contains($data['message'], 'Shift 3')
                && str_contains($data['message'], 'Tim C')
                && str_contains($data['message'], 'Bagus Senior')
                && str_contains($data['message'], 'POMPA SENTRIFUGAL 02')
                && str_contains($data['message'], 'Gland packing bocor tekanan drop')
                && str_contains($data['message'], '*Sudah*')
                && str_contains($data['message'], 'OPEN');
        });
    }

    public function test_it_validates_required_fields_for_handover(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('handover.store'), [
            'handover_date' => '',
            'shift' => '',
            'machine_name' => '',
            'problem_description' => '',
            'lototo_status' => 'InvalidValue',
        ]);

        $response->assertSessionHasErrors([
            'handover_date',
            'shift',
            'machine_name',
            'problem_description',
            'lototo_status',
        ]);
    }
}
