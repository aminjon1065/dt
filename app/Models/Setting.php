<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'key', 'type', 'value'])]
class Setting extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function for(string $group, string $key, mixed $default = null): mixed
    {
        return static::query()
            ->where('group', $group)
            ->where('key', $key)
            ->value('value') ?? $default;
    }
}
