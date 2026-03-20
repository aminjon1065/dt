<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\StaffMember;
use Inertia\Inertia;
use Inertia\Response;

class PublicStaffController extends Controller
{
    public function index(string $locale): Response
    {
        $staffMembers = StaffMember::query()
            ->with([
                'translations',
                'children' => fn ($query) => $query
                    ->where('status', 'published')
                    ->where(fn ($childQuery) => $childQuery->whereNull('published_at')->orWhere('published_at', '<=', now()))
                    ->with('translations')
                    ->orderBy('sort_order'),
            ])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (StaffMember $staffMember): array => $this->transformStaffMemberListItem($staffMember, $locale))
            ->values()
            ->all();

        return Inertia::render('public/staff/index', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => 'Staff directory',
                'description' => 'Public staff directory with contacts, roles, and hierarchy.',
                'canonical_url' => route('public.staff.index', ['locale' => $locale]),
                'type' => 'website',
            ],
            'staffMembers' => $staffMembers,
        ]);
    }

    public function show(string $locale, string $slug): Response
    {
        $staffMember = StaffMember::query()
            ->with([
                'translations',
                'parent.translations',
                'children' => fn ($query) => $query
                    ->where('status', 'published')
                    ->where(fn ($childQuery) => $childQuery->whereNull('published_at')->orWhere('published_at', '<=', now()))
                    ->with('translations')
                    ->orderBy('sort_order'),
            ])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->whereHas('translations', function ($query) use ($locale, $slug): void {
                $query->where('locale', $locale)->where('slug', $slug);
            })
            ->firstOrFail();

        $translation = $staffMember->translation($locale) ?? $staffMember->translation('en');

        return Inertia::render('public/staff/show', [
            'locale' => $locale,
            'site' => $this->siteData($locale),
            'navigation' => app(PublicPageController::class)->navigation($locale),
            'seo' => [
                'title' => $translation?->seo_title ?? $translation?->name,
                'description' => $translation?->seo_description ?? $translation?->position ?? Setting::for('site', 'tagline'),
                'canonical_url' => route('public.staff.show', ['locale' => $locale, 'slug' => $slug]),
                'type' => 'profile',
                'image_url' => $staffMember->getFirstMediaUrl('profile_photo') ?: null,
            ],
            'structuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $translation?->name,
                'jobTitle' => $translation?->position,
                'description' => $translation?->bio,
                'url' => route('public.staff.show', ['locale' => $locale, 'slug' => $slug]),
                'image' => $staffMember->getFirstMediaUrl('profile_photo') ?: null,
            ]],
            'staffMember' => $this->transformStaffMember($staffMember, $locale),
        ]);
    }

    protected function siteData(string $locale): array
    {
        return [
            'name' => Setting::for('site', 'name', config('app.name')),
            'tagline' => Setting::for('site', 'tagline'),
            'default_locale' => Setting::for('site', 'default_locale', 'en'),
            'contact_email' => Setting::for('contact', 'email'),
            'contact_phone' => Setting::for('contact', 'phone'),
            'contact_address' => Setting::for('contact', 'address'),
            'locale' => $locale,
        ];
    }

    protected function transformStaffMemberListItem(StaffMember $staffMember, string $locale): array
    {
        $translation = $staffMember->translation($locale) ?? $staffMember->translation('en');

        return [
            'id' => $staffMember->id,
            'name' => $translation?->name,
            'slug' => $translation?->slug,
            'position' => $translation?->position,
            'email' => $staffMember->show_email_publicly ? $staffMember->email : null,
            'phone' => $staffMember->show_phone_publicly ? $staffMember->phone : null,
            'office_location' => $staffMember->office_location,
            'photo_url' => $staffMember->getFirstMediaUrl('profile_photo') ?: null,
            'children' => $staffMember->children->map(function (StaffMember $child) use ($locale): array {
                $translation = $child->translation($locale) ?? $child->translation('en');

                return [
                    'id' => $child->id,
                    'name' => $translation?->name,
                    'slug' => $translation?->slug,
                    'position' => $translation?->position,
                ];
            })->values()->all(),
        ];
    }

    protected function transformStaffMember(StaffMember $staffMember, string $locale): array
    {
        $translation = $staffMember->translation($locale) ?? $staffMember->translation('en');

        abort_unless($translation !== null, 404);

        return [
            'id' => $staffMember->id,
            'name' => $translation->name,
            'slug' => $translation->slug,
            'position' => $translation->position,
            'bio' => $translation->bio,
            'email' => $staffMember->show_email_publicly ? $staffMember->email : null,
            'phone' => $staffMember->show_phone_publicly ? $staffMember->phone : null,
            'office_location' => $staffMember->office_location,
            'photo_url' => $staffMember->getFirstMediaUrl('profile_photo') ?: null,
            'parent' => $staffMember->parent ? [
                'name' => $staffMember->parent->translation($locale)?->name
                    ?? $staffMember->parent->translation('en')?->name,
                'slug' => $staffMember->parent->translation($locale)?->slug
                    ?? $staffMember->parent->translation('en')?->slug,
            ] : null,
            'children' => $staffMember->children->map(function (StaffMember $child) use ($locale): array {
                $translation = $child->translation($locale) ?? $child->translation('en');

                return [
                    'id' => $child->id,
                    'name' => $translation?->name,
                    'slug' => $translation?->slug,
                    'position' => $translation?->position,
                ];
            })->values()->all(),
        ];
    }
}
