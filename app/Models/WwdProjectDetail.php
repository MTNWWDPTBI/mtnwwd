<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WwdProjectDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'wwd_project_id',
        'machine_unit_id',
        'dynamic_data',
        'status',
    ];

    protected $casts = [
        'dynamic_data' => 'array', // Otomatis mengubah JSON database menjadi Array PHP
    ];

    // Relasi ke Project Master
    public function project()
    {
        return $this->belongsTo(WwdProject::class, 'wwd_project_id');
    }

    // Relasi ke Unit Mesin Fisik
    public function machineUnit()
    {
        return $this->belongsTo(MachineUnit::class);
    }
}
