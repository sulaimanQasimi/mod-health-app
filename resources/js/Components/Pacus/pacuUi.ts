import {
    CARE_UNIT_APPLY_BTN_BASE,
    CARE_UNIT_COMPLETE_BTN_BASE,
    CareUnitTheme,
} from '../CareUnits/careUnitUi';
import { PacuListVariant } from '../../types/pacu';

export const PACU_THEME: CareUnitTheme = {
    accent: 'from-cyan-600 to-teal-700',
    icon: 'bx-tv',
    panelIconClass: 'text-cyan-600 dark:text-cyan-400',
    panelIconBgClass: 'bg-cyan-50 dark:bg-cyan-950/30',
    applyBtnClass: `${CARE_UNIT_APPLY_BTN_BASE} bg-gradient-to-r from-cyan-500 to-teal-600 hover:from-cyan-600 hover:to-teal-700`,
    listSummaryBadge:
        'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300',
    completeBtnClass: `${CARE_UNIT_COMPLETE_BTN_BASE} bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 focus:ring-emerald-400`,
};

export const PACU_LIST_VARIANT_CONFIG: Record<
    PacuListVariant,
    { accent: string; icon: string; subtitleKey: string }
> = {
    new: {
        accent: 'from-sky-600 to-blue-700',
        icon: 'bx-plus-circle',
        subtitleKey: 'global.new_pacus',
    },
    completed: {
        accent: 'from-emerald-600 to-teal-700',
        icon: 'bx-check-circle',
        subtitleKey: 'global.completed_pacus',
    },
};

export const PACU_CARD_CLASS =
    'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900';
