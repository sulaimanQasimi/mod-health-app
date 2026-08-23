import { useForm } from '@inertiajs/react';
import { Button, Checkbox, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export interface ToolRecord {
    id: number;
    name: string;
    code: string;
    unit_id: string;
    description: string;
    is_active: boolean;
}

interface ToolFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    units: OptionItem[];
    tool?: ToolRecord;
}

export default function ToolForm({ mode, urls, units, tool }: ToolFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: tool?.name ?? '',
        code: tool?.code ?? '',
        unit_id: tool?.unit_id ?? '',
        description: tool?.description ?? '',
        is_active: tool?.is_active ?? true,
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
            <div className="grid gap-4 md:grid-cols-2">
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
                    <Label htmlFor="code">{t('global.code')} *</Label>
                    <TextInput
                        id="code"
                        value={data.code}
                        onChange={(event) => setData('code', event.target.value)}
                        required
                    />
                    {errors.code && <p className="mt-1 text-sm text-red-600">{errors.code}</p>}
                </div>
                <div>
                    <Label>{t('global.unit')}</Label>
                    <SearchableSelect
                        value={data.unit_id}
                        onChange={(value) => setData('unit_id', value)}
                        options={[
                            { value: '', label: t('global.select') },
                            ...units.map((unit) => ({
                                value: String(unit.id),
                                label: unit.name,
                            })),
                        ]}
                    />
                    {errors.unit_id && <p className="mt-1 text-sm text-red-600">{errors.unit_id}</p>}
                </div>
                <div className="flex items-end pb-1">
                    <label className="flex items-center gap-2">
                        <Checkbox
                            checked={data.is_active}
                            onChange={(event) => setData('is_active', event.target.checked)}
                        />
                        <span>{t('global.active')}</span>
                    </label>
                    {errors.is_active && <p className="mt-1 text-sm text-red-600">{errors.is_active}</p>}
                </div>
            </div>
            <div>
                <Label htmlFor="description">{t('global.description')}</Label>
                <Textarea
                    id="description"
                    rows={3}
                    value={data.description}
                    onChange={(event) => setData('description', event.target.value)}
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
