<?php

use App\Models\DocumentChecklist;
use App\Models\Task;
use App\Models\TimelinePhase;
use App\Models\User;
use App\Models\WeddingProject;
use App\Notifications\WeddingReminderNotification;
use Database\Seeders\DocumentChecklistSeeder;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\artisan;

beforeEach(function () {
    // Ensure seed data exists
    if (DocumentChecklist::count() === 0) {
        (new DocumentChecklistSeeder)->run();
    }
});

// =====================================================================
// DOCUMENT CHECKLIST
// =====================================================================

test('checklist items exist for islam', function () {
    $items = DocumentChecklist::where('religion', 'islam')->get();
    expect($items)->not->toBeEmpty();
    expect($items->count())->toBeGreaterThanOrEqual(10);
});

test('checklist items exist for kristen', function () {
    $items = DocumentChecklist::where('religion', 'kristen')->get();
    expect($items)->not->toBeEmpty();
});

test('checklist items exist for katolik', function () {
    $items = DocumentChecklist::where('religion', 'katolik')->get();
    expect($items)->not->toBeEmpty();
});

test('checklist items have version and version_note', function () {
    $item = DocumentChecklist::first();
    expect($item->version)->not->toBeEmpty();
    expect($item->version_note)->not->toBeEmpty();
});

test('forProject returns items matching project religion', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $project = WeddingProject::create([
        'team_id' => $team->id,
        'partner_one_name' => 'A',
        'budget_total' => 50_000_000,
        'city' => 'Jakarta',
        'religion' => 'islam',
        'tradition' => 'jawa',
    ]);

    $items = DocumentChecklist::forProject($project);

    // Should include both universal (null religion) and islam-specific items
    expect($items)->not->toBeEmpty();
    expect($items->where('religion', 'islam')->count())->toBeGreaterThan(0);
});

// =====================================================================
// REMINDER COMMAND
// =====================================================================

test('send reminders command sends overdue task notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $team = $user->currentTeam;

    $project = WeddingProject::create([
        'team_id' => $team->id,
        'partner_one_name' => 'B',
        'budget_total' => 100_000_000,
        'city' => 'Bandung',
        'religion' => 'islam',
        'tradition' => 'sunda',
    ]);

    $phase = TimelinePhase::factory()->create(['wedding_project_id' => $project->id]);
    Task::factory()->create([
        'timeline_phase_id' => $phase->id,
        'status' => 'todo',
        'due_date' => now()->subDays(3)->toDateString(),
    ]);

    artisan('wedding:send-reminders')
        ->assertSuccessful();

    Notification::assertSentTo($user, WeddingReminderNotification::class);
});

test('send reminders command sends due soon task notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $team = $user->currentTeam;

    $project = WeddingProject::create([
        'team_id' => $team->id,
        'partner_one_name' => 'C',
        'budget_total' => 100_000_000,
        'city' => 'Surabaya',
        'religion' => 'kristen',
        'tradition' => null,
    ]);

    $phase = TimelinePhase::factory()->create(['wedding_project_id' => $project->id]);
    Task::factory()->create([
        'timeline_phase_id' => $phase->id,
        'status' => 'todo',
        'due_date' => now()->addDays(3)->toDateString(),
    ]);

    artisan('wedding:send-reminders')
        ->assertSuccessful();

    Notification::assertSentTo($user, WeddingReminderNotification::class);
});

test('send reminders command does not send for completed tasks', function () {
    Notification::fake();

    $user = User::factory()->create();
    $team = $user->currentTeam;

    $project = WeddingProject::create([
        'team_id' => $team->id,
        'partner_one_name' => 'D',
        'budget_total' => 100_000_000,
        'city' => 'Medan',
        'religion' => 'katolik',
        'tradition' => null,
    ]);

    $phase = TimelinePhase::factory()->create(['wedding_project_id' => $project->id]);
    Task::factory()->create([
        'timeline_phase_id' => $phase->id,
        'status' => 'completed',
        'due_date' => now()->subDays(3)->toDateString(),
    ]);

    artisan('wedding:send-reminders')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WeddingReminderNotification::class);
});

test('send reminders command prevents duplicate notifications on same day', function () {
    // Use real notifications (no fake) so DB duplicate check works
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $project = WeddingProject::create([
        'team_id' => $team->id,
        'partner_one_name' => 'E',
        'budget_total' => 100_000_000,
        'city' => 'Bali',
        'religion' => 'hindu',
        'tradition' => null,
    ]);

    $phase = TimelinePhase::factory()->create(['wedding_project_id' => $project->id]);
    $task = Task::factory()->create([
        'timeline_phase_id' => $phase->id,
        'status' => 'todo',
        'due_date' => now()->subDays(5)->toDateString(),
    ]);

    // First run — sends notification
    artisan('wedding:send-reminders')->assertSuccessful();
    $firstCount = DB::table('notifications')->count();
    expect($firstCount)->toBeGreaterThan(0);

    // Second run same day — should skip (duplicate prevention)
    artisan('wedding:send-reminders')->assertSuccessful();
    $secondCount = DB::table('notifications')->count();

    // No new notifications should have been added
    expect($secondCount)->toBe($firstCount);
});
