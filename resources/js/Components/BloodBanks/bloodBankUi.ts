import { BloodRequestListVariant } from '../../types/bloodBank';

export const BLOOD_BANK_PANEL_ICON_CLASS = 'text-rose-600 dark:text-rose-400';

export const BLOOD_REQUEST_LIST_VARIANT_CONFIG: Record<
    BloodRequestListVariant,
    { accent: string; icon: string; subtitleKey: string; statIcon: string; statGradient: string }
> = {
    new: {
        accent: 'from-sky-600 to-blue-700',
        icon: 'bx-donate-blood',
        subtitleKey: 'global.new_blood_requests',
        statIcon: 'bx-time-five',
        statGradient: 'from-sky-500 to-blue-600',
    },
    approved: {
        accent: 'from-emerald-600 to-teal-700',
        icon: 'bx-check-circle',
        subtitleKey: 'global.approved_blood_requests',
        statIcon: 'bx-check-shield',
        statGradient: 'from-emerald-500 to-teal-600',
    },
    rejected: {
        accent: 'from-rose-600 to-red-700',
        icon: 'bx-x-circle',
        subtitleKey: 'global.rejected_blood_requests',
        statIcon: 'bx-block',
        statGradient: 'from-rose-500 to-red-600',
    },
    delivered: {
        accent: 'from-violet-600 to-purple-700',
        icon: 'bx-package',
        subtitleKey: 'global.delivered_blood_requests',
        statIcon: 'bx-badge-check',
        statGradient: 'from-violet-500 to-purple-600',
    },
};

export function bloodGroupLabel(group: string | null): string {
    return group ?? '—';
}

export function bloodRhLabel(rh: string | null): string {
    if (rh === '+') return 'Rh+';
    if (rh === '-') return 'Rh−';
    return rh ?? '—';
}

export function bloodStatusBadgeColor(
    status: string,
): 'info' | 'success' | 'warning' | 'failure' | 'purple' | 'gray' {
    switch (status) {
        case 'approved':
            return 'success';
        case 'delivered':
            return 'purple';
        case 'rejected':
            return 'failure';
        case 'new':
            return 'warning';
        default:
            return 'gray';
    }
}

export function bloodUnitStatusBadgeColor(
    status: string,
): 'info' | 'success' | 'warning' | 'failure' | 'purple' | 'gray' {
    switch (status) {
        case 'available':
            return 'success';
        case 'reserved':
            return 'info';
        case 'issued':
            return 'purple';
        case 'quarantine':
            return 'warning';
        case 'discarded':
        case 'expired':
            return 'failure';
        default:
            return 'gray';
    }
}

export const BLOOD_MOVEMENT_TYPES = ['received', 'issued', 'adjusted', 'discarded', 'transferred'] as const;
