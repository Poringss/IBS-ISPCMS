<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCompleted extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via($notifiable): array
    {
        return ['database']; // stored in notifications table
    }

    public function toDatabase($notifiable): array
    {
        return [
            'task_id'   => $this->task->id,
            'title'     => $this->task->title ?? 'Task',
            'message'   => 'Task marked complete by staff.',
            'client_id' => $this->task->client_id ?? null,
        ];
    }
}
