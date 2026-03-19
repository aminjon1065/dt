<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug',
    'is_active',
])]
class DocumentCategory extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentCategoryTranslation::class);
    }

    public function translation(string $locale): ?DocumentCategoryTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
