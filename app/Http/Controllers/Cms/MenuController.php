<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Menu::class);

        $menus = Menu::query()
            ->withCount('items')
            ->orderBy('name')
            ->get()
            ->map(fn (Menu $menu): array => [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'location' => $menu->location,
                'items_count' => $menu->items_count,
            ]);

        return Inertia::render('cms/menus/index', [
            'menus' => $menus,
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Menu::class);

        return Inertia::render('cms/menus/create');
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $menu = DB::transaction(function () use ($request): Menu {
            $menu = Menu::query()->create($request->safe()->only([
                'name',
                'slug',
                'location',
            ]));

            $this->syncItems($menu, $request->validated('items', []));

            return $menu;
        });

        return to_route('cms.menus.edit', $menu)->with('status', 'menu-created');
    }

    public function edit(Menu $menu): Response
    {
        $this->authorize('update', $menu);

        $menu->load(['items' => fn ($query) => $query->orderBy('sort_order')]);

        return Inertia::render('cms/menus/edit', [
            'menu' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'location' => $menu->location,
                'items' => $menu->items->map(fn (MenuItem $item): array => [
                    'id' => $item->id,
                    'item_key' => 'existing-'.$item->id,
                    'parent_item_key' => $item->parent_id ? 'existing-'.$item->parent_id : null,
                    'label' => $item->label,
                    'locale' => $item->locale,
                    'url' => $item->url,
                    'route_name' => $item->route_name,
                    'sort_order' => $item->sort_order,
                    'is_active' => $item->is_active,
                ])->values()->all(),
            ],
            'status' => session('status'),
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        DB::transaction(function () use ($request, $menu): void {
            $menu->update($request->safe()->only([
                'name',
                'slug',
                'location',
            ]));

            $this->syncItems($menu, $request->validated('items', []));
        });

        return to_route('cms.menus.edit', $menu)->with('status', 'menu-updated');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->authorize('delete', $menu);

        $menu->delete();

        return to_route('cms.menus.index')->with('status', 'menu-deleted');
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function syncItems(Menu $menu, array $items): void
    {
        $existingIds = collect($items)
            ->pluck('id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        $menu->items()->when(
            count($existingIds) > 0,
            fn ($query) => $query->whereNotIn('id', $existingIds),
            fn ($query) => $query,
        )->delete();

        $resolvedItems = [];

        foreach ($items as $itemData) {
            $item = $menu->items()->updateOrCreate(
                ['id' => $itemData['id'] ?? null],
                [
                    'parent_id' => null,
                    'label' => $itemData['label'],
                    'locale' => $itemData['locale'] ?: null,
                    'url' => $itemData['url'] ?: null,
                    'route_name' => $itemData['route_name'] ?: null,
                    'sort_order' => $itemData['sort_order'] ?? 0,
                    'is_active' => (bool) ($itemData['is_active'] ?? false),
                ],
            );

            $resolvedItems[$itemData['item_key']] = $item;
        }

        foreach ($items as $itemData) {
            if (! empty($itemData['parent_item_key'])
                && isset($resolvedItems[$itemData['item_key']], $resolvedItems[$itemData['parent_item_key']])) {
                $resolvedItems[$itemData['item_key']]->update([
                    'parent_id' => $resolvedItems[$itemData['parent_item_key']]->id,
                ]);
            }
        }
    }
}
