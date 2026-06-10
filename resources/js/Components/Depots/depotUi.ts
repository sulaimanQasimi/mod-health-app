export const DEPOT_CARD_CLASS =
    'rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800';

export const DEPOT_PRIMARY_BTN_CLASS =
    'inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:opacity-95 disabled:opacity-60';

export const DEPOT_SUCCESS_BTN_CLASS =
    'inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:opacity-95 disabled:opacity-60';

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
