<?php

namespace App\Models;

use App\Policies\SubscriptionPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(SubscriptionPolicy::class)]
#[Fillable([
    'email',
    'locale',
    'status',
    'source',
    'subscribed_at',
    'unsubscribed_at',
    'last_notified_at',
    'notes',
])]
class Subscription extends Model
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'last_notified_at' => 'datetime',
        ];
    }
}
