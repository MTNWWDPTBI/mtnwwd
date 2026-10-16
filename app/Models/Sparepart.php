<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model {
    protected $guarded = [];
    public function assemblies()
    {
        return $this->belongsToMany(Assembly::class, 'assembly_spareparts');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
}
