import { Link } from '@inertiajs/react';
import { useTranslation } from '../../hooks/useTranslation';

export type MedicineNavKey = 'medicines' | 'types' | 'formulas' | 'countries' | 'gramMls';

export type MedicineNavUrls = Record<MedicineNavKey, string>;

const TABS: Array<{ key: MedicineNavKey; labelKey: string; icon: string }> = [
    { key: 'medicines', labelKey: 'global.medicines', icon: 'bx-capsule' },
    { key: 'types', labelKey: 'global.types', icon: 'bx-category' },
    { key: 'formulas', labelKey: 'global.formulas', icon: 'bx-test-tube' },
    { key: 'countries', labelKey: 'global.countries', icon: 'bx-globe' },
    { key: 'gramMls', labelKey: 'global.medicine_gram_ml', icon: 'bx-injection' },
];

export default function MedicineSubNav({
    active,
    urls,
}: {
    active: MedicineNavKey;
    urls: MedicineNavUrls;
}) {
    const { t } = useTranslation();

    return (
        <div className="mb-2 flex flex-wrap gap-1 rounded-md border border-teal-100 bg-teal-50/60 p-1 dark:border-teal-900/40 dark:bg-teal-900/20">
            {TABS.map((tab) => {
                const isActive = active === tab.key;
                return (
                    <Link
                        key={tab.key}
                        href={urls[tab.key]}
                        preserveScroll
                        className={`inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-semibold ${
                            isActive
                                ? 'bg-teal-600 text-white'
                                : 'text-teal-800 hover:bg-white dark:text-teal-100 dark:hover:bg-teal-900/40'
                        }`}
                    >
                        <i className={`bx ${tab.icon} text-sm`} />
                        {t(tab.labelKey)}
                    </Link>
                );
            })}
        </div>
    );
}
