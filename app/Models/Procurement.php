<?php

namespace App\Models;

use App\Policies\ProcurementPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[UsePolicy(ProcurementPolicy::class)]
#[Fillable([
    'reference_number',
    'procurement_type',
    'status',
    'published_at',
    'closing_at',
    'archived_at',
    'subscription_notified_at',
    'created_by',
    'updated_by',
])]
class Procurement extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'closing_at' => 'datetime',
            'archived_at' => 'datetime',
            'subscription_notified_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProcurementTranslation::class);
    }

    public function translation(string $locale): ?ProcurementTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
