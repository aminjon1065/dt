<?php

namespace App\Http\Requests;

use App\Models\Menu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Menu::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:menus,slug'],
            'location' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.item_key' => ['required', 'string', 'max:100'],
            'items.*.parent_item_key' => ['nullable', 'string', 'max:100'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.locale' => ['nullable', Rule::in(['en', 'tj', 'ru'])],
            'items.*.url' => ['nullable', 'string', 'max:255'],
            'items.*.route_name' => ['nullable', 'string', 'max:255'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'items.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
