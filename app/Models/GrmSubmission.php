<?php

namespace App\Models;

use App\Policies\GrmSubmissionPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(GrmSubmissionPolicy::class)]
#[Fillable([
    'reference_number',
    'name',
    'email',
    'phone',
    'subject',
    'message',
    'status',
    'submitted_at',
    'reviewed_at',
    'resolved_at',
    'assigned_to',
])]
class GrmSubmission extends Model
{
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(GrmNote::class)->latest();
    }
}
