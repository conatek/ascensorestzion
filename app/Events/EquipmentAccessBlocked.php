<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/** Tiempo real para la campana del master: un tecnico espera el desbloqueo. */
class EquipmentAccessBlocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public array $payload,
        public array $masterUserIds,
    ) {}

    public function broadcastOn(): array
    {
        return collect($this->masterUserIds)
            ->map(fn ($id) => new PrivateChannel("App.Models.User.{$id}"))
            ->all();
    }

    public function broadcastWith(): array
    {
        return ['type' => 'equipment_access_blocked'] + $this->payload;
    }

    public function broadcastAs(): string
    {
        return 'equipment.access-blocked';
    }
}
