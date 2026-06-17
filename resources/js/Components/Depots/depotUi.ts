import type { DepotActiveOption, DepotSourceOption } from '../../types/depot';

export const DEPOT_CARD_CLASS =
    'rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800';

export const DEPOT_PRIMARY_BTN_CLASS =
    'inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:opacity-95 disabled:opacity-60';

export const DEPOT_SUCCESS_BTN_CLASS =
    'inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:opacity-95 disabled:opacity-60';

export const DEPOT_PHARMACY_BTN_CLASS =
    'inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:opacity-95 disabled:opacity-60';

export const DEPOT_SECTION_CLASS =
    'rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-gray-700/60 dark:bg-gray-800/30';

export function depotStatusBadgeColor(status: string): 'info' | 'success' | 'failure' | 'warning' | 'gray' {
    if (status === 'completed' || status === 'fulfilled' || status === 'approved') return 'success';
    if (status === 'cancelled' || status === 'rejected') return 'failure';
    if (status === 'pending' || status === 'draft') return 'warning';
    if (status === 'stock_out' || status === 'depot_to_pharmacy') return 'failure';
    return 'info';
}

export function depotTypeLabel(type: string, t: (key: string) => string): string {
    const key = `global.depot.${type}` as 'global.depot.transactions';
    const translated = t(key);
    if (translated !== key) return translated;
    return type.replace(/_/g, ' ');
}

export function depotRequestStatusLabel(status: string, t: (key: string) => string): string {
    const key = `global.depot.request_status_${status}`;
    const translated = t(key);
    if (translated !== key) return translated;
    return status.replace(/_/g, ' ');
}

export type DepotStockLevel = 'healthy' | 'low_stock' | 'out_of_stock';

export const DEPOT_LOW_STOCK_THRESHOLD = 10;

export function depotStockLevel(quantity: number): DepotStockLevel {
    if (quantity <= 0) return 'out_of_stock';
    if (quantity <= DEPOT_LOW_STOCK_THRESHOLD) return 'low_stock';
    return 'healthy';
}

export function depotStockLevelBadgeColor(level: DepotStockLevel): 'success' | 'warning' | 'failure' {
    if (level === 'healthy') return 'success';
    if (level === 'low_stock') return 'warning';
    return 'failure';
}

export function depotStockLevelLabel(level: DepotStockLevel, t: (key: string) => string): string {
    if (level === 'healthy') return t('global.in_stock');
    if (level === 'low_stock') return t('global.low_stock');
    return t('global.out_of_stock');
}

export function depotStockBarColor(level: DepotStockLevel): string {
    if (level === 'healthy') return 'bg-emerald-500';
    if (level === 'low_stock') return 'bg-amber-500';
    return 'bg-red-500';
}

export function resolveDepotRequestSourceDepot(
    destinationType: 'depot' | 'pharmacy',
    requestingDepotId: string,
    pharmacyId: string,
    activeDepots: DepotActiveOption[],
    fallbackSourceDepot?: DepotSourceOption | null,
): DepotSourceOption | null {
    if (destinationType === 'pharmacy' && pharmacyId) {
        const linkedDepot = activeDepots.find((depot) => depot.pharmacy_id === Number(pharmacyId));
        return linkedDepot ? { id: linkedDepot.id, name: linkedDepot.name } : null;
    }

    if (destinationType === 'depot' && requestingDepotId) {
        const requestingDepot = activeDepots.find((depot) => depot.id === Number(requestingDepotId));
        if (requestingDepot?.parent_depot_id) {
            const parentDepot = activeDepots.find((depot) => depot.id === requestingDepot.parent_depot_id);
            if (parentDepot) {
                return { id: parentDepot.id, name: parentDepot.name };
            }
        }
    }

    return fallbackSourceDepot ?? null;
}

export interface DepotRequestContextInfo {
    branch_name: string | null;
    department_name: string | null;
    pharmacy_depot_label: string | null;
    request_user_name: string | null;
}

export function resolveDepotRequestContext(
    destinationType: 'depot' | 'pharmacy',
    requestingDepotId: string,
    pharmacyId: string,
    activeDepots: DepotActiveOption[],
    pharmacies: Array<{ id: number; name: string }>,
    requestUserName: string | null,
): DepotRequestContextInfo {
    if (destinationType === 'pharmacy' && pharmacyId) {
        const linkedDepot = activeDepots.find((depot) => depot.pharmacy_id === Number(pharmacyId));
        const pharmacy = pharmacies.find((item) => item.id === Number(pharmacyId));

        return {
            branch_name: linkedDepot?.branch_name ?? null,
            department_name: linkedDepot?.department_name ?? null,
            pharmacy_depot_label: pharmacy?.name ?? linkedDepot?.name ?? null,
            request_user_name: requestUserName,
        };
    }

    if (destinationType === 'depot' && requestingDepotId) {
        const depot = activeDepots.find((item) => item.id === Number(requestingDepotId));

        return {
            branch_name: depot?.branch_name ?? null,
            department_name: depot?.department_name ?? null,
            pharmacy_depot_label: depot?.name ?? null,
            request_user_name: requestUserName,
        };
    }

    return {
        branch_name: null,
        department_name: null,
        pharmacy_depot_label: null,
        request_user_name: requestUserName,
    };
}
