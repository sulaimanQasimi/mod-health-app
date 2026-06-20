import { useTranslation } from '../../../hooks/useTranslation';
import { PatientType } from '../../../types/patient';

interface PatientTypeSelectorProps {
    value: PatientType;
    onChange: (type: PatientType) => void;
}

const patientTypes: { type: PatientType; labelKey: string }[] = [
    { type: '0', labelKey: 'global.mod' },
    { type: '1', labelKey: 'global.recipient' },
    { type: '3', labelKey: 'global.extraordinary' },
    { type: '2', labelKey: 'global.family' },
];

export default function PatientTypeSelector({ value, onChange }: PatientTypeSelectorProps) {
    const { t } = useTranslation();

    return (
        <div className="nav-align-top mb-6">
            <ul className="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4 sm:gap-3" role="tablist">
                {patientTypes.map((item) => {
                    const selected = value === item.type;

                    return (
                        <li key={item.type} role="presentation" className="min-w-0">
                            <button
                                type="button"
                                role="tab"
                                aria-selected={selected}
                                onClick={() => onChange(item.type)}
                                className={`flex w-full items-center justify-center rounded-xl px-4 py-3.5 text-center text-lg font-semibold transition-all sm:py-4 sm:text-xl ${
                                    selected
                                        ? 'bg-blue-600 text-white shadow-md ring-2 ring-blue-600/25 dark:bg-blue-500 dark:ring-blue-500/30'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700/70 dark:text-gray-200 dark:hover:bg-gray-700'
                                }`}
                            >
                                {t(item.labelKey)}
                            </button>
                        </li>
                    );
                })}
            </ul>
        </div>
    );
}
