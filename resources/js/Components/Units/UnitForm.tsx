import { useForm } from '@inertiajs/react';
import { Button, Checkbox, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface UnitRecord {
    id: number;
    name: string;
    symbol: string | null;
    is_active: boolean;
}

interface UnitFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    unit?: UnitRecord;
}

export default function UnitForm({ mode, urls, unit }: UnitFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: unit?.name ?? '',
        symbol: unit?.symbol ?? '',
        is_active: unit?.is_active ?? true,
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
                <TextInput
                    id="name"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    required
                />
                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
            </div>
            <div>
                <Label htmlFor="symbol">{t('global.symbol')}</Label>
                <TextInput
                    id="symbol"
                    value={data.symbol}
                    onChange={(event) => setData('symbol', event.target.value)}
                />
                {errors.symbol && <p className="mt-1 text-sm text-red-600">{errors.symbol}</p>}
            </div>
            <label className="flex items-center gap-2">
                <Checkbox
                    checked={data.is_active}
                    onChange={(event) => setData('is_active', event.target.checked)}
                />
                <span>{t('global.active')}</span>
            </label>
            {errors.is_active && <p className="mt-1 text-sm text-red-600">{errors.is_active}</p>}
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
