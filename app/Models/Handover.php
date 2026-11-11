<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Handover extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'handover_date',
        'shift',
        'team',
        'machine_name',
        'problem_description',
        'lototo_status',
        'status',
        'reported_by',
        'resolved_by',
        'resolved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'handover_date' => 'date',
            'resolved_at' => 'datetime',
        ];
    }
}
