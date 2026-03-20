<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'procurement_id',
    'locale',
    'title',
    'slug',
    'summary',
    'content',
    'content_blocks',
    'seo_title',
    'seo_description',
])]
class ProcurementTranslation extends Model
{
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
        ];
    }
}
