<?php

namespace Tests\Feature;

use App\Models\PicMachine;
use App\Models\SapHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class PicReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_pic_report(): void
    {
        $response = $this->get(route('pic.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_pic_report_index(): void
    {
        $technician = User::factory()->create([
            'name' => 'Budi Teknisi',
            'email' => 'budi@bekaert.com',
            'role' => 'teknisi',
        ]);

        PicMachine::create([
            'user_id' => $technician->id,
            'functional_location' => 'KA-WWD-NDB-3006',
        ]);
        PicMachine::create([
            'user_id' => $technician->id,
            'functional_location' => 'KA-WWD-NDB-3007',
        ]);

        $response = $this->actingAs($technician)->get(route('pic.index'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Laporan PIC: Budi Teknisi');
        $response->assertSee('KA-WWD-NDB-3006');
        $response->assertSee('KA-WWD-NDB-3007');
        // Teknisi biasa tidak boleh melihat tombol import Excel
        $response->assertDontSee('Import Data PIC (Excel)');

        // Admin dapat melihat tombol import Excel
        $admin = User::factory()->create(['role' => 'admin']);
        $adminResponse = $this->actingAs($admin)->get(route('pic.index'));
        $adminResponse->assertSee('Import Data PIC (Excel)');
    }

    public function test_generate_report_returns_top_5_parts_with_frequency_and_downtime(): void
    {
        $user = User::factory()->create();
        PicMachine::create([
            'user_id' => $user->id,
            'functional_location' => 'KA-WWD-NDB-3006',
        ]);

        // Kerusakan pada bulan 2026-03
        SapHistory::create([
            'date' => '2026-03-05',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-NDB-3006',
            'object_part' => 'BEARING',
            'problem' => 'Overheat',
            'bm_hrs' => '2.50',
            'executor' => 'Budi',
        ]);
        SapHistory::create([
            'date' => '2026-03-10',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-NDB-3006',
            'object_part' => 'BEARING',
            'problem' => 'Noise',
            'bm_hrs' => '1.50',
            'executor' => 'Budi',
        ]);
        SapHistory::create([
            'date' => '2026-03-12',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-NDB-3006',
            'object_part' => 'MOTOR',
            'problem' => 'Trip',
            'bm_hrs' => '4.00',
            'executor' => 'Budi',
        ]);

        // Kerusakan di mesin lain yang bukan PIC user
        SapHistory::create([
            'date' => '2026-03-15',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-OTHER-9999',
            'object_part' => 'GEARBOX',
            'problem' => 'Leak',
            'bm_hrs' => '10.00',
            'executor' => 'Other',
        ]);

        $response = $this->actingAs($user)->postJson(route('pic.generate'), [
            'month' => '2026-03',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'labels' => ['BEARING', 'MOTOR'],
            'frequencies' => [2, 1],
            'downtimes' => [4.0, 4.0],
            'total_events' => 3,
            'total_downtime' => 8.0,
        ]);
        $response->assertJsonMissing(['GEARBOX']);
    }

    public function test_generate_report_supports_multiple_selected_machines(): void
    {
        $user = User::factory()->create();
        PicMachine::create(['user_id' => $user->id, 'functional_location' => 'KA-WWD-NDB-4001']);
        PicMachine::create(['user_id' => $user->id, 'functional_location' => 'KA-WWD-NDB-4002']);
        PicMachine::create(['user_id' => $user->id, 'functional_location' => 'KA-WWD-NDB-4003']);

        SapHistory::create([
            'date' => '2026-03-01',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-NDB-4001',
            'object_part' => 'PULLEY',
            'bm_hrs' => '1.00',
        ]);
        SapHistory::create([
            'date' => '2026-03-02',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-NDB-4002',
            'object_part' => 'BELT',
            'bm_hrs' => '2.00',
        ]);
        SapHistory::create([
            'date' => '2026-03-03',
            'month' => '03.2026',
            'functional_location' => 'KA-WWD-NDB-4003',
            'object_part' => 'SENSOR',
            'bm_hrs' => '3.00',
        ]);

        // Filter hanya mesin 4001 dan 4002 (mesin 4003 tidak dipilih)
        $response = $this->actingAs($user)->postJson(route('pic.generate'), [
            'month' => '2026-03',
            'machines' => ['KA-WWD-NDB-4001', 'KA-WWD-NDB-4002'],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'machine_count' => 2,
            'total_events' => 2,
        ]);
        $this->assertContains('PULLEY', $response->json('labels'));
        $this->assertContains('BELT', $response->json('labels'));
        $this->assertNotContains('SENSOR', $response->json('labels'));
    }

    public function test_generate_ai_summary_returns_json_summary(): void
    {
        $user = User::factory()->create();
        PicMachine::create([
            'user_id' => $user->id,
            'functional_location' => 'KA-WWD-NDB-3006',
        ]);

        // Buat 15 record kerusakan dalam bulan 2026-03 untuk membuktikan tidak dibatasi 10 data
        for ($i = 1; $i <= 15; $i++) {
            SapHistory::create([
                'date' => sprintf('2026-03-%02d', min($i, 28)),
                'month' => '03.2026',
                'functional_location' => 'KA-WWD-NDB-3006',
                'object_part' => $i % 2 === 0 ? 'BEARING' : 'MOTOR',
                'problem' => 'Problem nomor '.$i,
                'bm_hrs' => '1.00',
                'executor' => 'Budi',
            ]);
        }

        Http::fake([
            '*relink-gateway.biz.id*' => function ($request) {
                // Pastikan prompt memuat total 15 order (seluruh bulan) dan kode mesin spesifik
                $body = json_decode($request->body(), true);
                $promptText = $body['messages'][0]['content'] ?? '';
                if (! str_contains($promptText, '15 kejadian')) {
                    return Http::response(['error' => 'Data tidak memuat seluruh 15 order'], 500);
                }
                if (! str_contains($promptText, 'KA-WWD-NDB-3006')) {
                    return Http::response(['error' => 'Data tidak memuat kode mesin spesifik'], 500);
                }

                return Http::response([
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "1. **Fokus Masalah Terbanyak**:\n- Mesin KA-WWD-NDB-3006 menyumbang 15 kerusakan.\n\n2. **Root Cause**:\n- Analisis akar masalah mesin KA-WWD-NDB-3006.\n\n3. **Action Plan & TPM Preventif**:\n- Jadwal grease harian TPM khusus mesin KA-WWD-NDB-3006.",
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($user)->postJson(route('pic.ai-summary'), [
            'month' => '2026-03',
            'machine' => 'KA-WWD-NDB-3006',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'summary']);
        $this->assertStringContainsString('Fokus Masalah Terbanyak', $response->json('summary'));
    }

    public function test_regular_user_is_forbidden_from_importing_pic(): void
    {
        $technician = User::factory()->create(['role' => 'teknisi']);
        $file = UploadedFile::fake()->create('mapping_pic.xlsx', 100);

        $response = $this->actingAs($technician)->post(route('pic.import'), [
            'file' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_import_pic_requires_valid_excel_or_csv_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('pic.import'), [
            'file' => UploadedFile::fake()->create('document.pdf', 100),
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_pic_successfully_dispatches_excel_import(): void
    {
        Excel::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->create('mapping_pic.xlsx', 100);

        $response = $this->actingAs($admin)->post(route('pic.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Excel::assertImported('mapping_pic.xlsx');
    }
}
