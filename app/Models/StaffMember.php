<?php

namespace App\Models;

use App\Policies\StaffMemberPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[UsePolicy(StaffMemberPolicy::class)]
#[Fillable([
    'parent_id',
    'email',
    'phone',
    'office_location',
    'show_email_publicly',
    'show_phone_publicly',
    'status',
    'published_at',
    'archived_at',
    'sort_order',
    'created_by',
    'updated_by',
])]
class StaffMember extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo')->singleFile();
    }

    protected function casts(): array
    {
        return [
            'show_email_publicly' => 'boolean',
            'show_phone_publicly' => 'boolean',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StaffMemberTranslation::class);
    }

    public function translation(string $locale): ?StaffMemberTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
