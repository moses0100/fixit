<?php

namespace App\Notifications;

use App\Models\RepairRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class NewRepairRequestReceived extends Notification
{
    use Queueable;

    public function __construct(public RepairRequest $repair) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'type' => 'new_repair',
            'repair_id' => $this->repair->id,
            'ticket_no' => $this->repair->ticket_no,
            'title' => $this->repair->title,
            'message' => 'มีรายการแจ้งซ่อมใหม่ '.$this->repair->ticket_no,
            'url' => route('admin.repairs.show', $this->repair),
        ]);
    }
}
