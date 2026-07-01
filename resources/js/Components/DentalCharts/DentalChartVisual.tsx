import {
    LOWER_LEFT_TEETH,
    LOWER_RIGHT_TEETH,
    TOOTH_CONDITION_COLORS,
    TOOTH_CONDITIONS,
    UPPER_LEFT_TEETH,
    UPPER_RIGHT_TEETH,
} from '../../lib/dentalChartOptions';
import { useTranslation } from '../../hooks/useTranslation';
import { DentalChartEntry } from '../../types/dentistRegistration';
import DentalToothButton from './DentalToothButton';

interface DentalChartVisualProps {
    chartEntries: DentalChartEntry[];
    selectedTooth: number | null;
    onToothClick: (toothNumber: number) => void;
}

function ToothRow({
    teeth,
    entriesByTooth,
    selectedTooth,
    onToothClick,
}: {
    teeth: number[];
    entriesByTooth: Record<number, DentalChartEntry>;
    selectedTooth: number | null;
    onToothClick: (toothNumber: number) => void;
}) {
    return (
        <div className="flex flex-wrap items-center justify-center gap-2">
            {teeth.map((toothNumber) => (
                <DentalToothButton
                    key={toothNumber}
                    toothNumber={toothNumber}
                    entry={entriesByTooth[toothNumber] ?? null}
                    isSelected={selectedTooth === toothNumber}
                    onClick={onToothClick}
                />
            ))}
        </div>
    );
}

export default function DentalChartVisual({
    chartEntries,
    selectedTooth,
    onToothClick,
}: DentalChartVisualProps) {
    const { t } = useTranslation();

    const entriesByTooth = chartEntries.reduce<Record<number, DentalChartEntry>>((accumulator, entry) => {
        accumulator[entry.tooth_number] = entry;
        return accumulator;
    }, {});

    return (
        <div className="mx-auto max-w-4xl rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/40 sm:p-6">
            <div className="mb-4 text-center">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">{t('global.dental_chart')}</h3>
                <p className="text-xs text-gray-500 dark:text-gray-400">{t('global.permanent_teeth')}</p>
            </div>

            <div className="space-y-6">
                <div>
                    <p className="mb-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {t('global.upper_jaw')}
                    </p>
                    <ToothRow
                        teeth={UPPER_RIGHT_TEETH}
                        entriesByTooth={entriesByTooth}
                        selectedTooth={selectedTooth}
                        onToothClick={onToothClick}
                    />
                    <div className="my-3" />
                    <ToothRow
                        teeth={UPPER_LEFT_TEETH}
                        entriesByTooth={entriesByTooth}
                        selectedTooth={selectedTooth}
                        onToothClick={onToothClick}
                    />
                </div>

                <div className="border-t border-dashed border-gray-200 dark:border-gray-700" />

                <div>
                    <p className="mb-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {t('global.lower_jaw')}
                    </p>
                    <ToothRow
                        teeth={LOWER_LEFT_TEETH}
                        entriesByTooth={entriesByTooth}
                        selectedTooth={selectedTooth}
                        onToothClick={onToothClick}
                    />
                    <div className="my-3" />
                    <ToothRow
                        teeth={LOWER_RIGHT_TEETH}
                        entriesByTooth={entriesByTooth}
                        selectedTooth={selectedTooth}
                        onToothClick={onToothClick}
                    />
                </div>
            </div>

            <div className="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
                <p className="mb-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                    {t('global.legend')}
                </p>
                <div className="flex flex-wrap justify-center gap-2">
                    {TOOTH_CONDITIONS.slice(0, 6).map((condition) => (
                        <span
                            key={condition}
                            className="rounded-full px-2.5 py-1 text-xs font-medium text-white"
                            style={{ backgroundColor: TOOTH_CONDITION_COLORS[condition] }}
                        >
                            {t(`global.${condition}`)}
                        </span>
                    ))}
                </div>
            </div>
        </div>
    );
}
