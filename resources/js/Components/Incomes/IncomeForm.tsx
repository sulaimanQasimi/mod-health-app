import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem } from '../../types/settings';

interface IncomeFormProps {
    urls: { store: string; back: string };
    formData: { medicines: OptionItem[]; incomeTypes: string[] };
}

export default function IncomeForm({ urls, formData }: IncomeFormProps) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        medicine_id: '',
        amount: '',
        batch_number: '',
        supplier_name: '',
        purchase_price: '',
        purchase_date: '',
        income_type: '',
        notes: '',
    });

    return (
        <form
            onSubmit={(event: FormEvent) => {
                event.preventDefault();
                post(urls.store, { preserveScroll: true });
            }}
            className="space-y-4"
        >
            <div className="grid gap-4 md:grid-cols-2">
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
                    <Label htmlFor="income_type">{t('global.income_type')} *</Label>
                    <SearchableSelect
                        id="income_type"
                        value={data.income_type}
                        onChange={(value) => setData('income_type', value)}
                        options={(formData?.incomeTypes ?? []).map((type) => ({
                            value: type,
                            label: t(`global.${type}`),
                        }))}
                        placeholder={t('global.select_income_type')}
                        required
                    />
                    {errors.income_type && <p className="mt-1 text-sm text-red-600">{errors.income_type}</p>}
                </div>
                <div>
                    <Label htmlFor="amount">{t('global.amount')} *</Label>
                    <TextInput
                        id="amount"
                        type="number"
                        min={1}
                        value={data.amount}
                        onChange={(event) => setData('amount', event.target.value)}
                        required
                    />
                    {errors.amount && <p className="mt-1 text-sm text-red-600">{errors.amount}</p>}
                </div>
                <div>
                    <Label htmlFor="batch_number">{t('global.batch_number')}</Label>
                    <TextInput
                        id="batch_number"
                        value={data.batch_number}
                        onChange={(event) => setData('batch_number', event.target.value)}
                    />
                    {errors.batch_number && <p className="mt-1 text-sm text-red-600">{errors.batch_number}</p>}
                </div>
                <div>
                    <Label htmlFor="supplier_name">{t('global.supplier_name')}</Label>
                    <TextInput
                        id="supplier_name"
                        value={data.supplier_name}
                        onChange={(event) => setData('supplier_name', event.target.value)}
                    />
                    {errors.supplier_name && <p className="mt-1 text-sm text-red-600">{errors.supplier_name}</p>}
                </div>
                <div>
                    <Label htmlFor="purchase_price">{t('global.purchase_price')}</Label>
                    <TextInput
                        id="purchase_price"
                        type="number"
                        min={0}
                        step="0.01"
                        value={data.purchase_price}
                        onChange={(event) => setData('purchase_price', event.target.value)}
                    />
                    {errors.purchase_price && <p className="mt-1 text-sm text-red-600">{errors.purchase_price}</p>}
                </div>
                <div>
                    <Label htmlFor="purchase_date">{t('global.purchase_date')}</Label>
                    <PersianDateInput
                        id="purchase_date"
                        value={data.purchase_date}
                        onChange={(value) => setData('purchase_date', value)}
                    />
                    {errors.purchase_date && <p className="mt-1 text-sm text-red-600">{errors.purchase_date}</p>}
                </div>
                <div className="md:col-span-2">
                    <Label htmlFor="notes">{t('global.notes')}</Label>
                    <Textarea
                        id="notes"
                        rows={3}
                        value={data.notes}
                        onChange={(event) => setData('notes', event.target.value)}
                        placeholder={t('global.add_notes_here')}
                    />
                    {errors.notes && <p className="mt-1 text-sm text-red-600">{errors.notes}</p>}
                </div>
            </div>
            <div className="flex justify-end gap-2 border-t pt-4">
                <Button color="light" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" disabled={processing}>
                    {processing ? <Spinner size="sm" /> : t('global.create')}
                </Button>
            </div>
        </form>
    );
}
