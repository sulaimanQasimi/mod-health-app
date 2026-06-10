import { Label } from 'flowbite-react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotFormData } from '../../types/depot';

export type DepotItemKind = 'medicine' | 'tool';

type DepotItemKindFieldProps = {
    kind: DepotItemKind;
    onKindChange: (kind: DepotItemKind) => void;
    medicineId: string;
    toolId: string;
    onMedicineChange: (value: string) => void;
    onToolChange: (value: string) => void;
    formData: DepotFormData;
    medicineError?: string;
    toolError?: string;
};

export default function DepotItemKindField({
    kind,
    onKindChange,
    medicineId,
    toolId,
    onMedicineChange,
    onToolChange,
    formData,
    medicineError,
    toolError,
}: DepotItemKindFieldProps) {
    const { t } = useTranslation();
    const itemError = kind === 'medicine' ? medicineError : toolError;

    return (
        <div className="md:col-span-2">
            <Label>{t('global.type')}</Label>
            <div className="mt-1 inline-flex rounded-xl border border-gray-200 bg-gray-50 p-1 dark:border-gray-600 dark:bg-gray-900/40">
                <button
                    type="button"
                    onClick={() => onKindChange('medicine')}
                    className={`rounded-lg px-4 py-2 text-sm font-medium transition-all ${
                        kind === 'medicine'
                            ? 'bg-white text-emerald-700 shadow-sm dark:bg-gray-800 dark:text-emerald-300'
                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
                    }`}
                >
                    {t('global.medicine')}
                </button>
                <button
                    type="button"
                    onClick={() => onKindChange('tool')}
                    className={`rounded-lg px-4 py-2 text-sm font-medium transition-all ${
                        kind === 'tool'
                            ? 'bg-white text-emerald-700 shadow-sm dark:bg-gray-800 dark:text-emerald-300'
                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
                    }`}
                >
                    {t('global.depot.tool')}
                </button>
            </div>

            <div className="mt-3">
                <Label>{kind === 'medicine' ? t('global.medicine') : t('global.depot.tool')} *</Label>
                {kind === 'medicine' ? (
                    <SearchableSelect
                        value={medicineId}
                        onChange={onMedicineChange}
                        options={[
                            { value: '', label: t('global.select') },
                            ...formData.medicines.map((item) => ({
                                value: String(item.id),
                                label: item.name,
                            })),
                        ]}
                    />
                ) : (
                    <SearchableSelect
                        value={toolId}
                        onChange={onToolChange}
                        options={[
                            { value: '', label: t('global.select') },
                            ...formData.tools.map((item) => ({
                                value: String(item.id),
                                label: item.name,
                            })),
                        ]}
                    />
                )}
                {itemError && <p className="mt-1 text-sm text-red-600">{itemError}</p>}
            </div>
        </div>
    );
}
