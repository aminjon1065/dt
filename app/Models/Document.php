<?php

namespace App\Models;

use App\Policies\DocumentPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[UsePolicy(DocumentPolicy::class)]
#[Fillable([
    'document_category_id',
    'status',
    'file_type',
    'document_date',
    'published_at',
    'archived_at',
    'created_by',
    'updated_by',
])]
class Document extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')->singleFile();
    }

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DocumentTag::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentTranslation::class);
    }

    public function translation(string $locale): ?DocumentTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
