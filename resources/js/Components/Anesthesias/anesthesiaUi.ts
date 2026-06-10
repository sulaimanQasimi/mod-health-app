import { AnesthesiaDetail, AnesthesiaListVariant } from '../../types/anesthesia';

export const ANESTHESIA_CARD_CLASS =
    'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900';

export const ANESTHESIA_HERO_AVATAR_CLASS =
    'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-lg font-bold text-white shadow-lg ring-2 ring-white/30 backdrop-blur-sm';

export const ANESTHESIA_PENDING_PANEL_CLASS =
    'overflow-hidden rounded-xl border border-violet-200/90 bg-gradient-to-br from-violet-50 via-indigo-50 to-purple-50 shadow-md dark:border-violet-900/50 dark:from-violet-950/50 dark:via-indigo-950/30 dark:to-purple-950/20';

export const ANESTHESIA_APPROVE_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-emerald-600 hover:to-green-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-gray-900';

export const ANESTHESIA_REJECT_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-rose-600 hover:to-red-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-gray-900';

export const ANESTHESIA_PANEL_ICON_CLASS = 'text-violet-600 dark:text-violet-400';

export const ANESTHESIA_PANEL_ICON_BG_CLASS = 'bg-violet-50 dark:bg-violet-950/30';

export const ANESTHESIA_APPLY_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-violet-500 to-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:from-violet-600 hover:to-purple-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60';

export const ANESTHESIA_LIST_VARIANT_CONFIG: Record<
    AnesthesiaListVariant,
    { accent: string; icon: string; subtitleKey: string; statIcon: string; statGradient: string }
> = {
    new: {
        accent: 'from-sky-600 to-blue-700',
        icon: 'bx-time-five',
        subtitleKey: 'global.new_anesthesias',
        statIcon: 'bx-time-five',
        statGradient: 'from-sky-500 to-blue-600',
    },
    approved: {
        accent: 'from-emerald-600 to-teal-700',
        icon: 'bx-check-circle',
        subtitleKey: 'global.approved_anesthesias',
        statIcon: 'bx-check-shield',
        statGradient: 'from-emerald-500 to-teal-600',
    },
    rejected: {
        accent: 'from-rose-600 to-red-700',
        icon: 'bx-x-circle',
        subtitleKey: 'global.rejected_anesthesias',
        statIcon: 'bx-block',
        statGradient: 'from-rose-500 to-red-600',
    },
};

export function anesthesiaStatusBadgeColor(status: string): 'info' | 'success' | 'failure' | 'gray' {
    if (status === 'approved') return 'success';
    if (status === 'rejected') return 'failure';
    if (status === 'new') return 'info';
    return 'gray';
}

export function anesthesiaStatusLabel(status: string, t: (key: string) => string): string {
    if (status === 'approved') return t('global.approved');
    if (status === 'rejected') return t('global.rejected');
    if (status === 'new') return t('global.new');
    return status;
}

export function anesthesiaTypeLabel(type: string | null | undefined, t: (key: string) => string): string {
    if (!type) return '—';
    if (type === 'local') return t('global.local');
    if (type === 'spinal') return t('global.spinal');
    if (type === 'general') return t('global.general');
    return type;
}

export function anesthesiaPatientLabel(anesthesia: AnesthesiaDetail): string {
    return anesthesia.patient?.name ?? `#${anesthesia.id}`;
}

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
