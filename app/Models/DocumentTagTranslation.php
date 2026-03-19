<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'document_tag_id',
    'locale',
    'name',
])]
class DocumentTagTranslation extends Model
{
    public function tag(): BelongsTo
    {
        return $this->belongsTo(DocumentTag::class, 'document_tag_id');
    }
}
