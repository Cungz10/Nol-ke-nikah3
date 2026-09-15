<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TimelineController extends Controller
{
    public function index(Team $current_team): Response|RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        $phases = $project->timelinePhases()
            ->with(['tasks' => function ($query) {
                $query->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Timeline/Index', [
            'weddingProject' => $project,
            'phases' => $phases,
        ]);
    }

    public function storeTask(StoreTaskRequest $request, Team $current_team): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        $phase = $project->timelinePhases()->findOrFail($request->validated('timeline_phase_id'));

        $data = $request->validated();
        $data['sort_order'] = $phase->tasks()->max('sort_order') + 1;

        $task = $phase->tasks()->create($data);
        $this->logCreated($task, $project->id, 'Task ditambahkan: '.$task->title);

        return back()->with('success', 'Tugas berhasil ditambahkan');
    }

    public function updateTask(UpdateTaskRequest $request, Team $current_team, Task $task): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project || $task->timelinePhase->wedding_project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('update', $task);

        $task->update($request->validated());
        $this->logUpdated($task, $project->id, 'Task diperbarui: '.$task->title);

        return back()->with('success', 'Tugas berhasil diperbarui');
    }

    public function toggleTask(Team $current_team, Task $task): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project || $task->timelinePhase->wedding_project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('update', $task);

        $task->update(['is_completed' => ! $task->is_completed]);
        $this->logActivity($project->id, 'updated', 'tasks', $task->id, 'Task status diubah: '.$task->title);

        return back();
    }

    public function destroyTask(Team $current_team, Task $task): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project || $task->timelinePhase->wedding_project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('delete', $task);

        $taskTitle = $task->title;
        $this->logDeleted($task, $project->id, 'Task dihapus: '.$taskTitle);
        $task->delete();

        return back()->with('success', 'Tugas berhasil dihapus');
    }
}
