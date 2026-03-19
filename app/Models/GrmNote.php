<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'grm_submission_id',
    'user_id',
    'note',
])]
class GrmNote extends Model
{
    public function submission(): BelongsTo
    {
        return $this->belongsTo(GrmSubmission::class, 'grm_submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
