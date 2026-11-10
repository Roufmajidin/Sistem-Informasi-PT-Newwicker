<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PengajuanHargaNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public int $recipientId, public array $notification) {}
    public function broadcastOn(): array { return [new PrivateChannel('pengajuan-harga.user.' . $this->recipientId)]; }
    public function broadcastAs(): string { return 'notification.created'; }
    public function broadcastWith(): array { return $this->notification; }
}
