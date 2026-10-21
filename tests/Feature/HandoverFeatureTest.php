<?php

namespace Tests\Feature;

use App\Models\Handover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoverFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_handover_index_page(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Handover::create([
            'handover_date' => '2026-10-21',
            'shift' => '1',
            'team' => 'A',
            'machine_name' => 'MESIN COMPRESSOR 1',
            'problem_description' => 'Tekanan angin drop',
            'lototo_status' => 'Sudah',
            'status' => 'OPEN',
            'reported_by' => 'Teknisi 1',
        ]);

        $response = $this->actingAs($user)->get(route('handover.index'));

        $response->assertOk();
        $response->assertSee('MESIN COMPRESSOR 1');
        $response->assertSee('Tekanan angin drop');
        $response->assertSee('SUDAH');
        $response->assertSee('Tim A');
    }

    public function test_it_stores_new_handover_with_lototo_status(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
            'name' => 'Bagus',
        ]);

        $response = $this->actingAs($user)->post(route('handover.store'), [
            'handover_date' => '2026-10-21',
            'shift' => '2',
            'team' => 'B',
            'machine_name' => 'NDB 3006',
            'problem_description' => 'Motor overheat',
            'lototo_status' => 'Sudah',
        ]);

        $response->assertRedirect(route('handover.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('handovers', [
            'machine_name' => 'NDB 3006',
            'shift' => '2',
            'team' => 'B',
            'lototo_status' => 'Sudah',
            'status' => 'OPEN',
            'reported_by' => 'Bagus',
        ]);
    }

    public function test_it_resolves_open_handover(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
            'name' => 'Teknisi Lanjutan',
        ]);

        $handover = Handover::create([
            'handover_date' => '2026-10-21',
            'shift' => '1',
            'team' => 'A',
            'machine_name' => 'POMPA AIR 2',
            'problem_description' => 'Seal bocor',
            'lototo_status' => 'Belum',
            'status' => 'OPEN',
            'reported_by' => 'Teknisi Pagi',
        ]);

        $response = $this->actingAs($user)->post(route('handover.resolve', $handover->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('CLOSED', $handover->fresh()->status);
        $this->assertEquals('Teknisi Lanjutan', $handover->fresh()->resolved_by);
        $this->assertNotNull($handover->fresh()->resolved_at);
    }

    public function test_it_exports_handover_to_excel_including_lototo_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Handover::create([
            'handover_date' => '2026-10-21',
            'shift' => '3',
            'team' => 'C',
            'machine_name' => 'BLOWER 1',
            'problem_description' => 'Vibrasi tinggi',
            'lototo_status' => 'Sudah',
            'status' => 'OPEN',
            'reported_by' => 'Teknisi Malam',
        ]);

        $response = $this->actingAs($admin)->get(route('export.handover'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.ms-excel');
        $response->assertSee('Laporan Shift Handover Logbook');
        $response->assertSee('BLOWER 1');
        $response->assertSee('Sudah');
    }
}
