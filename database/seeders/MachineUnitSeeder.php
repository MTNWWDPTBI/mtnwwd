<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachineUnit;
use Illuminate\Database\Seeder;

class MachineUnitSeeder extends Seeder
{
    public function run()
    {
        $file = storage_path('app/mesin.csv');

        if (! file_exists($file)) {
            $this->command->error('GAGAL: File mesin.csv tidak ditemukan di folder storage/app/');

            return;
        }

        // Membaca file CSV
        // Catatan: Jika CSV-mu menggunakan pemisah titik koma (;), ganti parameter di bawah menjadi: str_getcsv($line, ';')
        $data = array_map('str_getcsv', file($file));
        array_shift($data); // Hapus baris pertama (Header: Nomer mesin, tipe mesin)

        // Kamus Penerjemah (Mapping) dari nama Excel ke nama di tabel 'machines'
        $typeMapping = [
            'SHX' => 'SHX',
            'NDE-SC2007' => 'NDE',
            'NDE-SW' => 'NDE',
            'NDE RF' => 'NDE',
            'NDB TRITON' => 'NDB TRITON',
            'NDB ONO' => 'NDB ONO',
            'NDB ONTRI' => 'NDB TRITON',
            'NDE-SC2003' => 'NDE',
            'NDB ONB' => 'NDB ONB',
            'NDB MOT' => 'NDE-MOT',
        ];

        $insertedCount = 0;

        foreach ($data as $row) {
            // Skip baris kosong atau tidak valid
            if (count($row) < 2 || empty(trim($row[0]))) {
                continue;
            }

            $machineNumber = trim($row[0]);
            $excelType = trim($row[1]);

            // Terjemahkan nama tipe mesin
            $dbMachineName = $typeMapping[$excelType] ?? $excelType;

            // Cari mesin master berdasarkan nama yang sudah diterjemahkan
            $machine = Machine::where('machine_name', $dbMachineName)->first();

            if ($machine) {
                // Gunakan updateOrCreate agar tidak ada data ganda jika Seeder dijalankan 2x
                MachineUnit::updateOrCreate(
                    ['machine_number' => $machineNumber],
                    [
                        'machine_id' => $machine->id,
                        'status' => 'ACTIVE',
                    ]
                );
                $insertedCount++;
            } else {
                $this->command->warn("Peringatan: Tipe mesin '{$dbMachineName}' tidak ada di tabel machines. Unit {$machineNumber} dilewati.");
            }
        }

        $this->command->info("SELESAI! Berhasil memproses {$insertedCount} unit mesin fisik ke database.");
    }
}
