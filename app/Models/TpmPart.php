<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpmPart extends Model
{
    use HasFactory;

    protected $fillable = ['tpm_part_number', 'name', 'location', 'stock', 'min_stock', 'max_stock'];

    // Relasi ke riwayat transaksinya
    public function transactions()
    {
        return $this->hasMany(TpmTransaction::class, 'tpm_part_id');
    }
}