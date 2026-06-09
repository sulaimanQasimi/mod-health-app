export const HOSPITALIZATION_MUTED_NOTE_CLASS =
    'rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-5 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800/40 dark:text-gray-400';

export const HOSPITALIZATION_DISCHARGED_PANEL_CLASS =
    'flex items-start gap-3 rounded-xl border border-amber-200/90 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3.5 text-sm text-amber-950 shadow-sm dark:border-amber-800/60 dark:from-amber-950/40 dark:to-orange-950/20 dark:text-amber-100';

export const HOSPITALIZATION_CARD_CLASS =
    'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900';

export const HOSPITALIZATION_FILTER_PANEL_CLASS =
    'rounded-xl border border-gray-200 bg-gradient-to-b from-white to-gray-50/80 p-5 shadow-sm dark:border-gray-700 dark:from-gray-900 dark:to-gray-900/60';

export const HOSPITALIZATION_TABLE_PANEL_CLASS =
    'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900';

export const HOSPITALIZATION_FORM_SECTION_CLASS =
    'rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30';

export const HOSPITALIZATION_AVATAR_CLASS =
    'flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-sm font-semibold text-white shadow-md';

export const HOSPITALIZATION_HERO_AVATAR_CLASS =
    'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-lg font-bold text-white shadow-lg ring-2 ring-white/30 backdrop-blur-sm';

export function patientInitials(name?: string | null): string {
    if (!name?.trim()) {
        return '?';
    }

    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
}

export function dischargeStatusBadgeColor(
    status?: string | null
): 'success' | 'failure' | 'warning' | 'gray' {
    if (status === 'recovered') {
        return 'success';
    }
    if (status === 'died') {
        return 'failure';
    }
    if (status === 'moved') {
        return 'warning';
    }

    return 'gray';
}
