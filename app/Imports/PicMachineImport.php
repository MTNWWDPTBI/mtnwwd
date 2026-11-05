<?php

namespace App\Imports;

use App\Models\PicMachine;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PicMachineImport implements ToModel, WithHeadingRow
{
    /**
     * Map each row from the Excel file into a PicMachine model.
     *
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        // 1. Ekstrak email atau NIK teknisi
        $email = $row['email'] ?? $row['user_email'] ?? $row['email_teknisi'] ?? null;
        $nik = $row['nik'] ?? $row['nik_teknisi'] ?? null;

        $user = null;

        if (! empty($email)) {
            $user = User::where('email', trim((string) $email))->first();
        }

        if (! $user && ! empty($nik)) {
            $cleanNik = trim((string) $nik);
            if (Schema::hasColumn('users', 'nik')) {
                $user = User::where('nik', $cleanNik)->first();
            } else {
                // Fallback jika email berformat nik@domain atau diawali NIK
                $user = User::where('email', 'like', "{$cleanNik}%")->first();
            }
        }

        // 2. Ekstrak functional_location mesin
        $functionalLocation = $row['functional_location']
            ?? $row['functional_loc']
            ?? $row['machine']
            ?? $row['kode_mesin']
            ?? $row['mesin']
            ?? null;

        if (! $user || empty($functionalLocation)) {
            return null;
        }

        $cleanFunctionalLocation = strtoupper(trim((string) $functionalLocation));

        // 3. updateOrCreate pada tabel pic_machines agar tidak terjadi data ganda
        return PicMachine::updateOrCreate(
            [
                'user_id' => $user->id,
                'functional_location' => $cleanFunctionalLocation,
            ],
            [
                'functional_location' => $cleanFunctionalLocation,
            ]
        );
    }
}
