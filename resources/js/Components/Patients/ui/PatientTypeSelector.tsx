import { useTranslation } from '../../../hooks/useTranslation';
import { PatientType } from '../../../types/patient';

interface PatientTypeSelectorProps {
    value: PatientType;
    onChange: (type: PatientType) => void;
}

const patientTypes = [
    {
        type: '0' as PatientType,
        icon: 'bx-shield-quarter',
        labelKey: 'global.mod',
        descKey: 'global.military',
    },
    {
        type: '1' as PatientType,
        icon: 'bx-buildings',
        labelKey: 'global.recipient',
        descKey: 'global.referred_by',
    },
    {
        type: '2' as PatientType,
        icon: 'bx-group',
        labelKey: 'global.family',
        descKey: 'global.referred_person',
    },
];

export default function PatientTypeSelector({ value, onChange }: PatientTypeSelectorProps) {
    const { t } = useTranslation();

    return (
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
            {patientTypes.map((item) => {
                const selected = value === item.type;

                return (
                    <button
                        key={item.type}
                        type="button"
                        onClick={() => onChange(item.type)}
                        className={`flex items-start gap-3 rounded-lg border px-4 py-3 text-start transition-colors ${
                            selected
                                ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600/20 dark:border-blue-500 dark:bg-blue-950/30 dark:ring-blue-500/30'
                                : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800/50 dark:hover:border-gray-500 dark:hover:bg-gray-800'
                        }`}
                    >
                        <span
                            className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${
                                selected
                                    ? 'bg-blue-600 text-white dark:bg-blue-500'
                                    : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                            }`}
                        >
                            <i className={`bx ${item.icon} text-lg`} />
                        </span>
                        <span className="min-w-0">
                            <span className="block text-sm font-semibold text-gray-900 dark:text-white">
                                {t(item.labelKey)}
                            </span>
                            <span className="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
                                {t(item.descKey)}
                            </span>
                        </span>
                    </button>
                );
            })}
        </div>
    );
}
