<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PicMachine extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'functional_location',
    ];

    /**
     * User / teknisi penanggung jawab (PIC) mesin ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
