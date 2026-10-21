<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'user_name', 'user_message', 'ai_response'])]
class ChatHistory extends Model
{
    use HasFactory;

    /**
     * Relasi ke User pemilik riwayat chat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
