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

type StaffMemberData = {
    id: number;
    name: string;
    slug: string;
    position?: string | null;
    bio?: string | null;
    email?: string | null;
    phone?: string | null;
    office_location?: string | null;
    photo_url?: string | null;
    parent?: { name?: string | null; slug?: string | null } | null;
    children: Array<{ id: number; name?: string | null; slug?: string | null; position?: string | null }>;
};

export default function PublicStaffShow({
    site,
    navigation,
    seo,
    structuredData,
    staffMember,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    structuredData?: Array<Record<string, unknown>>;
    staffMember: StaffMemberData;
}) {
    return (
        <PublicLayout
            title={staffMember.name}
            site={site}
            navigation={navigation}
            seo={seo}
            structuredData={structuredData}
        >
            <article className="mx-auto max-w-5xl px-4 py-12 md:py-20">
                <div className="grid gap-8 md:grid-cols-[18rem_minmax(0,1fr)]">
                    <div className="space-y-4">
                        <div className="overflow-hidden rounded-[2rem] border border-stone-200 bg-stone-100 shadow-sm">
                            {staffMember.photo_url ? (
                                <img
                                    src={staffMember.photo_url}
                                    alt={staffMember.name}
                                    loading="lazy"
                                    decoding="async"
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                <div className="flex aspect-[4/5] items-center justify-center text-5xl font-semibold text-stone-400">
                                    {staffMember.name.slice(0, 1)}
                                </div>
                            )}
                        </div>

                        <div className="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-sm">
                            <h2 className="text-xs uppercase tracking-[0.18em] text-stone-500">Contact</h2>
                            <div className="mt-3 space-y-2 text-sm text-stone-700">
                                {staffMember.email && <p>{staffMember.email}</p>}
                                {staffMember.phone && <p>{staffMember.phone}</p>}
                                {staffMember.office_location && <p>{staffMember.office_location}</p>}
                                {!staffMember.email && !staffMember.phone && !staffMember.office_location && (
                                    <p className="text-stone-500">Public contact details are not available.</p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="space-y-4">
                            <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                                Staff profile
                            </p>
                            <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                                {staffMember.name}
                            </h1>
                            {staffMember.position && (
                                <p className="text-lg leading-8 text-stone-600">{staffMember.position}</p>
                            )}
                            {staffMember.parent?.slug && (
                                <p className="text-sm text-stone-500">
                                    Reports to{' '}
                                    <Link href={`/${site.locale}/staff/${staffMember.parent.slug}`} className="underline">
                                        {staffMember.parent.name}
                                    </Link>
                                </p>
                            )}
                        </div>

                        <div className="prose prose-stone max-w-none rounded-[2rem] border border-stone-200 bg-white p-8 shadow-sm">
                            {staffMember.bio ? (
                                <div dangerouslySetInnerHTML={{ __html: staffMember.bio }} />
                            ) : (
                                <p>No biography has been published yet.</p>
                            )}
                        </div>

                        {staffMember.children.length > 0 && (
                            <section className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="staff-team-heading">
                                <h2 id="staff-team-heading" className="text-xl font-semibold tracking-tight text-stone-950">
                                    Team and direct reports
                                </h2>
                                <div className="mt-4 grid gap-3">
                                    {staffMember.children.map((child) => (
                                        <div key={child.id} className="rounded-2xl border border-stone-200 px-4 py-3">
                                            {child.slug ? (
                                                <Link href={`/${site.locale}/staff/${child.slug}`} className="font-medium underline">
                                                    {child.name}
                                                </Link>
                                            ) : (
                                                <span className="font-medium">{child.name}</span>
                                            )}
                                            {child.position && (
                                                <p className="mt-1 text-sm text-stone-600">{child.position}</p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </section>
                        )}
                    </div>
                </div>
            </article>
        </PublicLayout>
    );
}
