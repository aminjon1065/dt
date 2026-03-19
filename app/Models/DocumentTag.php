<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug',
])]
class DocumentTag extends Model
{
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentTagTranslation::class);
    }

    public function translation(string $locale): ?DocumentTagTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
