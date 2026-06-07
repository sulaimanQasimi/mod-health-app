import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

interface SectionFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { departments: OptionItem[] };
    section?: { id: number; name: string; department_id: string };
}

export default function SectionForm({ mode, urls, formData, section }: SectionFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: section?.name ?? '',
        department_id: section?.department_id ?? '',
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
                <Label htmlFor="department_id">{t('global.department')} *</Label>
                <SearchableSelect
                    value={data.department_id}
                    onChange={(value) => setData('department_id', value)}
                    options={formData.departments.map((d) => ({ value: String(d.id), label: d.name }))}
                    placeholder={t('global.select')}
                />
                {errors.department_id && <p className="mt-1 text-sm text-red-600">{errors.department_id}</p>}
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
