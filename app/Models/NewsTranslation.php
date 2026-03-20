<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'news_id',
    'locale',
    'title',
    'slug',
    'summary',
    'content',
    'content_blocks',
    'seo_title',
    'seo_description',
])]
class NewsTranslation extends Model
{
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
        ];
    }
}
