<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    use HasFactory;

    protected $fillable = ['part_id', 'sap_code', 'part_name', 'bin_location'];
    
    // Relasi ke Assembly melalui tabel pivot assembly_spareparts (BOM)
    public function assemblies()
    {
        return $this->belongsToMany(Assembly::class, 'assembly_spareparts', 'sparepart_id', 'assembly_id')
                    ->withPivot('qty', 'unit');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
}