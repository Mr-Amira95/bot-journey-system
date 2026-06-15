<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Call   $call,
        public string $event,
        public array  $payload = []
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('call.' . $this->call->id)];
    }

    public function broadcastAs(): string
    {
        return 'call.status';
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'call_id' => $this->call->id,
            'status'  => $this->call->status->value,
            'event'   => $this->event,
        ], $this->payload);
    }
}
