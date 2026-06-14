import { usePage } from '@inertiajs/react';
import { SharedPageProps } from '../types';

function resolveTranslation(source: Record<string, unknown>, key: string): string {
    const parts = key.split('.');
    let value: unknown = source;

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
    const { translations, activityLogTranslations, locale, direction } = usePage<SharedPageProps>().props;

    const t = (key: string): string => {
        if (key.startsWith('activity_log.')) {
            return resolveTranslation(activityLogTranslations ?? {}, key.replace(/^activity_log\./, ''));
        }

        if (!key.startsWith('global.')) {
            return key;
        }

        return resolveTranslation(translations, key.replace(/^global\./, ''));
    };

    return { t, locale, direction };
}
