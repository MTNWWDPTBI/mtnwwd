<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Handover extends Model
{
    use HasFactory;

    protected $fillable = [
        'handover_date', 'shift', 'team', 'machine_name', 'problem_description', 
        'status', 'reported_by', 'resolved_by', 'resolved_at'
    ];
}