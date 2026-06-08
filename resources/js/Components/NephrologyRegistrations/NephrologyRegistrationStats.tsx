import { Card } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { NephrologyRegistrationStats as Stats } from '../../types/nephrologyRegistration';

interface NephrologyRegistrationStatsProps {
    stats: Stats;
}

const items: Array<{ key: keyof Stats; labelKey: string; icon: string; color: string }> = [
    { key: 'total', labelKey: 'global.total', icon: 'bx-list-ul', color: 'text-blue-500' },
    { key: 'pending', labelKey: 'global.pending', icon: 'bx-time', color: 'text-amber-500' },
    { key: 'in_progress', labelKey: 'global.in_progress', icon: 'bx-loader-circle', color: 'text-cyan-500' },
    { key: 'completed', labelKey: 'global.completed', icon: 'bx-check-circle', color: 'text-emerald-500' },
    { key: 'cancelled', labelKey: 'global.cancelled', icon: 'bx-x-circle', color: 'text-red-500' },
];

export default function NephrologyRegistrationStats({ stats }: NephrologyRegistrationStatsProps) {
    const { t } = useTranslation();

    return (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            {items.map((item) => (
                <Card key={item.key} className="shadow-sm">
                    <div className="flex items-center gap-3">
                        <div
                            className={`flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 ${item.color}`}
                        >
                            <i className={`bx ${item.icon} text-xl`} />
                        </div>
                        <div>
                            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                                {t(item.labelKey)}
                            </p>
                            <p className="text-xl font-bold text-gray-900 dark:text-white">{stats[item.key]}</p>
                        </div>
                    </div>
                </Card>
            ))}
        </div>
    );
}
