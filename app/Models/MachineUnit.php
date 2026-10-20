<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'machine_number',
        'location',
        'status',
    ];

    // Relasi balik ke master kategori mesin (Machine)
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
