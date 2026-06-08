import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useRef } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem } from '../../types/settings';

interface PharmacyFulfillmentFormProps {
    mode: 'create' | 'edit';
    urls: { store: string; update: string; back: string };
    formData: { medicines: OptionItem[] };
    fulfillment?: {
        medicine_id: string;
        unit_type: string;
        amount: string | null;
        form_no: string | null;
        date: string | null;
        form_path?: string | null;
        pharmacy_name?: string | null;
    };
    userPharmacy?: { id: number; name: string } | null;
}

export default function PharmacyFulfillmentForm({
    mode,
    urls,
    formData,
    fulfillment,
    userPharmacy,
}: PharmacyFulfillmentFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, put, processing, errors } = useForm({
        medicine_id: fulfillment?.medicine_id ?? '',
        unit_type: fulfillment?.unit_type ?? '',
        amount: fulfillment?.amount ?? '',
        form_no: fulfillment?.form_no ?? '',
        date: fulfillment?.date ?? '',
        form: null as File | null,
    });

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        const options = { forceFormData: true, preserveScroll: true };
        if (isEdit) put(urls.update, options);
        else post(urls.store, options);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            {userPharmacy && (
                <div>
                    <Label>{t('global.pharmacy')}</Label>
                    <TextInput value={userPharmacy.name} disabled readOnly />
                </div>
            )}
            {isEdit && fulfillment?.pharmacy_name && !userPharmacy && (
                <div>
                    <Label>{t('global.pharmacy')}</Label>
                    <TextInput value={fulfillment.pharmacy_name} disabled readOnly />
                </div>
            )}
            <div>
                <Label htmlFor="medicine_id">{t('global.medicine')} *</Label>
                <SearchableSelect
                    id="medicine_id"
                    value={data.medicine_id}
                    onChange={(value) => setData('medicine_id', value)}
                    options={(formData?.medicines ?? []).map((medicine) => ({
                        value: String(medicine.id),
                        label: medicine.name,
                    }))}
                    placeholder={t('global.select')}
                    required
                />
                {errors.medicine_id && <p className="mt-1 text-sm text-red-600">{errors.medicine_id}</p>}
            </div>
            <div>
                <Label htmlFor="unit_type">{t('global.unit_type')}</Label>
                <TextInput
                    id="unit_type"
                    value={data.unit_type}
                    onChange={(event) => setData('unit_type', event.target.value)}
                />
                {errors.unit_type && <p className="mt-1 text-sm text-red-600">{errors.unit_type}</p>}
            </div>
            <div>
                <Label htmlFor="amount">{t('global.amount')} *</Label>
                <TextInput
                    id="amount"
                    value={data.amount}
                    onChange={(event) => setData('amount', event.target.value)}
                    required
                />
                {errors.amount && <p className="mt-1 text-sm text-red-600">{errors.amount}</p>}
            </div>
            <div>
                <Label htmlFor="form_no">{t('global.form_no')} *</Label>
                <TextInput
                    id="form_no"
                    value={data.form_no}
                    onChange={(event) => setData('form_no', event.target.value)}
                    required
                />
                {errors.form_no && <p className="mt-1 text-sm text-red-600">{errors.form_no}</p>}
            </div>
            <div>
                <Label htmlFor="date">{t('global.date')} *</Label>
                <TextInput
                    id="date"
                    value={data.date}
                    onChange={(event) => setData('date', event.target.value)}
                    required
                />
                {errors.date && <p className="mt-1 text-sm text-red-600">{errors.date}</p>}
            </div>
            <div>
                <Label htmlFor="form">PDF</Label>
                {isEdit && fulfillment?.form_path && (
                    <p className="mb-2 text-sm text-gray-500">
                        {t('global.prosthetics_attachments')}: {fulfillment.form_path.split('/').pop()}
                    </p>
                )}
                <input
                    ref={fileInputRef}
                    id="form"
                    type="file"
                    accept="application/pdf"
                    className="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 file:me-4 file:rounded-s-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-blue-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400"
                    onChange={(event) => setData('form', event.target.files?.[0] ?? null)}
                />
                {errors.form && <p className="mt-1 text-sm text-red-600">{errors.form}</p>}
            </div>
            <div className="flex justify-end gap-2 border-t pt-4">
                <Button color="light" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" disabled={processing}>
                    {processing ? <Spinner size="sm" /> : isEdit ? t('global.update') : t('global.create')}
                </Button>
            </div>
        </form>
    );
}
