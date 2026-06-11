import { OperationDetail, OperationListVariant } from '../../types/operation';

export const OPERATION_CARD_CLASS =
    'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900';

export const OPERATION_HERO_AVATAR_CLASS =
    'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-lg font-bold text-white shadow-lg ring-2 ring-white/30 backdrop-blur-sm';

export const OPERATION_PENDING_PANEL_CLASS =
    'overflow-hidden rounded-xl border border-amber-200/90 bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 shadow-md dark:border-amber-900/50 dark:from-amber-950/50 dark:via-orange-950/30 dark:to-yellow-950/20';

export const OPERATION_APPROVE_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-emerald-600 hover:to-green-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-gray-900';

export const OPERATION_COMPLETE_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-sky-600 hover:to-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-gray-900';

export const OPERATION_RESERVE_BTN_CLASS =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-amber-600 hover:to-orange-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-gray-900';

export const OPERATION_PANEL_ICON_CLASS = 'text-amber-600 dark:text-amber-400';

export const OPERATION_LIST_VARIANT_CONFIG: Record<
    OperationListVariant,
    { accent: string; icon: string; subtitleKey: string; statIcon: string; statGradient: string }
> = {
    new: {
        accent: 'from-amber-600 to-orange-700',
        icon: 'bx-cut',
        subtitleKey: 'global.new_operations',
        statIcon: 'bx-time-five',
        statGradient: 'from-amber-500 to-orange-600',
    },
    approved: {
        accent: 'from-emerald-600 to-teal-700',
        icon: 'bx-check-circle',
        subtitleKey: 'global.approved_operations',
        statIcon: 'bx-check-shield',
        statGradient: 'from-emerald-500 to-teal-600',
    },
    reserved: {
        accent: 'from-violet-600 to-purple-700',
        icon: 'bx-calendar-check',
        subtitleKey: 'global.reserved_operations',
        statIcon: 'bx-pause-circle',
        statGradient: 'from-violet-500 to-purple-600',
    },
    completed: {
        accent: 'from-sky-600 to-blue-700',
        icon: 'bx-check-double',
        subtitleKey: 'global.completed_operations',
        statIcon: 'bx-badge-check',
        statGradient: 'from-sky-500 to-blue-600',
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

export function operationPatientLabel(operation: OperationDetail): string {
    return operation.patient?.name ?? `#${operation.id}`;
}

export function operationApprovalLabel(approved: boolean, t: (key: string) => string): string {
    return approved ? t('global.approved') : t('global.operation_not_approved');
}

export function operationDoneLabel(done: boolean, t: (key: string) => string): string {
    return done ? t('global.completed') : t('global.pending');
}

export function operationReservedLabel(reserved: boolean, t: (key: string) => string): string {
    return reserved ? t('global.reserved') : t('global.unreserved');
}
