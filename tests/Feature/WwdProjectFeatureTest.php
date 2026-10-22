<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachineUnit;
use App\Models\User;
use App\Models\WwdProject;
use App\Models\WwdProjectDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WwdProjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_project_detail_with_frozen_header_and_bulk_form(): void
    {
        $user = User::factory()->create();
        $machine = Machine::create([
            'machine_code' => 'MC001',
            'machine_name' => 'WWD NDB Drawbench',
        ]);
        $unit = MachineUnit::create([
            'machine_id' => $machine->id,
            'machine_number' => 'KA-WWD-NDB-3006',
            'status' => 'ACTIVE',
        ]);

        $project = WwdProject::create([
            'project_name' => 'Overhaul Capstan 2026',
            'pic_leader' => 'Budi Santoso',
            'target_date' => '2026-03-31',
            'column_headers' => ['Kondisi Bearing', 'Ketebalan O-Ring', 'Tegangan Sabuk'],
            'status' => 'OPEN',
        ]);

        WwdProjectDetail::create([
            'wwd_project_id' => $project->id,
            'machine_unit_id' => $unit->id,
            'dynamic_data' => [
                'Kondisi Bearing' => 'OK',
                'Ketebalan O-Ring' => null,
                'Tegangan Sabuk' => null,
            ],
            'status' => 'OPEN',
        ]);

        $response = $this->actingAs($user)->get(route('wwd-projects.show', $project->id));

        $response->assertOk();
        $response->assertSee('Overhaul Capstan 2026');
        $response->assertSee('KA-WWD-NDB-3006');
        $response->assertSee('Kondisi Bearing');
        $response->assertSee('Simpan Semua Data');
        $response->assertSee('bulk-update-form');
    }

    public function test_user_can_bulk_update_multiple_machine_details_at_once(): void
    {
        $user = User::factory()->create();
        $machine = Machine::create([
            'machine_code' => 'MC001',
            'machine_name' => 'WWD NDB Drawbench',
        ]);
        $unit1 = MachineUnit::create([
            'machine_id' => $machine->id,
            'machine_number' => 'KA-WWD-NDB-3001',
            'status' => 'ACTIVE',
        ]);
        $unit2 = MachineUnit::create([
            'machine_id' => $machine->id,
            'machine_number' => 'KA-WWD-NDB-3002',
            'status' => 'ACTIVE',
        ]);

        $project = WwdProject::create([
            'project_name' => 'Checklist Pelumasan dan Alignment',
            'pic_leader' => 'Ahmad',
            'target_date' => '2026-04-15',
            'column_headers' => ['Parameter A', 'Parameter B'],
            'status' => 'OPEN',
        ]);

        $detail1 = WwdProjectDetail::create([
            'wwd_project_id' => $project->id,
            'machine_unit_id' => $unit1->id,
            'dynamic_data' => ['Parameter A' => null, 'Parameter B' => null],
            'status' => 'OPEN',
        ]);

        $detail2 = WwdProjectDetail::create([
            'wwd_project_id' => $project->id,
            'machine_unit_id' => $unit2->id,
            'dynamic_data' => ['Parameter A' => null, 'Parameter B' => null],
            'status' => 'OPEN',
        ]);

        // Submit form bulk update untuk 2 mesin sekaligus
        $response = $this->actingAs($user)->put(route('wwd-projects.bulk-update', $project->id), [
            'details' => [
                $detail1->id => [
                    'dynamic_data' => [
                        'Parameter A' => 'Nilai 100',
                        'Parameter B' => 'Sesuai Standar',
                    ],
                    'status' => 'CLOSE',
                ],
                $detail2->id => [
                    'dynamic_data' => [
                        'Parameter A' => 'Nilai 95',
                        'Parameter B' => 'Perlu Monitoring',
                    ],
                    'status' => 'OPEN',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan kedua data mesin langsung terupdate di database
        $detail1->refresh();
        $detail2->refresh();

        $this->assertEquals('Nilai 100', $detail1->dynamic_data['Parameter A']);
        $this->assertEquals('Sesuai Standar', $detail1->dynamic_data['Parameter B']);
        $this->assertEquals('CLOSE', $detail1->status);

        $this->assertEquals('Nilai 95', $detail2->dynamic_data['Parameter A']);
        $this->assertEquals('Perlu Monitoring', $detail2->dynamic_data['Parameter B']);
        $this->assertEquals('OPEN', $detail2->status);
    }

    public function test_user_can_still_update_a_single_detail_row(): void
    {
        $user = User::factory()->create();
        $machine = Machine::create([
            'machine_code' => 'MC001',
            'machine_name' => 'WWD NDB Drawbench',
        ]);
        $unit = MachineUnit::create([
            'machine_id' => $machine->id,
            'machine_number' => 'KA-WWD-NDB-3005',
            'status' => 'ACTIVE',
        ]);

        $project = WwdProject::create([
            'project_name' => 'Single Row Update Test',
            'pic_leader' => 'Doni',
            'target_date' => '2026-05-01',
            'column_headers' => ['Cek Suhu Motor'],
            'status' => 'OPEN',
        ]);

        $detail = WwdProjectDetail::create([
            'wwd_project_id' => $project->id,
            'machine_unit_id' => $unit->id,
            'dynamic_data' => ['Cek Suhu Motor' => 'Belum Diukur'],
            'status' => 'OPEN',
        ]);

        $response = $this->actingAs($user)->put(route('wwd-projects.details.update', $detail->id), [
            'details' => [
                $detail->id => [
                    'dynamic_data' => ['Cek Suhu Motor' => '45 Celcius'],
                    'status' => 'CLOSE',
                ],
            ],
        ]);

        $response->assertRedirect();
        $detail->refresh();

        $this->assertEquals('45 Celcius', $detail->dynamic_data['Cek Suhu Motor']);
        $this->assertEquals('CLOSE', $detail->status);
    }
}
