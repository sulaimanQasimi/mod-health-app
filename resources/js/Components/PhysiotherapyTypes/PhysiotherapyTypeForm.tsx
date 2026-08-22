import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface PhysiotherapyTypeFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    physiotherapyType?: { id: number; name: string; description: string };
}

export default function PhysiotherapyTypeForm({ mode, urls, physiotherapyType }: PhysiotherapyTypeFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: physiotherapyType?.name ?? '',
        description: physiotherapyType?.description ?? '',
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
                <Label htmlFor="physiotherapy-type-name">{t('global.name')} *</Label>
                <TextInput
                    id="physiotherapy-type-name"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    placeholder={t('global.enter_physiotherapy_type_name')}
                    required
                    maxLength={255}
                />
                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
            </div>
            <div>
                <Label htmlFor="physiotherapy-type-description">{t('global.description')}</Label>
                <Textarea
                    id="physiotherapy-type-description"
                    rows={4}
                    value={data.description}
                    onChange={(event) => setData('description', event.target.value)}
                    placeholder={t('global.enter_physiotherapy_type_description')}
                    maxLength={1000}
                />
                {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
            </div>
            <div className="flex justify-end gap-2 border-t pt-4">
                <Button color="light" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" disabled={processing}>
                    {processing ? (
                        <Spinner size="sm" />
                    ) : isEdit ? (
                        t('global.update_physiotherapy_type')
                    ) : (
                        t('global.create_physiotherapy_type')
                    )}
                </Button>
            </div>
        </form>
    );
}
