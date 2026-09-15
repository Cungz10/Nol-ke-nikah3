<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, Team $current_team): Response|RedirectResponse
    {
        $email = strtolower($request->user()->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        $project = $current_team->weddingProject;

        if (! $project) {
            return Inertia::render('Dashboard', [
                'pendingInvitations' => $pendingInvitations,
                'weddingProject' => null,
                'metrics' => [
                    'totalTasks' => 0,
                    'completedTasks' => 0,
                    'overdueTasksCount' => 0,
                    'progressPercentage' => 0,
                    'totalBudget' => 0,
                    'totalSpent' => 0,
                    'remainingBudget' => 0,
                ],
                'urgentTasks' => [],
                'vendorsToFollowUp' => [],
            ]);
        }

        // Dashboard Metrics
        $phases = $project->timelinePhases()->with('tasks')->get();
        $allTasks = $phases->flatMap->tasks;
        $totalTasks = $allTasks->count();
        $completedTasks = $allTasks->where('status', 'completed')->count();
        $todayStr = now()->toDateString();
        $overdueTasksCount = $allTasks->filter(fn ($t) => $t->status !== 'completed' && $t->due_date && $t->due_date < $todayStr)->count();

        $urgentTasks = $allTasks
            ->where('status', '!=', 'completed')
            ->sortBy(function ($t) {
                return [$t->is_critical ? 0 : 1, $t->due_date ?? '9999-12-31'];
            })
            ->take(5)
            ->values();

        // Budget summary
        $totalSpent = (int) $project->payments()->sum('amount_idr');

        // Vendors to follow up
        $vendorsToFollowUp = $project->vendors()
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', now()->addDays(7)->toDateString())
            ->whereNotIn('status', ['fully_paid', 'cancelled'])
            ->with('vendorCategory')
            ->orderBy('follow_up_date')
            ->take(4)
            ->get();

        return Inertia::render('Dashboard', [
            'pendingInvitations' => $pendingInvitations,
            'weddingProject' => $project,
            'metrics' => [
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'overdueTasksCount' => $overdueTasksCount,
                'progressPercentage' => $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0,
                'totalBudget' => $project->budget_total,
                'totalSpent' => $totalSpent,
                'remainingBudget' => $project->budget_total - $totalSpent,
            ],
            'urgentTasks' => $urgentTasks,
            'vendorsToFollowUp' => $vendorsToFollowUp,
        ]);
    }
}
