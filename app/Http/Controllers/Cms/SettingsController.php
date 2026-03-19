<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        abort_unless(request()->user()?->can('settings.manage'), 403);

        return Inertia::render('cms/settings/edit', [
            'settings' => [
                'site_name' => Setting::for('site', 'name', config('app.name')),
                'site_tagline' => Setting::for('site', 'tagline'),
                'default_locale' => Setting::for('site', 'default_locale', 'en'),
                'contact_email' => Setting::for('contact', 'email'),
                'contact_phone' => Setting::for('contact', 'phone'),
                'contact_address' => Setting::for('contact', 'address'),
                'google_analytics_id' => Setting::for('analytics', 'google_analytics_id'),
                'telegram_url' => Setting::for('social', 'telegram_url'),
                'youtube_url' => Setting::for('social', 'youtube_url'),
                'facebook_url' => Setting::for('social', 'facebook_url'),
                'linkedin_url' => Setting::for('social', 'linkedin_url'),
            ],
            'status' => session('status'),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $map = [
            'site_name' => ['site', 'name', 'string'],
            'site_tagline' => ['site', 'tagline', 'string'],
            'default_locale' => ['site', 'default_locale', 'string'],
            'contact_email' => ['contact', 'email', 'string'],
            'contact_phone' => ['contact', 'phone', 'string'],
            'contact_address' => ['contact', 'address', 'string'],
            'google_analytics_id' => ['analytics', 'google_analytics_id', 'string'],
            'telegram_url' => ['social', 'telegram_url', 'string'],
            'youtube_url' => ['social', 'youtube_url', 'string'],
            'facebook_url' => ['social', 'facebook_url', 'string'],
            'linkedin_url' => ['social', 'linkedin_url', 'string'],
        ];

        foreach ($map as $inputKey => [$group, $key, $type]) {
            Setting::query()->updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['type' => $type, 'value' => $validated[$inputKey] ?? null],
            );
        }

        return to_route('cms.settings.edit')->with('status', 'settings-updated');
    }
}
