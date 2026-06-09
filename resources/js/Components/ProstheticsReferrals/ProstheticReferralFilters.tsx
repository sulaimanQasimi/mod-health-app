import { Button, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';

export interface ProstheticReferralFilters {
    q: string;
    referral_number: string;
    patient_id: string;
    patient_name: string;
    phone: string;
    nid: string;
    id_card: string;
    status: string;
    urgency: string;
    requested_service_type: string;
    from: string;
    to: string;
}

export const EMPTY_PROSTHETIC_REFERRAL_FILTERS: ProstheticReferralFilters = {
    q: '',
    referral_number: '',
    patient_id: '',
    patient_name: '',
    phone: '',
    nid: '',
    id_card: '',
    status: '',
    urgency: '',
    requested_service_type: '',
    from: '',
    to: '',
};

interface ProstheticReferralFiltersProps {
    filters: ProstheticReferralFilters;
    statusOptions: string[];
    processing: boolean;
    onChange: (filters: ProstheticReferralFilters) => void;
    onApply: (filters: ProstheticReferralFilters) => void;
    onReset: () => void;
}

export default function ProstheticReferralFilters({
    filters,
    statusOptions,
    processing,
    onChange,
    onApply,
    onReset,
}: ProstheticReferralFiltersProps) {
    const { t } = useTranslation();

    const setField = (key: keyof ProstheticReferralFilters, value: string) => {
        onChange({ ...filters, [key]: value });
    };

    return (
        <form
            className="grid gap-3 md:grid-cols-4"
            onSubmit={(e: FormEvent) => {
                e.preventDefault();
                onApply(filters);
            }}
        >
            {[
                { key: 'q' as const, label: t('global.search') },
                { key: 'referral_number' as const, label: t('global.prosthetics_referral_number') },
                { key: 'patient_name' as const, label: t('global.patient_name') },
                { key: 'patient_id' as const, label: t('global.id'), type: 'number' },
                { key: 'phone' as const, label: t('global.phone') },
                { key: 'nid' as const, label: t('global.nid') },
                { key: 'id_card' as const, label: t('global.id_card') },
                { key: 'urgency' as const, label: t('global.urgency') },
                { key: 'requested_service_type' as const, label: t('global.prosthetics_requested_service_type') },
                { key: 'from' as const, label: t('global.from'), type: 'date' },
                { key: 'to' as const, label: t('global.to'), type: 'date' },
            ].map((field) => (
                <div key={field.key}>
                    <Label htmlFor={field.key} value={field.label} className="mb-1 text-xs" />
                    <TextInput
                        id={field.key}
                        type={field.type ?? 'text'}
                        sizing="sm"
                        value={filters[field.key]}
                        onChange={(e) => setField(field.key, e.target.value)}
                    />
                </div>
            ))}
            <div>
                <Label htmlFor="status" value={t('global.status')} className="mb-1 text-xs" />
                <select
                    id="status"
                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
                    value={filters.status}
                    onChange={(e) => setField('status', e.target.value)}
                >
                    <option value="">{t('global.all')}</option>
                    {statusOptions.map((status) => (
                        <option key={status} value={status}>
                            {status}
                        </option>
                    ))}
                </select>
            </div>
            <div className="flex items-end gap-2 md:col-span-4">
                <Button type="submit" color="blue" size="sm" disabled={processing}>
                    {t('global.filter')}
                </Button>
                <Button type="button" color="light" size="sm" onClick={onReset} disabled={processing}>
                    {t('global.reset')}
                </Button>
            </div>
        </form>
    );
}
