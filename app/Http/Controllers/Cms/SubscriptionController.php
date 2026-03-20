<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterSubscriptionsRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionWorkflowRequest;
use App\Models\AuditLog;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(FilterSubscriptionsRequest $request): Response
    {
        $this->authorize('viewAny', Subscription::class);

        $filters = $request->validated();

        $subscriptions = Subscription::query();

        if (($search = $filters['search'] ?? null) !== null) {
            $subscriptions->where('email', 'like', "%{$search}%");
        }

        if (($status = $filters['status'] ?? null) !== null) {
            $subscriptions->where('status', $status);
        }

        if (($locale = $filters['locale'] ?? null) !== null) {
            $subscriptions->where('locale', $locale);
        }

        $subscriptions = $subscriptions
            ->orderByDesc('subscribed_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Subscription $subscription): array => [
                'id' => $subscription->id,
                'email' => $subscription->email,
                'locale' => $subscription->locale,
                'status' => $subscription->status,
                'source' => $subscription->source,
                'subscribed_at' => $subscription->subscribed_at?->toIso8601String(),
                'unsubscribed_at' => $subscription->unsubscribed_at?->toIso8601String(),
                'last_notified_at' => $subscription->last_notified_at?->toIso8601String(),
            ]);

        return Inertia::render('cms/subscriptions/index', [
            'subscriptions' => $subscriptions,
            'filters' => [
                'search' => $filters['search'] ?? null,
                'status' => $filters['status'] ?? null,
                'locale' => $filters['locale'] ?? null,
            ],
            'stats' => [
                'total' => Subscription::query()->count(),
                'active' => Subscription::query()->where('status', 'active')->count(),
                'unsubscribed' => Subscription::query()->where('status', 'unsubscribed')->count(),
                'bounced' => Subscription::query()->where('status', 'bounced')->count(),
            ],
            'status' => session('status'),
        ]);
    }

    public function edit(Subscription|string $subscription): Response
    {
        $subscription = $subscription instanceof Subscription
            ? $subscription
            : Subscription::query()->findOrFail($subscription);

        $this->authorize('update', $subscription);

        return Inertia::render('cms/subscriptions/edit', [
            'subscription' => [
                'id' => $subscription->id,
                'email' => $subscription->email,
                'locale' => $subscription->locale,
                'status' => $subscription->status,
                'source' => $subscription->source,
                'subscribed_at' => $subscription->subscribed_at?->format('Y-m-d\\TH:i'),
                'unsubscribed_at' => $subscription->unsubscribed_at?->format('Y-m-d\\TH:i'),
                'last_notified_at' => $subscription->last_notified_at?->format('Y-m-d\\TH:i'),
                'notes' => $subscription->notes,
            ],
            'status' => session('status'),
        ]);
    }

    public function update(UpdateSubscriptionRequest $request, Subscription|string $subscription): RedirectResponse
    {
        $subscription = $subscription instanceof Subscription
            ? $subscription
            : Subscription::query()->findOrFail($subscription);

        $oldValues = $subscription->fresh()->toArray();

        $subscription->update($request->validated());

        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => 'updated',
            'auditable_type' => $subscription->getMorphClass(),
            'auditable_id' => $subscription->id,
            'old_values' => $oldValues,
            'new_values' => $subscription->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return to_route('cms.subscriptions.edit', $subscription)->with('status', 'subscription-updated');
    }

    public function destroy(Request $request, Subscription|string $subscription): RedirectResponse
    {
        $subscription = $subscription instanceof Subscription
            ? $subscription
            : Subscription::query()->findOrFail($subscription);

        $this->authorize('delete', $subscription);

        $oldValues = $subscription->toArray();
        $subscription->delete();

        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => 'deleted',
            'auditable_type' => $subscription->getMorphClass(),
            'auditable_id' => $subscription->id,
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return to_route('cms.subscriptions.index')->with('status', 'subscription-deleted');
    }

    public function workflow(
        UpdateSubscriptionWorkflowRequest $request,
        Subscription|string $subscription,
    ): RedirectResponse {
        $subscription = $subscription instanceof Subscription
            ? $subscription
            : Subscription::query()->findOrFail($subscription);

        $oldValues = $subscription->toArray();
        $status = $request->string('status')->toString();

        $subscription->update([
            'status' => $status,
            'unsubscribed_at' => $status === 'unsubscribed'
                ? ($subscription->unsubscribed_at ?? now())
                : null,
        ]);

        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => 'workflow-updated',
            'auditable_type' => $subscription->getMorphClass(),
            'auditable_id' => $subscription->id,
            'old_values' => $oldValues,
            'new_values' => $subscription->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('status', 'subscription-workflow-updated');
    }
}
