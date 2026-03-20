<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'staff_member_id',
    'locale',
    'name',
    'slug',
    'position',
    'bio',
    'seo_title',
    'seo_description',
])]
class StaffMemberTranslation extends Model
{
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}
