<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'page_id',
    'locale',
    'title',
    'slug',
    'summary',
    'content',
    'seo_title',
    'seo_description',
])]
class PageTranslation extends Model
{
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
