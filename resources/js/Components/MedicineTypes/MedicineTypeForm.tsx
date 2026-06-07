import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface MedicineTypeFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    medicineType?: { id: number; type: string };
}

export default function MedicineTypeForm({ mode, urls, medicineType }: MedicineTypeFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        type: medicineType?.type ?? '',
    });

    const typeLabel = t('global.medicine_type') || t('global.type');

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
                <Label htmlFor="type">{typeLabel} *</Label>
                <TextInput
                    id="type"
                    value={data.type}
                    onChange={(event) => setData('type', event.target.value)}
                    required
                />
                {errors.type && <p className="mt-1 text-sm text-red-600">{errors.type}</p>}
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
