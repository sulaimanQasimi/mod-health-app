import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

interface RecipientPartFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { recipients: OptionItem[] };
    recipientPart?: {
        id: number;
        name: string;
        code: string;
        recipient_id: string;
    };
}

export default function RecipientPartForm({ mode, urls, formData, recipientPart }: RecipientPartFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        recipient_id: recipientPart?.recipient_id ?? '',
        name: recipientPart?.name ?? '',
        code: recipientPart?.code ?? '',
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
                <Label htmlFor="recipient_id">{t('global.recipient')} *</Label>
                <SearchableSelect
                    value={data.recipient_id}
                    onChange={(value) => setData('recipient_id', value)}
                    options={formData.recipients.map((recipient) => ({
                        value: String(recipient.id),
                        label: recipient.name,
                    }))}
                    placeholder={t('global.select')}
                />
                {errors.recipient_id && <p className="mt-1 text-sm text-red-600">{errors.recipient_id}</p>}
            </div>
            <div>
                <Label htmlFor="name">{t('global.name')} *</Label>
                <TextInput id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
            </div>
            <div>
                <Label htmlFor="code">{t('global.code')} *</Label>
                <TextInput id="code" value={data.code} onChange={(e) => setData('code', e.target.value)} required />
                {errors.code && <p className="mt-1 text-sm text-red-600">{errors.code}</p>}
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
