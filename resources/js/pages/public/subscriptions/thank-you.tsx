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

export default function PublicSubscriptionThankYou({
    site,
    navigation,
    seo,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
}) {
    return (
        <PublicLayout title="Subscription confirmed" site={site} navigation={navigation} seo={seo}>
            <section className="mx-auto max-w-3xl px-4 py-16 md:py-24">
                <div className="rounded-[2rem] border border-stone-200 bg-white p-8 shadow-sm md:p-12">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Subscription recorded
                    </p>
                    <h1 className="mt-4 text-4xl font-semibold tracking-tight text-stone-950">
                        Thank you for subscribing
                    </h1>
                    <p className="mt-4 text-lg leading-8 text-stone-600">
                        Your email has been added to the public updates list.
                    </p>
                    <div className="mt-8 flex gap-4 text-sm">
                        <Link href={`/${site.locale}`} className="font-medium text-stone-900 underline">
                            Return to homepage
                        </Link>
                        <Link href={`/${site.locale}/news`} className="font-medium text-stone-900 underline">
                            Browse news
                        </Link>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
