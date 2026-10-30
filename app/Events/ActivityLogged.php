<?php

namespace App\Events;

use App\Models\ActivityLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ActivityLogged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public array $payload;

    public string $connection = 'database';

    public string $queue = 'broadcasts';

    public function __construct(ActivityLog $activity)
    {
        $this->payload = [
            'id' => $activity->id,
            'user_id' => $activity->user_id,
            'user_name' => $activity->user?->name ?? '-',
            'module_id' => $activity->module_id,
            'route_name' => $activity->route_name ?? '-',
            'url' => $activity->url ?? '-',
            'method' => $activity->method ?? '-',
            'ip_address' => $activity->ip_address ?? '-',
            'user_agent' => $activity->user_agent ?? '-',
            'created_at' => $activity->created_at?->toISOString(),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('it-dashboard'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'activity.logged';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
