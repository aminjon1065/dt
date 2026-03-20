import { Form, Link } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/PublicSubscriptionController';
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

export default function PublicSubscriptionCreate({
    locale,
    site,
    navigation,
    seo,
}: {
    locale: string;
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
}) {
    return (
        <PublicLayout title="Subscribe" site={site} navigation={navigation} seo={seo}>
            <section className="mx-auto max-w-3xl px-4 py-12 md:py-20">
                <div className="space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Subscriptions
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        Subscribe to official updates
                    </h1>
                    <p className="text-lg leading-8 text-stone-600">
                        Receive important announcements and public updates by email.
                    </p>
                </div>

                <div className="mt-10 rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm md:p-8">
                    <Form {...store.form({ locale })} options={{ preserveScroll: true }} className="space-y-6">
                        {({ errors, processing }) => (
                            <>
                                <input type="hidden" name="locale" value={locale} />
                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>
                                    <Input id="email" name="email" type="email" />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="flex items-center justify-between gap-4 border-t border-stone-200 pt-4 text-sm text-stone-600">
                                    <p>We will only use this email for public portal updates.</p>
                                    <Button disabled={processing}>Subscribe</Button>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <div className="mt-6 grid gap-4 rounded-[2rem] border border-stone-200 bg-stone-100/80 p-6 md:grid-cols-3">
                    <div>
                        <h2 className="text-base font-semibold text-stone-950">What you receive</h2>
                        <p className="mt-2 text-sm leading-7 text-stone-600">
                            News, procurement notices, and newly published public documents.
                        </p>
                    </div>
                    <div>
                        <h2 className="text-base font-semibold text-stone-950">Language</h2>
                        <p className="mt-2 text-sm leading-7 text-stone-600">
                            Updates use the current site language when matching content is available.
                        </p>
                    </div>
                    <div>
                        <h2 className="text-base font-semibold text-stone-950">Need to stop updates?</h2>
                        <p className="mt-2 text-sm leading-7 text-stone-600">
                            You can unsubscribe at any time without contacting an administrator.
                        </p>
                    </div>
                </div>

                <div className="mt-6 flex flex-wrap gap-4 text-sm text-stone-600">
                    <Link href={`/${site.locale}`} className="font-medium text-stone-900 underline">
                        Return to homepage
                    </Link>
                    <Link
                        href={`/${site.locale}/unsubscribe`}
                        className="font-medium text-stone-900 underline"
                    >
                        Unsubscribe from updates
                    </Link>
                </div>
            </section>
        </PublicLayout>
    );
}
