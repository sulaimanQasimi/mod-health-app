import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

interface RoomFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { floors: OptionItem[]; departments: OptionItem[] };
    room?: { id: number; name: string; floor_id: string; department_id: string | null };
}

export default function RoomForm({ mode, urls, formData, room }: RoomFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: room?.name ?? '',
        floor_id: room?.floor_id ?? '',
        department_id: room?.department_id ?? '',
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
                <Label htmlFor="floor_id">{t('global.floor')} *</Label>
                <SearchableSelect
                    value={data.floor_id}
                    onChange={(value) => setData('floor_id', value)}
                    options={formData.floors.map((f) => ({ value: String(f.id), label: f.name }))}
                    placeholder={t('global.select')}
                />
                {errors.floor_id && <p className="mt-1 text-sm text-red-600">{errors.floor_id}</p>}
            </div>
            <div>
                <Label htmlFor="department_id">{t('global.department')}</Label>
                <SearchableSelect
                    value={data.department_id}
                    onChange={(value) => setData('department_id', value)}
                    options={[
                        { value: '', label: t('global.select') },
                        ...formData.departments.map((d) => ({ value: String(d.id), label: d.name })),
                    ]}
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
