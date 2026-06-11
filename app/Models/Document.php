<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'file_name',
        'file_path',
        'file_size',
        'status',
        'page_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function chatSession(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }
}
