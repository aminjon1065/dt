<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'news_id',
    'locale',
    'title',
    'slug',
    'summary',
    'content',
    'seo_title',
    'seo_description',
])]
class NewsTranslation extends Model
{
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }
}
