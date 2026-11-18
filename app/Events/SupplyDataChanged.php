<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Un master creo, edito o borro algo del modulo de proveedores. Las pantallas
 * abiertas de los demas master recargan su consulta al recibirlo. Es una
 * mejora, no una garantia: lo que evita pisarse es el 409 por updated_at.
 */
class SupplyDataChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $entity,      // supplier | branch | contact | offer | item
        public string $action,      // created | updated | deleted
        public ?int $supplierId,
        public ?int $userId,
        public ?string $userName,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('masters')];
    }

    public function broadcastAs(): string
    {
        return 'supply.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'entity' => $this->entity,
            'action' => $this->action,
            'supplier_id' => $this->supplierId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
        ];
    }
}
