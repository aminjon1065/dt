<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'document_id',
    'locale',
    'title',
    'slug',
    'summary',
    'content',
    'content_blocks',
    'seo_title',
    'seo_description',
])]
class DocumentTranslation extends Model
{
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
        ];
    }
}
