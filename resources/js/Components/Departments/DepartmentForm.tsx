import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

interface DepartmentFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { categories: OptionItem[] };
    department?: { id: number; name: string; room_number: string; price: string; category_id: string };
}

function sanitizeNonNegativePrice(value: string): string {
    if (value === '' || value === '.') {
        return value;
    }

    const parsed = Number(value);
    if (Number.isNaN(parsed)) {
        return '';
    }

    return parsed < 0 ? '0' : value;
}

export default function DepartmentForm({ mode, urls, formData, department }: DepartmentFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: department?.name ?? '',
        room_number: department?.room_number ?? '',
        price: department?.price ?? '',
        category_id: department?.category_id ?? '',
    });

    return (
        <form
            onSubmit={(event: FormEvent) => {
                event.preventDefault();
                const opts = { preserveScroll: true };
                if (isEdit) put(urls.update, opts);
                else post(urls.store, opts);
            }}
            className="space-y-4"
        >
            <div>
                <Label htmlFor="name">{t('global.name')} *</Label>
                <TextInput id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
            </div>
            <div>
                <Label htmlFor="room_number">{t('global.room_number')}</Label>
                <TextInput
                    id="room_number"
                    value={data.room_number}
                    onChange={(e) => setData('room_number', e.target.value)}
                />
                {errors.room_number && <p className="mt-1 text-sm text-red-600">{errors.room_number}</p>}
            </div>
            <div>
                <Label htmlFor="price">{t('global.price')}</Label>
                <TextInput
                    id="price"
                    type="number"
                    min={0}
                    step="0.01"
                    inputMode="decimal"
                    value={data.price}
                    onChange={(e) => setData('price', sanitizeNonNegativePrice(e.target.value))}
                    onKeyDown={(e) => {
                        if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') {
                            e.preventDefault();
                        }
                    }}
                />
                {errors.price && <p className="mt-1 text-sm text-red-600">{errors.price}</p>}
            </div>
            <div>
                <Label htmlFor="category_id">{t('global.category')}</Label>
                <SearchableSelect
                    value={data.category_id}
                    onChange={(value) => setData('category_id', value)}
                    options={formData.categories.map((c) => ({ value: String(c.id), label: c.name }))}
                    placeholder={t('global.select')}
                />
                {errors.category_id && <p className="mt-1 text-sm text-red-600">{errors.category_id}</p>}
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
