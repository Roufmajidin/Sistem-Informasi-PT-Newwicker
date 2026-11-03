<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QcInspectionSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $inspection;

    public function __construct(array $inspection)
    {
        $type = strtolower(trim((string) ($inspection['type'] ?? '')));

        $this->inspection = [
            'id' => $inspection['id'] ?? null,
            'type' => $type,
            'user' => $inspection['user'] ?? 'QC',
            'po_id' => $inspection['po_id'] ?? null,
            'spk_id' => $inspection['spk_id'] ?? null,
            'spk_no' => $inspection['spk_no'] ?? '-',
            'item' => $inspection['item'] ?? '-',
            'kategori' => $inspection['kategori'] ?? '-',
            'passed' => (int) ($inspection['passed'] ?? 0),
            'rejected' => (int) ($inspection['rejected'] ?? 0),
            'qty' => (int) (
                $inspection['qty']
                ?? (
                    (int) ($inspection['passed'] ?? 0)
                    + (int) ($inspection['rejected'] ?? 0)
                )
            ),
            'created_at' => $inspection['created_at']
                ?? now()->toIso8601String(),
        ];
    }

    public function broadcastOn(): array
    {
        // Hanya QC Inspection POST dan QC Incoming
        if (!in_array($this->inspection['type'], [
            'post',
            'incoming',
        ], true)) {
            return [];
        }

        return [
            new PrivateChannel('qc-activity'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'qc.inspection.submitted';
    }

    public function broadcastWith(): array
    {
        return $this->inspection;
    }
}
