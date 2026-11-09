<?php
namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class SmChanged implements ShouldBroadcast {
 use Dispatchable,InteractsWithSockets,SerializesModels;
 public function __construct(public int $issueId,public string $action,public array $payload=[]){}
 public function broadcastOn():array{return [new PrivateChannel('sm')];}
 public function broadcastAs():string{return 'sm.changed';}
 public function broadcastWith():array{return ['issue_id'=>$this->issueId,'action'=>$this->action,'payload'=>$this->payload];}
}
