<?php

namespace Tests\Feature;

use App\Models\SapHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_accessing_import_data(): void
    {
        $response = $this->get(route('import.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_import_data(): void
    {
        $user = User::factory()->create(['role' => 'teknisi']);

        $response = $this->actingAs($user)->get(route('import.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_import_data_with_sap_stats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Insert sample SAP history
        SapHistory::create([
            'date' => '2026-03-25',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-NDB-3006',
            'object_part' => 'MOTOR',
            'bm_hrs' => '2.50',
            'created_at' => '2026-03-26 14:30:00',
        ]);
        SapHistory::create([
            'date' => '2026-03-31',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-NDB-3007',
            'object_part' => 'BEARING',
            'bm_hrs' => '1.00',
            'created_at' => '2026-04-01 09:15:00',
        ]);

        $response = $this->actingAs($admin)->get(route('import.index'));

        $response->assertStatus(200);
        $response->assertViewHas('sapStats', function ($stats) {
            return $stats['total_rows'] === 2
                && $stats['latest_date'] === '2026-03-31'
                && ! empty($stats['last_uploaded_at']);
        });

        $response->assertSee('Status Data & Riwayat Upload Terakhir', false);
        $response->assertSee('Total Baris Data');
        $response->assertSee('31 Mar 2026');
    }
}
