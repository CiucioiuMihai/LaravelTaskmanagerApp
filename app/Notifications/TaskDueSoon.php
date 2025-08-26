<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task Due: '.$this->task->title)
            ->line('Your task "'.$this->task->title.'" is due on '.$this->task->due_date?->format('Y-m-d').'.')
            ->action('View Tasks', url(route('tasks.index')))
            ->line('If already completed, you can mark it as completed.');
    }
}
