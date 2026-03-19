import { Form, Head } from '@inertiajs/react';
import { update } from '@/actions/App/Http/Controllers/Cms/SettingsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { edit } from '@/routes/cms/settings';
import type { BreadcrumbItem } from '@/types';

type SettingsFormData = {
    site_name: string;
    site_tagline?: string | null;
    default_locale: string;
    contact_email?: string | null;
    contact_phone?: string | null;
    contact_address?: string | null;
    google_analytics_id?: string | null;
    telegram_url?: string | null;
    youtube_url?: string | null;
    facebook_url?: string | null;
    linkedin_url?: string | null;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: edit(),
    },
];

export default function EditSettings({
    settings,
    status,
}: {
    settings: SettingsFormData;
    status?: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Settings" />

            <div className="space-y-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Settings</h1>
                    <p className="text-sm text-muted-foreground">
                        Manage global site configuration and contact details.
                    </p>
                </div>

                {status && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {status}
                    </div>
                )}

                <Form
                    {...update.form()}
                    options={{ preserveScroll: true }}
                    className="space-y-8"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-6 rounded-xl border p-6">
                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="site_name">
                                            Site name
                                        </Label>
                                        <Input
                                            id="site_name"
                                            name="site_name"
                                            defaultValue={settings.site_name}
                                        />
                                        <InputError
                                            message={errors.site_name}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="site_tagline">
                                            Tagline
                                        </Label>
                                        <Input
                                            id="site_tagline"
                                            name="site_tagline"
                                            defaultValue={
                                                settings.site_tagline ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.site_tagline}
                                        />
                                    </div>
                                </div>

                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="default_locale">
                                            Default locale
                                        </Label>
                                        <select
                                            id="default_locale"
                                            name="default_locale"
                                            defaultValue={
                                                settings.default_locale
                                            }
                                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                        >
                                            <option value="en">English</option>
                                            <option value="tj">Tajik</option>
                                            <option value="ru">Russian</option>
                                        </select>
                                        <InputError
                                            message={errors.default_locale}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="google_analytics_id">
                                            Google Analytics ID
                                        </Label>
                                        <Input
                                            id="google_analytics_id"
                                            name="google_analytics_id"
                                            defaultValue={
                                                settings.google_analytics_id ??
                                                ''
                                            }
                                        />
                                        <InputError
                                            message={
                                                errors.google_analytics_id
                                            }
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="grid gap-6 rounded-xl border p-6">
                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="contact_email">
                                            Contact email
                                        </Label>
                                        <Input
                                            id="contact_email"
                                            name="contact_email"
                                            defaultValue={
                                                settings.contact_email ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.contact_email}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="contact_phone">
                                            Contact phone
                                        </Label>
                                        <Input
                                            id="contact_phone"
                                            name="contact_phone"
                                            defaultValue={
                                                settings.contact_phone ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.contact_phone}
                                        />
                                    </div>
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="contact_address">
                                        Address
                                    </Label>
                                    <textarea
                                        id="contact_address"
                                        name="contact_address"
                                        defaultValue={
                                            settings.contact_address ?? ''
                                        }
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                    />
                                    <InputError
                                        message={errors.contact_address}
                                    />
                                </div>
                            </div>

                            <div className="grid gap-6 rounded-xl border p-6">
                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="telegram_url">
                                            Telegram
                                        </Label>
                                        <Input
                                            id="telegram_url"
                                            name="telegram_url"
                                            defaultValue={
                                                settings.telegram_url ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.telegram_url}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="youtube_url">
                                            YouTube
                                        </Label>
                                        <Input
                                            id="youtube_url"
                                            name="youtube_url"
                                            defaultValue={
                                                settings.youtube_url ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.youtube_url}
                                        />
                                    </div>
                                </div>
                                <div className="grid gap-2 md:grid-cols-2 md:gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="facebook_url">
                                            Facebook
                                        </Label>
                                        <Input
                                            id="facebook_url"
                                            name="facebook_url"
                                            defaultValue={
                                                settings.facebook_url ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.facebook_url}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="linkedin_url">
                                            LinkedIn
                                        </Label>
                                        <Input
                                            id="linkedin_url"
                                            name="linkedin_url"
                                            defaultValue={
                                                settings.linkedin_url ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.linkedin_url}
                                        />
                                    </div>
                                </div>
                            </div>

                            <Button disabled={processing}>
                                Save settings
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
