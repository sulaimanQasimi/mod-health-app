import { IcuDischargeFilter } from '../../types/icu';
import { useTranslation } from '../../hooks/useTranslation';

interface IcuDischargeTabsProps {
    value: string;
    onChange: (value: IcuDischargeFilter) => void;
    disabled?: boolean;
}

const TABS: { key: IcuDischargeFilter; labelKey: string; color: string }[] = [
    { key: 'all', labelKey: 'global.all_approved', color: 'blue' },
    { key: 'in_icu', labelKey: 'global.in_icu', color: 'blue' },
    { key: 'discharged', labelKey: 'global.discharged', color: 'blue' },
    { key: 'recovered', labelKey: 'global.recovered', color: 'green' },
    { key: 'died', labelKey: 'global.died', color: 'red' },
    { key: 'moved', labelKey: 'global.moved', color: 'yellow' },
];

function tabClass(isActive: boolean, color: string): string {
    if (!isActive) {
        return 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300';
    }

    const map: Record<string, string> = {
        blue: 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        green: 'border-green-500 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        red: 'border-red-500 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        yellow: 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    };

    return map[color] ?? map.blue;
}

export default function IcuDischargeTabs({ value, onChange, disabled }: IcuDischargeTabsProps) {
    const { t } = useTranslation();
    const current = (value || 'in_icu') as IcuDischargeFilter;

    return (
        <div className="rounded-xl border border-rose-100 bg-rose-50/50 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
            <p className="mb-3 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">
                <i className="bx bx-pie-chart-alt" />
                {t('global.filter_by_discharge')}
            </p>
            <div className="flex flex-wrap gap-2">
                {TABS.map((tab) => (
                    <button
                        key={tab.key}
                        type="button"
                        disabled={disabled}
                        onClick={() => onChange(tab.key)}
                        className={`rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors disabled:opacity-50 ${tabClass(current === tab.key, tab.color)}`}
                    >
                        {t(tab.labelKey)}
                    </button>
                ))}
            </div>
        </div>
    );
}
