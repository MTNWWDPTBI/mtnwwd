<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['image_url'];

    // Fungsi relasi ini harus berada DI DALAM class Machine, sebelum kurung kurawal terakhir
    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }

    public function getImageUrlAttribute()
    {
        if (!empty($this->image)) {
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }
            if (file_exists(public_path($this->image))) {
                return asset($this->image);
            }
        }

        // Default visual path based on machine code
        $code = strtolower(trim($this->machine_code));
        if (file_exists(public_path("images/machines/{$code}.svg"))) {
            return asset("images/machines/{$code}.svg");
        }

        return asset("images/machines/default.svg");
    }
}