<?php

namespace App\Console\Commands;

use App\Models\WeddingProject;
use App\Notifications\WeddingReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendWeddingReminders extends Command
{
    protected $signature = 'wedding:send-reminders';

    protected $description = 'Kirim reminder harian untuk task overdue, task deadline dekat, dan vendor perlu follow-up';

    public function handle(): int
    {
        $today = now()->toDateString();
        $soonLimit = now()->addDays(7)->toDateString();

        $projects = WeddingProject::with([
            'team.members',
        ])->get();

        foreach ($projects as $project) {
            $members = $project->team->members;
            if ($members->isEmpty()) {
                continue;
            }

            $this->sendTaskOverdueReminders($project, $members, $today);
            $this->sendTaskDueSoonReminders($project, $members, $today, $soonLimit);
            $this->sendVendorFollowupReminders($project, $members, $today);
        }

        $this->info('Reminder berhasil dikirim.');

        return self::SUCCESS;
    }

    /**
     * Cek apakah notifikasi untuk item ini sudah terkirim hari ini.
     * Mengembalikan true jika sudah ada (cegah duplikasi).
     * Saat Notification::fake() aktif, tabel notifications tidak diisi —
     * metode ini mengembalikan false (belum terkirim) agar notifikasi tetap dikirim.
     */
    private function alreadySentToday(string $type, int $relatedId, string $today): bool
    {
        try {
            return DB::table('notifications')
                ->where('type', WeddingReminderNotification::class)
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->related_id', $relatedId)
                ->whereJsonContains('data->type', $type)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Kirim notifikasi untuk task yang sudah melewati deadline dan belum selesai.
     */
    private function sendTaskOverdueReminders(WeddingProject $project, $members, string $today): void
    {
        $overdueTasks = DB::table('tasks')
            ->join('timeline_phases', 'tasks.timeline_phase_id', '=', 'timeline_phases.id')
            ->where('timeline_phases.wedding_project_id', $project->id)
            ->where('tasks.status', '!=', 'completed')
            ->whereNotNull('tasks.due_date')
            ->where('tasks.due_date', '<', $today)
            ->select('tasks.id', 'tasks.title', 'tasks.due_date')
            ->get();

        foreach ($overdueTasks as $task) {
            if ($this->alreadySentToday('task_overdue', $task->id, $today)) {
                continue;
            }

            $notification = new WeddingReminderNotification(
                type: 'task_overdue',
                message: "Task \"{$task->title}\" sudah melewati batas waktu ({$task->due_date}).",
                wedding_project_id: $project->id,
                related_id: $task->id,
            );

            foreach ($members as $member) {
                $member->notify($notification);
            }
        }
    }

    /**
     * Kirim notifikasi untuk task yang deadline-nya dalam 7 hari ke depan dan belum selesai.
     */
    private function sendTaskDueSoonReminders(WeddingProject $project, $members, string $today, string $soonLimit): void
    {
        $dueSoonTasks = DB::table('tasks')
            ->join('timeline_phases', 'tasks.timeline_phase_id', '=', 'timeline_phases.id')
            ->where('timeline_phases.wedding_project_id', $project->id)
            ->where('tasks.status', '!=', 'completed')
            ->whereNotNull('tasks.due_date')
            ->whereBetween('tasks.due_date', [$today, $soonLimit])
            ->select('tasks.id', 'tasks.title', 'tasks.due_date')
            ->get();

        foreach ($dueSoonTasks as $task) {
            if ($this->alreadySentToday('task_due_soon', $task->id, $today)) {
                continue;
            }

            $notification = new WeddingReminderNotification(
                type: 'task_due_soon',
                message: "Task \"{$task->title}\" jatuh tempo pada {$task->due_date}.",
                wedding_project_id: $project->id,
                related_id: $task->id,
            );

            foreach ($members as $member) {
                $member->notify($notification);
            }
        }
    }

    /**
     * Kirim notifikasi untuk vendor yang sudah melewati tanggal follow-up dan belum berstatus final.
     */
    private function sendVendorFollowupReminders(WeddingProject $project, $members, string $today): void
    {
        $finalStatuses = ['booked', 'dp_paid', 'paid', 'cancelled'];

        $vendors = DB::table('vendors')
            ->where('wedding_project_id', $project->id)
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', $today)
            ->whereNotIn('status', $finalStatuses)
            ->select('id', 'name', 'follow_up_date')
            ->get();

        foreach ($vendors as $vendor) {
            if ($this->alreadySentToday('vendor_followup', $vendor->id, $today)) {
                continue;
            }

            $notification = new WeddingReminderNotification(
                type: 'vendor_followup',
                message: "Vendor \"{$vendor->name}\" perlu di-follow up (jadwal: {$vendor->follow_up_date}).",
                wedding_project_id: $project->id,
                related_id: $vendor->id,
            );

            foreach ($members as $member) {
                $member->notify($notification);
            }
        }
    }
}
