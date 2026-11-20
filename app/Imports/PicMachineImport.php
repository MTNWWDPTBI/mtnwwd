<?php

namespace App\Imports;

use App\Models\PicMachine;
use App\Models\SapHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PicMachineImport implements ToModel, WithHeadingRow
{
    public int $importedCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    public array $skippedDetails = [];

    /**
     * Cache user list in memory to prevent repeated queries during Excel import
     *
     * @var Collection|null
     */
    protected $allUsers = null;

    /**
     * Map each row from the Excel file into a PicMachine model.
     *
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        if ($this->allUsers === null) {
            $this->allUsers = User::all();
        }

        // 1. Ekstrak data user (teknisi) dari berbagai kemungkinan header
        $user = $this->resolveUser($row);

        // 2. Ekstrak data mesin / functional_location dari berbagai kemungkinan header
        $functionalLocation = $this->resolveFunctionalLocation($row);

        if (! $user) {
            $this->skippedCount++;
            $rawUser = $this->extractRawUserString($row);
            $this->skippedDetails[] = "Teknisi '{$rawUser}' tidak ditemukan di database pengguna.";

            return null;
        }

        if (empty($functionalLocation)) {
            $this->skippedCount++;
            $this->skippedDetails[] = "Baris untuk teknisi '{$user->name}' tidak memiliki kode mesin/functional location.";

            return null;
        }

        $cleanFunctionalLocation = strtoupper(trim((string) $functionalLocation));

        // Normalisasi kode mesin jika user hanya memasukkan angka/kode pendek (misal: 3006 -> KA-WWD-NDB-3006)
        if (! str_starts_with($cleanFunctionalLocation, 'KA-')) {
            $matchedFloc = SapHistory::where('functional_location', 'like', "%{$cleanFunctionalLocation}%")
                ->value('functional_location');
            if ($matchedFloc) {
                $cleanFunctionalLocation = $matchedFloc;
            }
        }

        // 3. updateOrCreate pada tabel pic_machines agar tidak terjadi data ganda
        $picMachine = PicMachine::updateOrCreate(
            [
                'user_id' => $user->id,
                'functional_location' => $cleanFunctionalLocation,
            ],
            [
                'functional_location' => $cleanFunctionalLocation,
            ]
        );

        if ($picMachine->wasRecentlyCreated) {
            $this->importedCount++;
        } else {
            $this->updatedCount++;
            $this->importedCount++;
        }

        return $picMachine;
    }

    /**
     * Temukan user berdasarkan Email, NIK, atau Nama dengan pencarian fleksibel
     */
    protected function resolveUser(array $row): ?User
    {
        // 1. Cek Email
        $email = $row['email']
            ?? $row['user_email']
            ?? $row['email_teknisi']
            ?? $row['email_pic']
            ?? null;

        if (! empty($email)) {
            $cleanEmail = trim((string) $email);
            $user = $this->allUsers->firstWhere('email', $cleanEmail);
            if (! $user) {
                $user = $this->allUsers->first(fn ($u) => stripos($u->email, $cleanEmail) !== false);
            }
            if ($user) {
                return $user;
            }
        }

        // 2. Cek NIK / No. Badge
        $nik = $row['nik']
            ?? $row['nik_teknisi']
            ?? $row['no_badge']
            ?? $row['badge']
            ?? $row['nip']
            ?? null;

        if (! empty($nik)) {
            $cleanNik = trim((string) $nik);
            if (Schema::hasColumn('users', 'nik')) {
                $user = $this->allUsers->firstWhere('nik', $cleanNik);
                if ($user) {
                    return $user;
                }
            }
            // Fallback cari di email jika email diawali NIK
            $user = $this->allUsers->first(fn ($u) => stripos($u->email, $cleanNik) === 0);
            if ($user) {
                return $user;
            }
        }

        // 3. Cek Nama Teknisi
        $name = $row['nama']
            ?? $row['name']
            ?? $row['nama_teknisi']
            ?? $row['nama_pic']
            ?? $row['pic']
            ?? $row['teknisi']
            ?? $row['user']
            ?? $row['petugas']
            ?? $row['personel']
            ?? $row['executor']
            ?? null;

        if (! empty($name)) {
            $cleanName = trim((string) $name);
            $lowerName = strtolower($cleanName);

            // a. Exact match
            $user = $this->allUsers->first(fn ($u) => strtolower(trim($u->name)) === $lowerName);
            if ($user) {
                return $user;
            }

            // b. Substring match (nama di Excel ada di dalam nama DB atau sebaliknya)
            $user = $this->allUsers->first(fn ($u) => stripos($u->name, $cleanName) !== false || stripos($cleanName, $u->name) !== false);
            if ($user) {
                return $user;
            }

            // c. Kata kunci nama (misal Excel "Moh Syaoqi", di DB "Moh Syaoqi - Tim A")
            $words = array_filter(explode(' ', $cleanName), fn ($w) => strlen($w) >= 3);
            if (! empty($words)) {
                $user = $this->allUsers->first(function ($u) use ($words) {
                    foreach ($words as $word) {
                        if (stripos($u->name, $word) !== false) {
                            return true;
                        }
                    }

                    return false;
                });
                if ($user) {
                    return $user;
                }
            }
        }

        return null;
    }

    /**
     * Resolusi kode functional_location dari berbagai kemungkinan header
     */
    protected function resolveFunctionalLocation(array $row): ?string
    {
        $raw = $row['functional_location']
            ?? $row['functional_loc']
            ?? $row['kode_mesin']
            ?? $row['mesin']
            ?? $row['machine']
            ?? $row['floc']
            ?? $row['equipment']
            ?? $row['nama_mesin']
            ?? $row['unit']
            ?? $row['machine_code']
            ?? $row['machine_id']
            ?? $row['no_mesin']
            ?? $row['lokasi']
            ?? null;

        return ! empty($raw) ? trim((string) $raw) : null;
    }

    /**
     * Ekstrak teks mentah identitas user untuk pesan log/error
     */
    protected function extractRawUserString(array $row): string
    {
        return (string) ($row['nama']
            ?? $row['nama_teknisi']
            ?? $row['pic']
            ?? $row['email']
            ?? $row['nik']
            ?? $row['user']
            ?? 'Baris Excel');
    }
}
