<?php

use App\Console\Commands\SendWeddingReminders;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    TeamInvitation::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->delete();
})->daily()->description('Delete expired team invitations');

// Reminder harian: task overdue, task due soon, vendor follow-up
// withoutOverlapping() dan onOneServer() mencegah duplikasi di multi-server production
Schedule::command(SendWeddingReminders::class)
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->description('Kirim reminder pernikahan harian');
