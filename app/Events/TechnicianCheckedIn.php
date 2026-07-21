<?php

namespace App\Events;

use App\Models\TechnicianCheckin;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TechnicianCheckedIn implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TechnicianCheckin $checkin,
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
        $checkin = $this->checkin->load([
            'equipment:id,internal_code,equipment_type,brand,model',
            'equipment.site:id,name,client_id',
            'equipment.site.client:id,business_name',
            'technician:id,name,phone',
        ]);

        return [
            'type' => 'technician_checked_in',
            'checkin_id' => $checkin->id,
            'technician' => $checkin->technician->name,
            'equipment_code' => $checkin->equipment->internal_code,
            'equipment_type' => $checkin->equipment->equipment_type,
            'client' => $checkin->equipment->site?->client?->business_name,
            'site' => $checkin->equipment->site?->name,
            'checked_in_at' => $checkin->checked_in_at->toIso8601String(),
            'is_emergency' => $checkin->is_emergency,
            'response_time_minutes' => $checkin->response_time_minutes,
        ];
    }

    public function broadcastAs(): string
    {
        return 'technician.checked-in';
    }
}
