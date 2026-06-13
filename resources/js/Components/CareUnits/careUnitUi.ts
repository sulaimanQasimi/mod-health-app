export interface CareUnitTheme {
    accent: string;
    icon: string;
    panelIconClass: string;
    panelIconBgClass: string;
    applyBtnClass: string;
    listSummaryBadge: string;
    completeBtnClass: string;
}

export const CARE_UNIT_CARD_CLASS =
    'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900';

export const CARE_UNIT_APPLY_BTN_BASE =
    'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60';

export const CARE_UNIT_COMPLETE_BTN_BASE =
    'inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-gray-900';
