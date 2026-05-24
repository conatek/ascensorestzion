<?php

namespace App\Events;

use App\Models\ServiceReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ServiceReport $report,
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
        $report = $this->report->load([
            'equipment:id,internal_code,equipment_type',
            'client:id,business_name',
            'site:id,name',
            'technician:id,name',
        ]);

        return [
            'type' => 'report_completed',
            'report_id' => $report->id,
            'report_number' => $report->report_number,
            'report_type' => $report->report_type,
            'technician' => $report->technician?->name,
            'equipment_code' => $report->equipment?->internal_code,
            'client' => $report->client?->business_name,
            'site' => $report->site?->name,
        ];
    }

    public function broadcastAs(): string
    {
        return 'report.completed';
    }
}
