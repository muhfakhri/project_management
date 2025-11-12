<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Models\Project;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deadlines:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and notify users about upcoming and overdue deadlines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking deadlines...');

        $this->checkTaskDeadlines();
        $this->checkProjectDeadlines();

        $this->info('Deadline check completed!');
    }

    private function checkTaskDeadlines()
    {
        $now = Carbon::now();

        // Get tasks with due dates that are not done
        $tasks = Card::whereNotNull('due_date')
            ->where('status', '!=', 'done')
            ->get();

        $notified = 0;

        foreach ($tasks as $task) {
            $dueDate = Carbon::parse($task->due_date);
            $daysUntilDue = $now->diffInDays($dueDate, false);

            // Overdue tasks
            if ($daysUntilDue < 0 && !$task->is_overdue) {
                $task->markAsOverdue();
                $this->notifyTaskOverdue($task);
                $notified++;
            }

            // Due today
            elseif ($daysUntilDue == 0 && !$task->deadline_notified_at) {
                $this->notifyTaskDueToday($task);
                $task->update(['deadline_notified_at' => $now]);
                $notified++;
            }

            // Due tomorrow
            elseif ($daysUntilDue == 1 && !$task->deadline_notified_at) {
                $this->notifyTaskDueTomorrow($task);
                $task->update(['deadline_notified_at' => $now]);
                $notified++;
            }

            // Due in 3 days
            elseif ($daysUntilDue == 3 && !$task->deadline_notified_at) {
                $this->notifyTaskDueSoon($task, 3);
                $task->update(['deadline_notified_at' => $now]);
                $notified++;
            }

            // Clear overdue if task is no longer overdue
            if ($daysUntilDue >= 0 && $task->is_overdue) {
                $task->clearOverdue();
            }
        }

        $this->info("Tasks checked: {$tasks->count()}, notifications sent: {$notified}");
    }

    private function checkProjectDeadlines()
    {
        $now = Carbon::now();

        // Get active projects with deadlines
        $projects = Project::whereNotNull('deadline')
            ->where('is_archived', false)
            ->get();

        $notified = 0;

        foreach ($projects as $project) {
            $deadline = Carbon::parse($project->deadline);
            $daysUntilDue = $now->diffInDays($deadline, false);

            // Overdue projects
            if ($daysUntilDue < 0 && !$project->is_overdue) {
                $project->update(['is_overdue' => true]);
                $this->notifyProjectOverdue($project);
                $notified++;
            }

            // Due in 3 days
            elseif ($daysUntilDue == 3 && !$project->deadline_notified_at) {
                $this->notifyProjectDueSoon($project, 3);
                $project->update(['deadline_notified_at' => $now]);
                $notified++;
            }

            // Clear overdue if project is no longer overdue
            if ($daysUntilDue >= 0 && $project->is_overdue) {
                $project->update(['is_overdue' => false, 'deadline_notified_at' => null]);
            }
        }

        $this->info("Projects checked: {$projects->count()}, notifications sent: {$notified}");
    }

    private function notifyTaskOverdue(Card $task)
    {
        // Notify assignees
        foreach ($task->assignees as $assignee) {
            Notification::create([
                'user_id' => $assignee->user_id,
                'type' => 'task_overdue',
                'title' => 'Task Overdue',
                'message' => "Task '{$task->card_title}' is now overdue!",
                'url' => route('tasks.show', $task->card_id),
                'read' => false
            ]);
        }

        // Notify creator
        if ($task->creator) {
            Notification::create([
                'user_id' => $task->created_by,
                'type' => 'task_overdue',
                'title' => 'Task Overdue',
                'message' => "Your task '{$task->card_title}' is now overdue!",
                'url' => route('tasks.show', $task->card_id),
                'read' => false
            ]);
        }
    }

    private function notifyTaskDueToday(Card $task)
    {
        foreach ($task->assignees as $assignee) {
            Notification::create([
                'user_id' => $assignee->user_id,
                'type' => 'task_due_today',
                'title' => 'Task Due Today',
                'message' => "Task '{$task->card_title}' is due today!",
                'url' => route('tasks.show', $task->card_id),
                'read' => false
            ]);
        }
    }

    private function notifyTaskDueTomorrow(Card $task)
    {
        foreach ($task->assignees as $assignee) {
            Notification::create([
                'user_id' => $assignee->user_id,
                'type' => 'task_due_soon',
                'title' => 'Task Due Tomorrow',
                'message' => "Task '{$task->card_title}' is due tomorrow!",
                'url' => route('tasks.show', $task->card_id),
                'read' => false
            ]);
        }
    }

    private function notifyTaskDueSoon(Card $task, int $days)
    {
        foreach ($task->assignees as $assignee) {
            Notification::create([
                'user_id' => $assignee->user_id,
                'type' => 'task_due_soon',
                'title' => 'Task Due Soon',
                'message' => "Task '{$task->card_title}' is due in {$days} days!",
                'url' => route('tasks.show', $task->card_id),
                'read' => false
            ]);
        }
    }

    private function notifyProjectOverdue(Project $project)
    {
        // Notify all project members
        foreach ($project->members as $member) {
            Notification::create([
                'user_id' => $member->user_id,
                'type' => 'project_overdue',
                'title' => 'Project Overdue',
                'message' => "Project '{$project->project_name}' is now overdue!",
                'url' => route('projects.show', $project->project_id),
                'read' => false
            ]);
        }
    }

    private function notifyProjectDueSoon(Project $project, int $days)
    {
        // Notify project admin and team leads
        $projectAdmins = $project->members()->whereIn('role', ['Project Admin', 'Team Lead'])->get();
        
        foreach ($projectAdmins as $admin) {
            Notification::create([
                'user_id' => $admin->user_id,
                'type' => 'project_due_soon',
                'title' => 'Project Due Soon',
                'message' => "Project '{$project->project_name}' is due in {$days} days!",
                'url' => route('projects.show', $project->project_id),
                'read' => false
            ]);
        }
    }
}
