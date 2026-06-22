<?php

namespace App\Models;

use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, HasPublicUuid;

    protected $fillable = ['chat_session_id', 'role', 'content', 'model_used'];

    /**
     * @var list<string>
     */
    protected $hidden = ['id', 'chat_session_id'];

    /**
     * Keep the parent chat session's `updated_at` in sync so it reflects the
     * last activity timestamp used for cursor-based ordering.
     *
     * @var list<string>
     */
    protected $touches = ['conversation'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}
