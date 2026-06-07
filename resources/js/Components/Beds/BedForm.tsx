import { useForm } from '@inertiajs/react';
import { Button, Checkbox, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

interface BedFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { rooms: OptionItem[] };
    bed?: { id: number; number: string; room_id: string; is_occupied: boolean };
}

export default function BedForm({ mode, urls, formData, bed }: BedFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        number: bed?.number ?? '',
        room_id: bed?.room_id ?? '',
        is_occupied: bed?.is_occupied ?? false,
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
                <Label htmlFor="number">{t('global.bed_number')} *</Label>
                <TextInput
                    id="number"
                    value={data.number}
                    onChange={(event) => setData('number', event.target.value)}
                    required
                />
                {errors.number && <p className="mt-1 text-sm text-red-600">{errors.number}</p>}
            </div>
            <div>
                <Label htmlFor="room_id">{t('global.room')} *</Label>
                <SearchableSelect
                    value={data.room_id}
                    onChange={(value) => setData('room_id', value)}
                    options={formData.rooms.map((room) => ({
                        value: String(room.id),
                        label: room.name,
                    }))}
                    placeholder={t('global.select')}
                />
                {errors.room_id && <p className="mt-1 text-sm text-red-600">{errors.room_id}</p>}
            </div>
            <div className="flex items-center gap-2">
                <Checkbox
                    id="is_occupied"
                    checked={data.is_occupied}
                    onChange={(event) => setData('is_occupied', event.target.checked)}
                />
                <Label htmlFor="is_occupied">{t('global.occupied')}</Label>
            </div>
            {errors.is_occupied && <p className="text-sm text-red-600">{errors.is_occupied}</p>}
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
