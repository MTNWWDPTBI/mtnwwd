<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'month', 'week', 'date', 'cat', 'type', 'plant', 'area', 'dept', 
        'functional_location', 'indicator', 'maint_order', 'maint_order_desc', 
        'code_cause', 'error_code', 'creator', 'executor', 'code_group', 
        'object_part', 'problem', 'activity', 'status', 'confirmation_text', 
        'notfis', 'response_hrs', 'actual_hrs', 'bm_hrs'
    ];
}