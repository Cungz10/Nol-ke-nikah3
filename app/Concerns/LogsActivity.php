<?php

namespace App\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Catat aktivitas ke activity_logs.
     */
    protected function logActivity(
        int $projectId,
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?string $description = null,
        ?array $properties = null,
    ): void {
        ActivityLog::create([
            'wedding_project_id' => $projectId,
            'user_id' => Auth::id(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    /**
     * Helper: log created event.
     */
    protected function logCreated(Model $model, int $projectId, ?string $description = null): void
    {
        $this->logActivity(
            projectId: $projectId,
            action: 'created',
            resourceType: $model->getTable(),
            resourceId: $model->id,
            description: $description,
            properties: $model->getAttributes(),
        );
    }

    /**
     * Helper: log updated event with changed attributes.
     */
    protected function logUpdated(Model $model, int $projectId, ?string $description = null): void
    {
        $this->logActivity(
            projectId: $projectId,
            action: 'updated',
            resourceType: $model->getTable(),
            resourceId: $model->id,
            description: $description,
            properties: $model->getChanges(),
        );
    }

    /**
     * Helper: log deleted event.
     */
    protected function logDeleted(Model $model, int $projectId, ?string $description = null): void
    {
        $this->logActivity(
            projectId: $projectId,
            action: 'deleted',
            resourceType: $model->getTable(),
            resourceId: $model->id,
            description: $description,
            properties: $model->getAttributes(),
        );
    }
}
