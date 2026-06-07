import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface RecipientFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    recipient?: { id: number; name: string; description: string };
}

export default function RecipientForm({ mode, urls, recipient }: RecipientFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: recipient?.name ?? '',
        description: recipient?.description ?? '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        const opts = { preserveScroll: true };
        if (isEdit) put(urls.update, opts);
        else post(urls.store, opts);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <Label htmlFor="name">{t('global.name')} *</Label>
                <TextInput id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
            </div>
            <div>
                <Label htmlFor="description">{t('global.description')}</Label>
                <Textarea
                    id="description"
                    rows={3}
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                />
                {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
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
