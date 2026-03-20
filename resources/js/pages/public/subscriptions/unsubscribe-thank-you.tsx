import { Link } from '@inertiajs/react';
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

export default function PublicSubscriptionUnsubscribeThankYou({
    site,
    navigation,
    seo,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
}) {
    return (
        <PublicLayout title="Unsubscribed" site={site} navigation={navigation} seo={seo}>
            <section className="mx-auto max-w-3xl px-4 py-20 text-center">
                <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                    Subscriptions
                </p>
                <h1 className="mt-4 text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                    You have been unsubscribed
                </h1>
                <p className="mt-4 text-lg leading-8 text-stone-600">
                    Future public updates will no longer be sent to your email address.
                </p>

                <div className="mt-8 flex flex-wrap justify-center gap-4 text-sm">
                    <Link href={`/${site.locale}/subscribe`} className="font-medium text-stone-900 underline">
                        Subscribe again
                    </Link>
                    <Link href={`/${site.locale}`} className="font-medium text-stone-900 underline">
                        Return to homepage
                    </Link>
                </div>
            </section>
        </PublicLayout>
    );
}
