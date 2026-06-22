<?php

namespace App\Models;

use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory, HasPublicUuid;

    protected $fillable = ['user_id', 'document_id', 'title'];

    /**
     * @var list<string>
     */
    protected $hidden = ['id', 'user_id', 'document_id', 'document'];

    /**
     * @var list<string>
     */
    protected $appends = ['document_uuid'];

    /**
     * Expose the parent document by its public UUID instead of the internal id.
     */
    protected function documentUuid(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->document?->uuid,
        );
    }

    public function scopeOrderByLastActivity(Builder $query): Builder
    {
        return $query
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('messages.chat_session_id', 'chat_sessions.id')
                    ->latest()
                    ->take(1)
            )
            ->latest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
