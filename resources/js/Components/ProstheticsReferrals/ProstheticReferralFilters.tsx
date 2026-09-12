import { Button, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import PersianDateInput from '../ui/PersianDateInput';
import { useTranslation } from '../../hooks/useTranslation';
import { prostheticReferralStatusLabel } from './prostheticsReferralUi';

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

    const textFields: Array<{
        key: Exclude<keyof ProstheticReferralFilters, 'status' | 'from' | 'to'>;
        label: string;
        placeholder: string;
        type?: string;
    }> = [
        { key: 'q', label: t('global.search'), placeholder: t('global.search') },
        { key: 'referral_number', label: t('global.prosthetics_referral_number'), placeholder: t('global.prosthetics_referral_number') },
        { key: 'patient_name', label: t('global.patient_name'), placeholder: t('global.search_by_patient_name') },
        { key: 'patient_id', label: t('global.id'), type: 'number', placeholder: t('global.search_by_patient_id') },
        { key: 'phone', label: t('global.phone'), placeholder: t('global.phone') },
        { key: 'nid', label: t('global.nid'), placeholder: t('global.nid') },
        { key: 'id_card', label: t('global.id_card'), placeholder: t('global.search_by_card_number') },
        { key: 'urgency', label: t('global.urgency'), placeholder: t('global.urgency') },
        { key: 'requested_service_type', label: t('global.prosthetics_requested_service_type'), placeholder: t('global.prosthetics_service_type') },
    ];

    return (
        <form
            className="grid gap-3 md:grid-cols-4"
            onSubmit={(e: FormEvent) => {
                e.preventDefault();
                onApply(filters);
            }}
        >
            {textFields.map((field) => (
                <div key={field.key}>
                    <Label htmlFor={field.key} value={field.label} className="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300" />
                    <TextInput
                        id={field.key}
                        type={field.type ?? 'text'}
                        sizing="sm"
                        placeholder={field.placeholder}
                        value={filters[field.key]}
                        onChange={(e) => setField(field.key, e.target.value)}
                    />
                </div>
            ))}
            <div>
                <Label htmlFor="from" value={t('global.from')} className="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300" />
                <PersianDateInput
                    id="from"
                    value={filters.from}
                    onChange={(value) => setField('from', value)}
                    placeholder={t('global.from')}
                    className="p-2 text-sm"
                />
            </div>
            <div>
                <Label htmlFor="to" value={t('global.to')} className="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300" />
                <PersianDateInput
                    id="to"
                    value={filters.to}
                    onChange={(value) => setField('to', value)}
                    placeholder={t('global.to')}
                    className="p-2 text-sm"
                />
            </div>
            <div>
                <Label htmlFor="status" value={t('global.status')} className="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300" />
                <select
                    id="status"
                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    value={filters.status}
                    onChange={(e) => setField('status', e.target.value)}
                >
                    <option value="">{t('global.all')}</option>
                    {statusOptions.map((status) => (
                        <option key={status} value={status}>
                            {prostheticReferralStatusLabel(status, t)}
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
