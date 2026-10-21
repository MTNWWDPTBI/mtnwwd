<?php

namespace Tests\Feature;

use App\Models\TpmPart;
use App\Models\TpmTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TpmFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_tpm_index_with_location_filter_and_counts(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        TpmPart::create([
            'tpm_part_number' => '9991',
            'name' => 'BEARING 6204',
            'location' => 'RAK A',
            'stock' => 10,
            'min_stock' => 2,
            'max_stock' => 20,
        ]);

        TpmPart::create([
            'tpm_part_number' => '9992',
            'name' => 'BELT LEVELWIND',
            'location' => 'RAK B',
            'stock' => 5,
            'min_stock' => 1,
            'max_stock' => 10,
        ]);

        // Akses halaman tanpa filter lokasi (semua lokasi)
        $response = $this->actingAs($user)->get(route('tpm.index'));

        $response->assertOk();
        $response->assertSee('BEARING 6204');
        $response->assertSee('BELT LEVELWIND');
        $response->assertSee('Lokasi RAK A');
        $response->assertSee('Lokasi RAK B');

        // Akses halaman dengan filter lokasi 'RAK A'
        $responseFiltered = $this->actingAs($user)->get(route('tpm.index', ['location' => 'RAK A']));

        $responseFiltered->assertOk();
        $responseFiltered->assertSee('BEARING 6204');
        $responseFiltered->assertDontSee('BELT LEVELWIND');
        $responseFiltered->assertSee('Lokasi: RAK A');
    }

    public function test_it_exports_tpm_stock_to_excel_successfully_without_class_not_found_error(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        TpmPart::create([
            'tpm_part_number' => '9991',
            'name' => 'SARUNG TANGAN',
            'location' => 'RAK A',
            'stock' => 15,
            'min_stock' => 5,
            'max_stock' => 30,
        ]);

        $response = $this->actingAs($admin)->get(route('export.tpm'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.ms-excel');
        $response->assertSee('Laporan Stok Barang TPM');
        $response->assertSee('9991');
        $response->assertSee('SARUNG TANGAN');
        $response->assertSee('RAK A');
    }

    public function test_it_exports_tpm_stock_filtered_by_location(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        TpmPart::create([
            'tpm_part_number' => '9991',
            'name' => 'BARANG A',
            'location' => 'LOKASI A',
            'stock' => 10,
            'min_stock' => 2,
            'max_stock' => 20,
        ]);

        TpmPart::create([
            'tpm_part_number' => '9992',
            'name' => 'BARANG B',
            'location' => 'LOKASI B',
            'stock' => 5,
            'min_stock' => 1,
            'max_stock' => 10,
        ]);

        $response = $this->actingAs($admin)->get(route('export.tpm', ['location' => 'LOKASI A']));

        $response->assertOk();
        $response->assertSee('Laporan Stok Barang TPM - Lokasi: LOKASI A');
        $response->assertSee('BARANG A');
        $response->assertDontSee('BARANG B');
    }

    public function test_it_renders_scan_page_with_locations_and_parts(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
        ]);

        TpmPart::create([
            'tpm_part_number' => '9991',
            'name' => 'WD-40 400ML',
            'location' => 'RAK C',
            'stock' => 8,
            'min_stock' => 2,
            'max_stock' => 12,
        ]);

        $response = $this->actingAs($user)->get(route('tpm.scan', ['part_number' => '9991']));

        $response->assertOk();
        $response->assertSee('Eksekusi Stok TPM');
        $response->assertSee('WD-40 400ML');
        $response->assertSee('RAK C');
    }

    public function test_it_processes_stock_in_via_manual_part_selection(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $part = TpmPart::create([
            'tpm_part_number' => '9991',
            'name' => 'MAJUN PUTIH',
            'location' => 'RAK D',
            'stock' => 5,
            'min_stock' => 2,
            'max_stock' => 20,
        ]);

        $response = $this->actingAs($user)->post(route('tpm.processScan'), [
            'part_id' => $part->id,
            'transaction_type' => 'IN',
            'qty' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(15, $part->fresh()->stock);

        $this->assertDatabaseHas('tpm_transactions', [
            'tpm_part_id' => $part->id,
            'transaction_type' => 'IN',
            'qty' => 10,
        ]);
    }

    public function test_it_processes_stock_out_via_barcode_scan(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $part = TpmPart::create([
            'tpm_part_number' => '9992',
            'name' => 'SARUNG TANGAN KAIN',
            'location' => 'RAK D',
            'stock' => 12,
            'min_stock' => 3,
            'max_stock' => 25,
        ]);

        $response = $this->actingAs($user)->post(route('tpm.processScan'), [
            'tpm_part_number' => '9992',
            'transaction_type' => 'OUT',
            'qty' => 4,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(8, $part->fresh()->stock);

        $this->assertDatabaseHas('tpm_transactions', [
            'tpm_part_id' => $part->id,
            'transaction_type' => 'OUT',
            'qty' => 4,
        ]);
    }

    public function test_it_prevents_stock_out_when_qty_exceeds_available_stock(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $part = TpmPart::create([
            'tpm_part_number' => '9993',
            'name' => 'KUNCI PAS 10',
            'location' => 'RAK E',
            'stock' => 2,
            'min_stock' => 1,
            'max_stock' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('tpm.processScan'), [
            'part_id' => $part->id,
            'transaction_type' => 'OUT',
            'qty' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertEquals(2, $part->fresh()->stock);
        $this->assertEquals(0, TpmTransaction::count());
    }
}
