<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WeddingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $type,       // 'task_overdue' | 'task_due_soon' | 'vendor_followup'
        public readonly string $message,
        public readonly int $wedding_project_id,
        public readonly ?int $related_id = null,   // task_id or vendor_id
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'wedding_project_id' => $this->wedding_project_id,
            'related_id' => $this->related_id,
        ];
    }
}
