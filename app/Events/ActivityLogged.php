<?php

namespace App\Events;

use App\Models\ActivityLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
class ActivityLogged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $activity;

    public function __construct(ActivityLog $activity)
    {
        $this->activity = $activity->load('user');
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
        return [
            'id' => $this->activity->id,

            'user_id' => $this->activity->user_id,

            'user_name' => $this->activity->user->name ?? '-',

            'module_id' => $this->activity->module_id,

            'route_name' => $this->activity->route_name ?? '-',

            'url' => $this->activity->url ?? '-',

            'method' => $this->activity->method ?? '-',

            'ip_address' => $this->activity->ip_address ?? '-',

            'user_agent' => $this->activity->user_agent ?? '-',

            'created_at' => $this->activity->created_at?->toISOString(),
        ];
    }
}

