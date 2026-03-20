export const SUPPORTED_LOCALES = ['en', 'tj', 'ru'] as const;

export type SupportedLocale = (typeof SUPPORTED_LOCALES)[number];

/** Type for Wayfinder .form() return value spread onto <Form> component */
export type WayfinderFormAction = {
    method: 'get' | 'post' | 'put' | 'patch' | 'delete';
    action: string;
};
