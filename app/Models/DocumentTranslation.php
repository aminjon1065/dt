<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'document_id',
    'locale',
    'title',
    'slug',
    'summary',
])]
class DocumentTranslation extends Model
{
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
