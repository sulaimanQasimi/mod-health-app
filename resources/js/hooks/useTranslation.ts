import { usePage } from '@inertiajs/react';
import { SharedPageProps } from '../types';

function resolveTranslation(translations: Record<string, unknown>, key: string): string {
    if (!key.startsWith('global.')) {
        return key;
    }

    const parts = key.replace(/^global\./, '').split('.');
    let value: unknown = translations;

    for (const part of parts) {
        if (value && typeof value === 'object' && part in (value as Record<string, unknown>)) {
            value = (value as Record<string, unknown>)[part];
        } else {
            return key;
        }
    }

    return typeof value === 'string' ? value : key;
}

export function useTranslation() {
    const { translations, locale, direction } = usePage<SharedPageProps>().props;

    const t = (key: string): string => resolveTranslation(translations, key);

    return { t, locale, direction };
}
