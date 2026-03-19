<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'procurement_id',
    'locale',
    'title',
    'slug',
    'summary',
    'content',
    'seo_title',
    'seo_description',
])]
class ProcurementTranslation extends Model
{
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }
}
