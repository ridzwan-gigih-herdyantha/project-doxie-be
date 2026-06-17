<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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

    /**
     * @var list<string>
     */
    protected $appends = ['file_url'];

    /**
     * Temporary signed URL to the document on R2, valid for one hour.
     */
    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->file_path
                ? Storage::disk('r2')->temporaryUrl($this->file_path, now()->addHour())
                : null,
        );
    }

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
