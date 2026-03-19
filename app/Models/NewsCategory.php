<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'is_active'])]
class NewsCategory extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(NewsCategoryTranslation::class);
    }

    public function translation(string $locale): ?NewsCategoryTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
