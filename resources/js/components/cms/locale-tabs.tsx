import { useState } from 'react';
import { cn } from '@/lib/utils';
import { SUPPORTED_LOCALES, type SupportedLocale } from '@/lib/locales';

const LOCALE_LABELS: Record<SupportedLocale, string> = {
    en: 'English',
    tj: 'Тоҷикӣ',
    ru: 'Русский',
};

type Props = {
    errors: Record<string, string | undefined>;
    children: (locale: SupportedLocale) => React.ReactNode;
};

export function LocaleTabs({ errors, children }: Props) {
    const [activeLocale, setActiveLocale] = useState<SupportedLocale>(SUPPORTED_LOCALES[0]);

    const hasErrors = (locale: SupportedLocale) =>
        Object.keys(errors).some((key) => key.startsWith(`translations.${locale}.`));

    return (
        <div className="rounded-xl border">
            <div className="flex border-b px-1">
                {SUPPORTED_LOCALES.map((locale) => (
                    <button
                        key={locale}
                        type="button"
                        onClick={() => setActiveLocale(locale)}
                        className={cn(
                            'relative flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors',
                            activeLocale === locale
                                ? 'text-foreground after:absolute after:inset-x-0 after:bottom-0 after:h-0.5 after:bg-foreground'
                                : 'text-muted-foreground hover:text-foreground',
                        )}
                    >
                        <span className="font-mono text-xs font-semibold uppercase tracking-widest">
                            {locale}
                        </span>
                        <span
                            className={cn(
                                'hidden text-xs sm:inline',
                                activeLocale === locale
                                    ? 'text-muted-foreground'
                                    : 'text-muted-foreground/60',
                            )}
                        >
                            {LOCALE_LABELS[locale]}
                        </span>
                        {hasErrors(locale) && (
                            <span className="size-1.5 rounded-full bg-destructive" aria-label="Has errors" />
                        )}
                    </button>
                ))}
            </div>

            {SUPPORTED_LOCALES.map((locale) => (
                <div
                    key={locale}
                    className={cn('grid gap-6 p-6', activeLocale !== locale && 'hidden')}
                >
                    {children(locale)}
                </div>
            ))}
        </div>
    );
}
