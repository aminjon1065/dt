<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\ContentStatus;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait HasCmsWorkflow
{
    protected function recordAudit(
        Request $request,
        string $event,
        Model $model,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => $event,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    protected function ensurePublishableStatus(Request $request, string $modelClass): void
    {
        if (in_array($request->input('status'), [ContentStatus::Published->value, ContentStatus::Archived->value], true)) {
            $this->authorize('publish', $modelClass);
        }
    }
}
