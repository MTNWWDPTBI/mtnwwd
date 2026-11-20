<?php

namespace Tests\Feature;

use App\Models\PicMachine;
use App\Models\SapHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_analytics(): void
    {
        $response = $this->get(route('analytics.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_analytics_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Reliability & Breakdown Analytics');
        $response->assertSee('Total Breakdown');
        $response->assertSee('Total Downtime');
        $response->assertSee('MTBF');
        $response->assertSee('MTTR');
        $response->assertSee('Availability');
        $response->assertSee('Response Time');
        $response->assertSee('topDowntimeChart');
        $response->assertSee('topFrequencyChart');
        $response->assertSee('trendChart');
        $response->assertSee('topComponentsChart');
    }

    public function test_analytics_calculates_kpis_and_filters_by_pic_and_machine(): void
    {
        $picUser = User::factory()->create([
            'name' => 'Agus Subagyo',
            'role' => 'teknisi',
        ]);

        PicMachine::create([
            'user_id' => $picUser->id,
            'functional_location' => 'KA-WWD-NDB-4001',
        ]);
        PicMachine::create([
            'user_id' => $picUser->id,
            'functional_location' => 'KA-WWD-NDB-4002',
        ]);

        // Masukkan data transaksi SAP (notfis = 1 sebagai WO header)
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-4001',
            'date' => Carbon::now()->subDays(5)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Shaft',
            'notfis' => 1,
            'bm_hrs' => 5.0,
            'response_hrs' => 0.5,
            'maint_order_desc' => 'Shaft macet',
        ]);

        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-4001',
            'date' => Carbon::now()->subDays(2)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Bearing',
            'notfis' => 1,
            'bm_hrs' => 3.0,
            'response_hrs' => 0.7,
            'maint_order_desc' => 'Bearing pecah',
        ]);

        // Baris konfirmasi kerja teknisi untuk WO yang sama (notfis IS NULL, actual_hrs ada)
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-4001',
            'date' => Carbon::now()->subDays(2)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Bearing',
            'notfis' => null,
            'bm_hrs' => null,
            'actual_hrs' => 2.5,
            'confirmation_text' => 'Ganti bearing selesai',
        ]);

        // Mesin milik PIC lain
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-9999',
            'date' => Carbon::now()->subDays(1)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Motor',
            'notfis' => 1,
            'bm_hrs' => 10.0,
            'response_hrs' => 1.0,
            'maint_order_desc' => 'Motor terbakar',
        ]);

        $viewer = User::factory()->create(['name' => 'Operator']);

        // 1. Filter PIC terpilih (Agus Subagyo)
        $responsePic = $this->actingAs($viewer)->get(route('analytics.index', [
            'pic' => $picUser->id,
            'period' => '30d',
        ]));

        $responsePic->assertStatus(200);
        $responsePic->assertSee('KA-WWD-NDB-4001');
        $responsePic->assertSee('Shaft');
        $responsePic->assertSee('Bearing');

        // Total breakdown untuk PIC Agus harus tepat 2 (HANYA notfis = 1) dan downtime 8 jam (5 + 3)
        // Sedangkan actual_hrs harus 2.5 jam
        $responsePic->assertViewHas('kpis', function (array $kpis) {
            return $kpis['total_breakdown'] === 2
                && $kpis['total_downtime'] == 8.0
                && $kpis['total_actual_hours'] == 2.5;
        });

        // 2. Filter Mesin Spesifik (KA-WWD-NDB-4001)
        $responseMachine = $this->actingAs($viewer)->get(route('analytics.index', [
            'machine' => 'KA-WWD-NDB-4001',
            'period' => '7d',
        ]));

        $responseMachine->assertStatus(200);
        $responseMachine->assertViewHas('kpis', function (array $kpis) {
            return $kpis['total_breakdown'] === 2 && $kpis['total_downtime'] == 8.0;
        });
    }

    public function test_analytics_strictly_ignores_confirmation_rows_for_breakdown_count(): void
    {
        $user = User::factory()->create();

        // 1 WO dengan 1 baris header (notfis = 1) dan 3 baris konfirmasi teknisi (notfis = null)
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-TEST',
            'maint_order' => '4401111111',
            'date' => Carbon::now()->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Shaft',
            'notfis' => 1,
            'bm_hrs' => 4.0,
            'response_hrs' => 0.5,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            SapHistory::create([
                'functional_location' => 'KA-WWD-NDB-TEST',
                'maint_order' => '4401111111',
                'date' => Carbon::now()->toDateString(),
                'month' => Carbon::now()->format('m.Y'),
                'object_part' => 'Shaft',
                'notfis' => null,
                'bm_hrs' => null,
                'actual_hrs' => 1.5,
                'confirmation_text' => "Aktivitas konfirmasi ke-{$i}",
            ]);
        }

        $response = $this->actingAs($user)->get(route('analytics.index', [
            'period' => '7d',
            'machine' => 'KA-WWD-NDB-TEST',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('kpis', function (array $kpis) {
            // Breakdown HARUS 1 (bukan 4 baris), downtime HARUS 4.0, jam kerja total = 4.5 jam
            return $kpis['total_breakdown'] === 1
                && $kpis['total_downtime'] == 4.0
                && $kpis['total_actual_hours'] == 4.5
                && $kpis['mttr'] == 4.0;
        });
    }

    public function test_analytics_syncs_with_pic_report_month_filter(): void
    {
        $picUser = User::factory()->create(['name' => 'Bagus Testing']);
        PicMachine::create([
            'user_id' => $picUser->id,
            'functional_location' => 'KA-WWD-NDB-3006',
        ]);

        // Simulasikan data dengan kolom month format SAP '10.2026'
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-3006',
            'date' => '2026-10-15',
            'month' => '10.2026',
            'object_part' => 'SPINDLE',
            'notfis' => 1,
            'bm_hrs' => 6.5,
            'response_hrs' => 0.25,
        ]);

        $response = $this->actingAs($picUser)->get(route('analytics.index', [
            'pic' => $picUser->id,
            'period' => 'month',
            'month' => '2026-10',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('kpis', function (array $kpis) {
            return $kpis['total_breakdown'] === 1
                && $kpis['total_downtime'] == 6.5
                && $kpis['mttr'] == 6.5;
        });
    }

    public function test_analytics_handles_zero_breakdown_without_division_by_zero(): void
    {
        $user = User::factory()->create();

        // Database tanpa ada breakdown di periode yang difilter
        $response = $this->actingAs($user)->get(route('analytics.index', [
            'period' => '7d',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('kpis', function (array $kpis) {
            return $kpis['total_breakdown'] === 0
                && $kpis['total_downtime'] == 0.0
                && $kpis['mttr'] == 0.0
                && $kpis['availability'] == 100.0;
        });
    }

    public function test_custom_date_range_is_respected(): void
    {
        $user = User::factory()->create();

        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-4001',
            'date' => '2026-05-10',
            'object_part' => 'Gearbox',
            'notfis' => 1,
            'bm_hrs' => 4.0,
            'response_hrs' => 0.5,
        ]);

        $response = $this->actingAs($user)->get(route('analytics.index', [
            'period' => 'custom',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-15',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('kpis', function (array $kpis) {
            return $kpis['total_breakdown'] === 1 && $kpis['total_downtime'] == 4.0;
        });
    }

    public function test_sidebar_navigation_displays_analytics_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('analytics.index'));
        $response->assertSee('Analytics');
    }

    public function test_pic_dropdown_marks_logged_in_user_with_saya(): void
    {
        $picUser = User::factory()->create(['name' => 'Hendro Setiawan']);
        PicMachine::create([
            'user_id' => $picUser->id,
            'functional_location' => 'KA-WWD-NDB-5001',
        ]);

        $response = $this->actingAs($picUser)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Hendro Setiawan (Saya)');
    }

    public function test_analytics_table_displays_reliability_status_badges(): void
    {
        $user = User::factory()->create();

        // 1. Mesin kritis: downtime besar dalam periode 7 hari (7 * 24 = 168 jam)
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-KRITIS',
            'date' => Carbon::now()->subDays(2)->toDateString(),
            'object_part' => 'Motor',
            'notfis' => 1,
            'bm_hrs' => 50.0, // >15% downtime -> availability < 85%
        ]);

        $response = $this->actingAs($user)->get(route('analytics.index', [
            'period' => '7d',
            'machine' => 'KA-WWD-NDB-KRITIS',
        ]));

        $response->assertStatus(200);
        $response->assertSee('KA-WWD-NDB-KRITIS');
        $response->assertSee('Kritis (<85%)', false);
    }

    public function test_analytics_deduplicates_work_orders_with_multiple_confirmation_rows(): void
    {
        $user = User::factory()->create();

        // 1 WO Pertama (nomor maint_order 4404233160) dengan 1 header notfis=1 dan 2 baris konfirmasi teknisi
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-3001',
            'maint_order' => '4404233160',
            'date' => Carbon::now()->subDays(3)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Motor',
            'notfis' => 1,
            'bm_hrs' => 6.0,
            'response_hrs' => 1.0,
            'actual_hrs' => null,
            'maint_order_desc' => 'Motor terbakar',
        ]);
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-3001',
            'maint_order' => '4404233160',
            'date' => Carbon::now()->subDays(3)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Motor',
            'notfis' => null,
            'bm_hrs' => null,
            'actual_hrs' => 2.5,
            'confirmation_text' => 'Bongkar motor lama',
        ]);
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-3001',
            'maint_order' => '4404233160',
            'date' => Carbon::now()->subDays(3)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Motor',
            'notfis' => null,
            'bm_hrs' => null,
            'actual_hrs' => 3.5,
            'confirmation_text' => 'Pasang motor baru dan tes run',
        ]);

        // 1 WO Kedua (nomor maint_order 4404233161) dengan 1 header notfis=1 dan 1 baris konfirmasi
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-3001',
            'maint_order' => '4404233161',
            'date' => Carbon::now()->subDays(1)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Bearing',
            'notfis' => 1,
            'bm_hrs' => 4.0,
            'response_hrs' => 0.5,
            'actual_hrs' => null,
            'maint_order_desc' => 'Bearing macet',
        ]);
        SapHistory::create([
            'functional_location' => 'KA-WWD-NDB-3001',
            'maint_order' => '4404233161',
            'date' => Carbon::now()->subDays(1)->toDateString(),
            'month' => Carbon::now()->format('m.Y'),
            'object_part' => 'Bearing',
            'notfis' => null,
            'bm_hrs' => null,
            'actual_hrs' => 1.5,
            'confirmation_text' => 'Ganti bearing',
        ]);

        $response = $this->actingAs($user)->get(route('analytics.index', [
            'period' => '7d',
            'machine' => 'KA-WWD-NDB-3001',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('kpis', function (array $kpis) {
            // Total breakdown harus tepat 2 WO (bukan 5 baris catatan)
            // Total downtime harus tepat 10 jam (6 + 4)
            // Total jam kerja teknisi harus tepat 7.5 jam (2.5 + 3.5 + 1.5)
            return $kpis['total_breakdown'] === 2
                && $kpis['total_downtime'] == 10.0
                && $kpis['total_actual_hours'] == 7.5;
        });
    }

    public function test_sidebar_navigation_split_action_and_sub_menus(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee(route('analytics.index'));
        $response->assertSee('Buka / Tutup Sub-Menu Analytics');
        $response->assertSee(route('assistant.index'));
        $response->assertSee('AI Smart Assistant');
        $response->assertSee(route('pic.index'));
        $response->assertSee('Laporan PIC Mesin');
    }
}
