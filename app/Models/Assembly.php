<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assembly extends Model {
    protected $guarded = [];
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function spareparts()
    {
        // Relasi many-to-many melalui tabel pivot BOM
        return $this->belongsToMany(Sparepart::class, 'assembly_spareparts')
                    ->withPivot('qty', 'unit', 'description');
    }
}

