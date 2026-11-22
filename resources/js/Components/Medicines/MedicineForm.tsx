import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

type Option = { id: number; name?: string; type?: string; symbol?: string; gram?: number | null; ml?: number | null };

interface MedicineFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    medicine?: {
        id: number;
        name: string;
        medicine_type_id?: number | null;
        medicine_country_id?: number | null;
        medicine_formula_id?: number | null;
        medicine_gram_ml_id?: number | null;
        unit_id?: number | null;
        units_per_pack?: number;
    };
    formData: {
        types: Option[];
        countries: Option[];
        formulas: Option[];
        gramMls: Option[];
        units: Option[];
    };
}

export default function MedicineForm({ mode, urls, medicine, formData }: MedicineFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: medicine?.name ?? '',
        medicine_type_id: medicine?.medicine_type_id ? String(medicine.medicine_type_id) : '',
        medicine_country_id: medicine?.medicine_country_id ? String(medicine.medicine_country_id) : '',
        medicine_formula_id: medicine?.medicine_formula_id ? String(medicine.medicine_formula_id) : '',
        medicine_gram_ml_id: medicine?.medicine_gram_ml_id ? String(medicine.medicine_gram_ml_id) : '',
        unit_id: medicine?.unit_id ? String(medicine.unit_id) : '',
        units_per_pack: String(medicine?.units_per_pack ?? 1),
    });

    return (
        <form
            onSubmit={(event: FormEvent) => {
                event.preventDefault();
                const opts = { preserveScroll: true };
                if (isEdit) put(urls.update, opts);
                else post(urls.store, opts);
            }}
            className="space-y-3"
        >
            <div>
                <Label htmlFor="name" className="text-xs">
                    {t('global.name')} *
                </Label>
                <TextInput
                    id="name"
                    sizing="sm"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    required
                />
                {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
            </div>

            <div className="grid gap-2 sm:grid-cols-2">
                <div>
                    <Label className="text-xs">Medicine type</Label>
                    <SearchableSelect
                        value={data.medicine_type_id}
                        onChange={(v) => setData('medicine_type_id', v)}
                        options={[
                            { value: '', label: 'Select type' },
                            ...formData.types.map((item) => ({
                                value: String(item.id),
                                label: item.type ?? item.name ?? String(item.id),
                            })),
                        ]}
                    />
                </div>
                <div>
                    <Label className="text-xs">Country</Label>
                    <SearchableSelect
                        value={data.medicine_country_id}
                        onChange={(v) => setData('medicine_country_id', v)}
                        options={[
                            { value: '', label: 'Select country' },
                            ...formData.countries.map((item) => ({
                                value: String(item.id),
                                label: item.name ?? String(item.id),
                            })),
                        ]}
                    />
                </div>
                <div>
                    <Label className="text-xs">Formula</Label>
                    <SearchableSelect
                        value={data.medicine_formula_id}
                        onChange={(v) => setData('medicine_formula_id', v)}
                        options={[
                            { value: '', label: 'Select formula' },
                            ...formData.formulas.map((item) => ({
                                value: String(item.id),
                                label: item.name ?? String(item.id),
                            })),
                        ]}
                    />
                </div>
                <div>
                    <Label className="text-xs">Gram / ml</Label>
                    <SearchableSelect
                        value={data.medicine_gram_ml_id}
                        onChange={(v) => setData('medicine_gram_ml_id', v)}
                        options={[
                            { value: '', label: 'Select gram/ml' },
                            ...formData.gramMls.map((item) => ({
                                value: String(item.id),
                                label: item.name ?? String(item.id),
                            })),
                        ]}
                    />
                </div>
                <div>
                    <Label className="text-xs">Unit</Label>
                    <SearchableSelect
                        value={data.unit_id}
                        onChange={(v) => setData('unit_id', v)}
                        options={[
                            { value: '', label: 'Select unit' },
                            ...formData.units.map((item) => ({
                                value: String(item.id),
                                label: item.symbol ? `${item.name} (${item.symbol})` : (item.name ?? String(item.id)),
                            })),
                        ]}
                    />
                </div>
                <div>
                    <Label className="text-xs">Units per pack</Label>
                    <TextInput
                        sizing="sm"
                        type="number"
                        min={1}
                        value={data.units_per_pack}
                        onChange={(e) => setData('units_per_pack', e.target.value)}
                    />
                </div>
            </div>

            <div className="flex justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-700">
                <Button color="light" size="xs" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" size="xs" color="success" disabled={processing}>
                    {processing ? <Spinner size="sm" /> : isEdit ? t('global.update') : t('global.create')}
                </Button>
            </div>
        </form>
    );
}
