<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftHandover extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'shift_from',
        'shift_to',
        'handover_date',
        'unresolved_machines',
        'lototo_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'handover_date' => 'date',
            'unresolved_machines' => 'array',
            'lototo_active' => 'array',
        ];
    }

    /**
     * Teknisi yang membuat handover shift ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
