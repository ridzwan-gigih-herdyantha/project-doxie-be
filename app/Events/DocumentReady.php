<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        private readonly Document $document
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("user.{$this->document->user->uuid}");
    }

    public function broadcastAs(): string
    {
        return 'document.ready';
    }

    public function broadcastWith(): array
    {
        return [
            'uuid'   => $this->document->uuid,
            'title'  => $this->document->title,
            'status' => $this->document->status,
        ];
    }
}