<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterAuditLogsRequest;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\GrmSubmission;
use App\Models\News;
use App\Models\Page;
use App\Models\Procurement;
use App\Models\StaffMember;
use App\Models\Subscription;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(FilterAuditLogsRequest $request): Response
    {
        $filters = $request->validated();

        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->when($filters['model'] ?? null, fn ($query, $model) => $query->where('auditable_type', $this->modelMap()[$model]))
            ->when($filters['actor'] ?? null, function ($query, $actor): void {
                $query->whereHas('user', function ($userQuery) use ($actor): void {
                    $userQuery
                        ->where('name', 'like', "%{$actor}%")
                        ->orWhere('email', 'like', "%{$actor}%");
                });
            })
            ->when($filters['date_from'] ?? null, fn ($query, $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn ($query, $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest('created_at')
            ->get()
            ->map(fn (AuditLog $log): array => [
                'id' => $log->id,
                'event' => $log->event,
                'event_label' => str($log->event)->replace('-', ' ')->title()->value(),
                'model' => $this->modelLabel($log->auditable_type),
                'record_id' => $log->auditable_id,
                'actor' => $log->user ? [
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at?->format('Y-m-d H:i'),
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'changed_fields' => collect(array_keys($log->new_values ?? []))
                    ->merge(array_keys($log->old_values ?? []))
                    ->unique()
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('cms/audit-logs/index', [
            'logs' => $logs,
            'filters' => [
                'event' => $filters['event'] ?? null,
                'model' => $filters['model'] ?? null,
                'actor' => $filters['actor'] ?? null,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
            ],
            'stats' => [
                'total' => AuditLog::query()->count(),
                'last_24_hours' => AuditLog::query()->where('created_at', '>=', now()->subDay())->count(),
                'public_actions' => AuditLog::query()->whereNull('user_id')->count(),
                'admin_actions' => AuditLog::query()->whereNotNull('user_id')->count(),
            ],
            'filterOptions' => [
                'events' => [
                    ['value' => 'created', 'label' => 'Created'],
                    ['value' => 'updated', 'label' => 'Updated'],
                    ['value' => 'deleted', 'label' => 'Deleted'],
                    ['value' => 'workflow-updated', 'label' => 'Workflow updated'],
                    ['value' => 'public-subscribed', 'label' => 'Public subscribed'],
                    ['value' => 'public-unsubscribed', 'label' => 'Public unsubscribed'],
                ],
                'models' => collect($this->modelMap())
                    ->map(fn (string $className, string $key): array => [
                        'value' => $key,
                        'label' => $this->modelLabel($className),
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    /**
     * @return array<string, class-string>
     */
    protected function modelMap(): array
    {
        return [
            'page' => Page::class,
            'news' => News::class,
            'document' => Document::class,
            'procurement' => Procurement::class,
            'grm' => GrmSubmission::class,
            'staff' => StaffMember::class,
            'subscription' => Subscription::class,
        ];
    }

    protected function modelLabel(?string $auditableType): string
    {
        return match ($auditableType) {
            Page::class => 'Page',
            News::class => 'News',
            Document::class => 'Document',
            Procurement::class => 'Procurement',
            GrmSubmission::class => 'GRM',
            StaffMember::class => 'Staff member',
            Subscription::class => 'Subscription',
            default => 'System',
        };
    }
}
