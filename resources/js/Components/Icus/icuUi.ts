import { IcuDetail, IcuListVariant } from '../../types/icu';

export const ICU_CARD_CLASS =
    'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900';

export const ICU_HERO_AVATAR_CLASS =
    'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-lg font-bold text-white shadow-lg ring-2 ring-white/30 backdrop-blur-sm';

export const ICU_PENDING_PANEL_CLASS =
    'overflow-hidden rounded-xl border border-rose-200/90 bg-gradient-to-br from-rose-50 via-orange-50 to-amber-50 shadow-md dark:border-rose-900/50 dark:from-rose-950/50 dark:via-orange-950/30 dark:to-amber-950/20';

export const ICU_APPROVE_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-emerald-600 hover:to-green-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-gray-900';

export const ICU_REJECT_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-rose-600 hover:to-red-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-gray-900';

export const ICU_PRINT_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:from-violet-600 hover:to-purple-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900';

export const ICU_PANEL_ICON_CLASS = 'text-rose-600 dark:text-rose-400';

export const ICU_PANEL_ICON_BG_CLASS = 'bg-rose-50 dark:bg-rose-950/30';

export const ICU_APPLY_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-rose-500 to-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:from-rose-600 hover:to-red-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60';

export const ICU_LIST_VARIANT_CONFIG: Record<
    IcuListVariant,
    { accent: string; icon: string; subtitleKey: string; statIcon: string; statGradient: string }
> = {
    new: {
        accent: 'from-sky-600 to-blue-700',
        icon: 'bx-plus-circle',
        subtitleKey: 'global.new_icus',
        statIcon: 'bx-time-five',
        statGradient: 'from-sky-500 to-blue-600',
    },
    approved: {
        accent: 'from-emerald-600 to-teal-700',
        icon: 'bx-check-circle',
        subtitleKey: 'global.approved_icus',
        statIcon: 'bx-pulse',
        statGradient: 'from-emerald-500 to-teal-600',
    },
    rejected: {
        accent: 'from-rose-600 to-red-700',
        icon: 'bx-x-circle',
        subtitleKey: 'global.rejected_icus',
        statIcon: 'bx-block',
        statGradient: 'from-rose-500 to-red-600',
    },
};

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

export function icuStatusLabel(icu: IcuDetail, t: (key: string) => string): string {
    if (icu.status === 'new') return t('global.new_icus');
    if (icu.status === 'rejected') return t('global.rejected_icus');
    if (icu.is_discharged) {
        if (icu.discharge_status === 'recovered') return t('global.recovered');
        if (icu.discharge_status === 'died') return t('global.died');
        if (icu.discharge_status === 'moved') return t('global.moved');
        return t('global.discharged');
    }
    return t('global.in_icu');
}

export function icuStatusBadgeColor(
    icu: IcuDetail
): 'info' | 'success' | 'failure' | 'warning' | 'gray' {
    if (icu.status === 'new') return 'info';
    if (icu.status === 'rejected') return 'failure';
    if (icu.is_discharged) {
        if (icu.discharge_status === 'recovered') return 'success';
        if (icu.discharge_status === 'died') return 'failure';
        if (icu.discharge_status === 'moved') return 'warning';
        return 'gray';
    }
    return 'success';
}
