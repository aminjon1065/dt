<?php

namespace App\Models;

use App\Policies\MenuPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(MenuPolicy::class)]
#[Fillable(['name', 'slug', 'location'])]
class Menu extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }
}
