import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface VitalSignTypeFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    vitalSignType?: { id: number; name: string };
}

export default function VitalSignTypeForm({ mode, urls, vitalSignType }: VitalSignTypeFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: vitalSignType?.name ?? '',
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
                <Label htmlFor="name">{t('global.vital_sign_type_name')} *</Label>
                <TextInput
                    id="name"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    required
                />
                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
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
