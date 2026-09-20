<?php

namespace App\Notifications;

use App\Models\RepairRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class RepairStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public RepairRequest $repair,
        public string $statusFrom,
        public string $statusTo,
        public ?string $note = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $changedStatus = $this->statusFrom !== $this->statusTo;
        $message = $changedStatus
            ? 'งาน '.$this->repair->ticket_no.' เปลี่ยนเป็น'.RepairRequest::STATUSES[$this->statusTo]
            : 'ผู้ดูแลอัปเดตหมายเหตุของงาน '.$this->repair->ticket_no;

        return new DatabaseMessage([
            'type' => 'repair_update',
            'repair_id' => $this->repair->id,
            'ticket_no' => $this->repair->ticket_no,
            'title' => $this->repair->title,
            'status' => $this->statusTo,
            'status_label' => RepairRequest::STATUSES[$this->statusTo],
            'message' => $message,
            'note' => $this->note,
            'url' => route('repairs.show', $this->repair),
        ]);
    }
}
