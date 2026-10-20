<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpmTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['tpm_part_id', 'transaction_type', 'qty', 'operator_name'];

    // Relasi balik ke TpmPart
    public function tpmPart()
    {
        return $this->belongsTo(TpmPart::class, 'tpm_part_id');
    }
}
