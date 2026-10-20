<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WwdProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'description',
        'pic_leader',
        'target_date',
        'column_headers',
        'status',
    ];

    protected $casts = [
        'column_headers' => 'array', // Otomatis mengubah JSON database menjadi Array PHP
    ];

    // Relasi ke detail progress mesin
    public function details()
    {
        return $this->hasMany(WwdProjectDetail::class);
    }
}