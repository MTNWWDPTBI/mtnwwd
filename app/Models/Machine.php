<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Fungsi relasi ini harus berada DI DALAM class Machine, sebelum kurung kurawal terakhir
    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }
}