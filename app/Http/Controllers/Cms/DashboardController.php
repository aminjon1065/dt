<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterDashboardActivityRequest;
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

class DashboardController extends Controller
{
    public function __invoke(FilterDashboardActivityRequest $request): Response
    {
        $filters = $request->validated();

        $activity = AuditLog::query()
            ->with('user:id,name,email')
            ->when($filters['event'] ?? null, function ($query, string $event): void {
                $query->where('event', $event);
            })
            ->when($filters['model'] ?? null, function ($query, string $model): void {
                $query->where('auditable_type', $this->modelMap()[$model]);
            })
            ->when($filters['actor'] ?? null, function ($query, string $actor): void {
                $query->whereHas('user', function ($userQuery) use ($actor): void {
                    $userQuery->where('name', 'like', "%{$actor}%")
                        ->orWhere('email', 'like', "%{$actor}%");
                });
            })
            ->when($filters['date_from'] ?? null, function ($query, string $dateFrom): void {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, string $dateTo): void {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest('created_at')
            ->limit(25)
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
                'created_at' => $log->created_at?->format('Y-m-d H:i'),
            ])
            ->values()
            ->all();

        return Inertia::render('dashboard', [
            'stats' => $this->stats(),
            'activity' => $activity,
            'filters' => [
                'event' => $filters['event'] ?? null,
                'model' => $filters['model'] ?? null,
                'actor' => $filters['actor'] ?? null,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
            ],
            'filterOptions' => [
                'events' => [
                    ['value' => 'created', 'label' => 'Created'],
                    ['value' => 'updated', 'label' => 'Updated'],
                    ['value' => 'deleted', 'label' => 'Deleted'],
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
     * @return array<string, int>
     */
    protected function stats(): array
    {
        return [
            'published_pages' => Page::query()->where('status', 'published')->count(),
            'published_news' => News::query()->where('status', 'published')->count(),
            'published_documents' => Document::query()->where('status', 'published')->count(),
            'active_procurements' => Procurement::query()->where('status', 'open')->count(),
            'new_grm_submissions' => GrmSubmission::query()->where('status', 'new')->count(),
            'published_staff' => StaffMember::query()->where('status', 'published')->count(),
            'active_subscriptions' => Subscription::query()->where('status', 'active')->count(),
            'recent_activity' => AuditLog::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];
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
