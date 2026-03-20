import { Form, Link } from '@inertiajs/react';
import { unsubscribeStore } from '@/actions/App/Http/Controllers/PublicSubscriptionController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/public-layout';

type SiteData = {
    name: string;
    tagline?: string | null;
    contact_email?: string | null;
    contact_phone?: string | null;
    contact_address?: string | null;
    default_locale: string;
    locale: string;
};

type NavigationItem = {
    id: number;
    label: string;
    href: string;
    children: Array<{ id: number; label: string; href: string }>;
};

type SeoData = {
    title?: string | null;
    description?: string | null;
    canonical_url?: string | null;
    robots?: string | null;
    type?: string | null;
    image_url?: string | null;
};

export default function PublicSubscriptionUnsubscribe({
    locale,
    site,
    navigation,
    seo,
    prefillEmail,
}: {
    locale: string;
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    prefillEmail?: string | null;
}) {
    return (
        <PublicLayout title="Unsubscribe" site={site} navigation={navigation} seo={seo}>
            <section className="mx-auto max-w-3xl px-4 py-12 md:py-20">
                <div className="space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Subscriptions
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        Stop email updates
                    </h1>
                    <p className="text-lg leading-8 text-stone-600">
                        Enter your email address to unsubscribe from future portal notifications.
                    </p>
                </div>

                <div className="mt-10 rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm md:p-8">
                    <Form {...unsubscribeStore.form({ locale })} options={{ preserveScroll: true }} className="space-y-6">
                        {({ errors, processing }) => (
                            <>
                                <input type="hidden" name="locale" value={locale} />
                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>
                                    <Input id="email" name="email" type="email" defaultValue={prefillEmail ?? ''} />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="flex items-center justify-between gap-4 border-t border-stone-200 pt-4 text-sm text-stone-600">
                                    <p>You can subscribe again later from the public portal.</p>
                                    <Button disabled={processing} variant="destructive">Unsubscribe</Button>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <div className="mt-6 flex flex-wrap gap-4 text-sm text-stone-600">
                    <Link href={`/${site.locale}/subscribe`} className="font-medium text-stone-900 underline">
                        Return to subscribe page
                    </Link>
                    <Link href={`/${site.locale}`} className="font-medium text-stone-900 underline">
                        Return to homepage
                    </Link>
                </div>
            </section>
        </PublicLayout>
    );
}
