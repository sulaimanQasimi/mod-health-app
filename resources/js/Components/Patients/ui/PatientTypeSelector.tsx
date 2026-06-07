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
        gradient: 'from-blue-500 to-indigo-600',
        ring: 'ring-blue-500',
        bg: 'bg-blue-50 dark:bg-blue-950/30',
    },
    {
        type: '1' as PatientType,
        icon: 'bx-buildings',
        labelKey: 'global.recipient',
        descKey: 'global.referred_by',
        gradient: 'from-violet-500 to-purple-600',
        ring: 'ring-violet-500',
        bg: 'bg-violet-50 dark:bg-violet-950/30',
    },
    {
        type: '2' as PatientType,
        icon: 'bx-group',
        labelKey: 'global.family',
        descKey: 'global.referred_person',
        gradient: 'from-emerald-500 to-teal-600',
        ring: 'ring-emerald-500',
        bg: 'bg-emerald-50 dark:bg-emerald-950/30',
    },
];

export default function PatientTypeSelector({ value, onChange }: PatientTypeSelectorProps) {
    const { t } = useTranslation();

    return (
        <div className="grid gap-4 sm:grid-cols-3">
            {patientTypes.map((item) => {
                const selected = value === item.type;

                return (
                    <button
                        key={item.type}
                        type="button"
                        onClick={() => onChange(item.type)}
                        className={`group relative overflow-hidden rounded-2xl border-2 p-5 text-start transition-all duration-200 ${
                            selected
                                ? `border-transparent ${item.bg} shadow-lg ring-2 ${item.ring} ring-offset-2 dark:ring-offset-gray-900`
                                : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600'
                        }`}
                    >
                        <div
                            className={`mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${item.gradient} text-white shadow-md transition-transform group-hover:scale-105`}
                        >
                            <i className={`bx ${item.icon} text-2xl`} />
                        </div>
                        <h3 className="text-base font-bold text-gray-900 dark:text-white">{t(item.labelKey)}</h3>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{t(item.descKey)}</p>
                        {selected && (
                            <div className="absolute end-4 top-4 flex h-6 w-6 items-center justify-center rounded-full bg-white shadow dark:bg-gray-900">
                                <i className="bx bx-check text-lg text-green-500" />
                            </div>
                        )}
                    </button>
                );
            })}
        </div>
    );
}
