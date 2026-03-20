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

type StaffMemberListItem = {
    id: number;
    name: string | null;
    slug: string | null;
    position?: string | null;
    email?: string | null;
    phone?: string | null;
    office_location?: string | null;
    photo_url?: string | null;
    children: Array<{
        id: number;
        name: string | null;
        slug: string | null;
        position?: string | null;
    }>;
};

export default function PublicStaffIndex({
    site,
    navigation,
    seo,
    staffMembers,
}: {
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    staffMembers: StaffMemberListItem[];
}) {
    return (
        <PublicLayout title="Staff directory" site={site} navigation={navigation} seo={seo}>
            <section className="mx-auto max-w-6xl px-4 py-12 md:py-20">
                <div className="max-w-3xl space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Directory
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        Staff directory
                    </h1>
                    <p className="text-lg leading-8 text-stone-600">
                        Public staff profiles with contacts, responsibilities, and organizational hierarchy.
                    </p>
                </div>

                <div className="mt-10 grid gap-6 md:grid-cols-2">
                    {staffMembers.map((staffMember) => (
                        <article key={staffMember.id} className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
                            <div className="flex gap-5">
                                <div className="size-20 shrink-0 overflow-hidden rounded-3xl bg-stone-100">
                                    {staffMember.photo_url ? (
                                        <img
                                            src={staffMember.photo_url}
                                            alt={staffMember.name ?? 'Staff photo'}
                                            loading="lazy"
                                            decoding="async"
                                            className="h-full w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-full items-center justify-center text-sm text-stone-500">
                                            {staffMember.name?.slice(0, 1) ?? '?'}
                                        </div>
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <h2 className="text-2xl font-semibold tracking-tight text-stone-950">
                                        {staffMember.name}
                                    </h2>
                                    {staffMember.position && (
                                        <p className="mt-1 text-sm text-stone-600">{staffMember.position}</p>
                                    )}
                                    <div className="mt-3 space-y-1 text-sm text-stone-600">
                                        {staffMember.email && <p>{staffMember.email}</p>}
                                        {staffMember.phone && <p>{staffMember.phone}</p>}
                                        {staffMember.office_location && <p>{staffMember.office_location}</p>}
                                    </div>
                                </div>
                            </div>

                            {staffMember.children.length > 0 && (
                                <div className="mt-5 rounded-3xl bg-stone-50 p-4">
                                    <p className="text-xs uppercase tracking-[0.18em] text-stone-500">
                                        Direct reports
                                    </p>
                                    <ul className="mt-3 space-y-2">
                                        {staffMember.children.map((child) => (
                                            <li key={child.id} className="text-sm text-stone-700">
                                                {child.slug ? (
                                                    <Link href={`/${site.locale}/staff/${child.slug}`} className="font-medium underline">
                                                        {child.name}
                                                    </Link>
                                                ) : (
                                                    <span className="font-medium">{child.name}</span>
                                                )}{' '}
                                                {child.position && <span className="text-stone-500">· {child.position}</span>}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            {staffMember.slug && (
                                <div className="mt-5">
                                    <Link href={`/${site.locale}/staff/${staffMember.slug}`} className="text-sm font-medium text-stone-900 underline">
                                        View full profile
                                    </Link>
                                </div>
                            )}
                        </article>
                    ))}

                    {staffMembers.length === 0 && (
                        <div className="rounded-[2rem] border border-dashed border-stone-300 bg-white p-10 text-center text-sm text-stone-500">
                            No staff profiles published yet.
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
