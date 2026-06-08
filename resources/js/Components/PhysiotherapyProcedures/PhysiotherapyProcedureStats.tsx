import { Card } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { PhysiotherapyProcedureStats as Stats } from '../../types/physiotherapyProcedure';

interface PhysiotherapyProcedureStatsProps {
    stats: Stats;
}

const STAT_ITEMS = [
    { key: 'total', labelKey: 'global.total_procedures', icon: 'bx-health', accent: 'from-cyan-500 to-blue-600' },
    { key: 'pending', labelKey: 'global.pending', icon: 'bx-time', accent: 'from-amber-500 to-orange-500' },
    { key: 'in_progress', labelKey: 'global.in_progress', icon: 'bx-loader-circle', accent: 'from-sky-500 to-indigo-500' },
    { key: 'completed', labelKey: 'global.completed', icon: 'bx-check-circle', accent: 'from-emerald-500 to-green-600' },
] as const;

export default function PhysiotherapyProcedureStats({ stats }: PhysiotherapyProcedureStatsProps) {
    const { t } = useTranslation();

    return (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {STAT_ITEMS.map((item) => (
                <Card key={item.key} className="overflow-hidden shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-2xl font-bold text-gray-900 dark:text-white">
                                {stats[item.key as keyof Stats]}
                            </p>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{t(item.labelKey)}</p>
                        </div>
                        <div
                            className={`flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${item.accent} text-white shadow`}
                        >
                            <i className={`bx ${item.icon} text-2xl`} />
                        </div>
                    </div>
                </Card>
            ))}
        </div>
    );
}
