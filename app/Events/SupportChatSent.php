<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportChatSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageData;

    public function __construct($messageData)
    {
        $this->messageData = $messageData;
    }

    // สร้าง Channel ส่วนตัวตาม ID ลูกค้า
    public function broadcastOn()
    {
        return new Channel('support-chat.' . $this->messageData->user_id);
    }

    // ชื่อ Event ที่ Frontend จะดักฟัง
    public function broadcastAs()
    {
        return 'message.sent';
    }
}