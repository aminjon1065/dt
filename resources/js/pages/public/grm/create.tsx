import { Form, Link } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/PublicGrmController';
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

export default function PublicGrmCreate({
    locale,
    site,
    navigation,
    seo,
    structuredData,
}: {
    locale: string;
    site: SiteData;
    navigation: NavigationItem[];
    seo?: SeoData;
    structuredData?: Array<Record<string, unknown>>;
}) {
    return (
        <PublicLayout
            title="GRM"
            site={site}
            navigation={navigation}
            seo={seo}
            structuredData={structuredData}
        >
            <section className="mx-auto max-w-4xl px-4 py-12 md:py-20">
                <div className="max-w-3xl space-y-4">
                    <p className="text-sm font-medium uppercase tracking-[0.24em] text-stone-500">
                        Feedback
                    </p>
                    <h1 className="text-4xl font-semibold tracking-tight text-stone-950 md:text-5xl">
                        Submit a grievance or feedback
                    </h1>
                    <p className="text-lg leading-8 text-stone-600">
                        Use this form to submit a grievance, complaint, or
                        general feedback. Required fields are minimal so the
                        process stays accessible on low-bandwidth connections.
                    </p>
                </div>

                <div className="mt-10 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm md:p-8">
                    <Form
                        {...store.form({ locale })}
                        options={{ preserveScroll: true }}
                        className="space-y-6"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-5 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Name</Label>
                                        <Input id="name" name="name" />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="subject">Subject</Label>
                                        <Input id="subject" name="subject" />
                                        <InputError message={errors.subject} />
                                    </div>
                                </div>

                                <div className="grid gap-5 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="email">Email</Label>
                                        <Input
                                            id="email"
                                            name="email"
                                            type="email"
                                        />
                                        <InputError message={errors.email} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="phone">Phone</Label>
                                        <Input id="phone" name="phone" />
                                        <InputError message={errors.phone} />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="message">Message</Label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 min-h-40 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]"
                                    />
                                    <InputError message={errors.message} />
                                </div>

                                <div className="flex flex-col gap-4 border-t border-stone-200 pt-4 text-sm text-stone-600 md:flex-row md:items-center md:justify-between">
                                    <p>
                                        By submitting this form, you allow the
                                        portal team to review your message and
                                        contact you if needed.
                                    </p>
                                    <Button disabled={processing}>
                                        Submit grievance
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <div className="mt-6 text-sm text-stone-600">
                    <span>Need general information instead? </span>
                    <Link
                        href={`/${site.locale}`}
                        className="font-medium text-stone-900 underline"
                    >
                        Return to the homepage
                    </Link>
                    .
                </div>
            </section>
        </PublicLayout>
    );
}
