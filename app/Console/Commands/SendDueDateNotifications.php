<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskDueSoon;

class SendDueDateNotifications extends Command
{
    protected $signature = 'tasks:notify-due';
    protected $description = 'Send notifications for tasks due today or overdue';

    public function handle(): int
    {
        $tasks = Task::query()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->toDateString())
            ->whereNull('completed_at')
            ->with('user')
            ->get();

        foreach ($tasks as $task) {
            if ($task->user) {
                $task->user->notify(new TaskDueSoon($task));
            }
        }

        $this->info('Notifications dispatched for '.count($tasks).' tasks.');
        return self::SUCCESS;
    }
}
