<?php

namespace App\Http\Requests;

use App\Models\Menu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('menu'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Menu $menu */
        $menu = $this->route('menu');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('menus', 'slug')->ignore($menu->id)],
            'location' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer', 'exists:menu_items,id'],
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
