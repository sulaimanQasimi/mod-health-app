import { useForm } from '@inertiajs/react';
import { Button, Checkbox, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useMemo } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface FloorOption {
    id: number;
    name: string;
    branch_id: number;
}

interface RoomOption {
    id: number;
    name: string;
    floor_id: number;
    branch_id: number;
}

interface OptionItem {
    id: number;
    name: string;
}

interface BedFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: {
        branches: OptionItem[];
        floors: FloorOption[];
        rooms: RoomOption[];
    };
    bed?: {
        id: number;
        number: string;
        branch_id: string;
        floor_id: string;
        room_id: string;
        is_occupied: boolean;
    };
}

export default function BedForm({ mode, urls, formData, bed }: BedFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        branch_id: bed?.branch_id ?? '',
        floor_id: bed?.floor_id ?? '',
        room_id: bed?.room_id ?? '',
        number: bed?.number ?? '',
        is_occupied: bed?.is_occupied ?? false,
    });

    const floorsForBranch = useMemo(
        () =>
            formData.floors.filter((floor) =>
                data.branch_id ? String(floor.branch_id) === String(data.branch_id) : false,
            ),
        [data.branch_id, formData.floors],
    );

    const roomsForFloor = useMemo(
        () =>
            formData.rooms.filter((room) =>
                data.floor_id ? String(room.floor_id) === String(data.floor_id) : false,
            ),
        [data.floor_id, formData.rooms],
    );

    return (
        <form
            onSubmit={(event: FormEvent) => {
                event.preventDefault();
                const opts = { preserveScroll: true };
                if (isEdit) put(urls.update, opts);
                else post(urls.store, opts);
            }}
            className="space-y-3"
        >
            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <Label htmlFor="branch_id" className="mb-0.5 !text-[11px]">
                        {t('global.branch')} *
                    </Label>
                    <SearchableSelect
                        compact
                        value={data.branch_id}
                        onChange={(value) =>
                            setData({ ...data, branch_id: value, floor_id: '', room_id: '' })
                        }
                        options={formData.branches.map((branch) => ({
                            value: String(branch.id),
                            label: branch.name,
                        }))}
                        placeholder={t('global.select')}
                    />
                    {errors.branch_id && <p className="mt-0.5 text-[11px] text-red-600">{errors.branch_id}</p>}
                </div>
                <div>
                    <Label htmlFor="floor_id" className="mb-0.5 !text-[11px]">
                        {t('global.floor')} *
                    </Label>
                    <SearchableSelect
                        compact
                        value={data.floor_id}
                        onChange={(value) => setData({ ...data, floor_id: value, room_id: '' })}
                        options={floorsForBranch.map((floor) => ({
                            value: String(floor.id),
                            label: floor.name,
                        }))}
                        placeholder={data.branch_id ? t('global.select') : t('global.select_branch_first')}
                        disabled={!data.branch_id}
                    />
                    {errors.floor_id && <p className="mt-0.5 text-[11px] text-red-600">{errors.floor_id}</p>}
                </div>
                <div>
                    <Label htmlFor="room_id" className="mb-0.5 !text-[11px]">
                        {t('global.room')} *
                    </Label>
                    <SearchableSelect
                        compact
                        value={data.room_id}
                        onChange={(value) => setData('room_id', value)}
                        options={roomsForFloor.map((room) => ({
                            value: String(room.id),
                            label: room.name,
                        }))}
                        placeholder={data.floor_id ? t('global.select') : t('global.select_floor_first')}
                        disabled={!data.floor_id}
                    />
                    {errors.room_id && <p className="mt-0.5 text-[11px] text-red-600">{errors.room_id}</p>}
                </div>
                <div>
                    <Label htmlFor="number" className="mb-0.5 !text-[11px]">
                        {t('global.bed_number')} *
                    </Label>
                    <TextInput
                        id="number"
                        sizing="sm"
                        value={data.number}
                        onChange={(event) => setData('number', event.target.value)}
                        placeholder={t('global.bed_number')}
                        disabled={!data.room_id}
                        required
                    />
                    {errors.number && <p className="mt-0.5 text-[11px] text-red-600">{errors.number}</p>}
                </div>
                <div className="flex items-end pb-0.5">
                    <label className="inline-flex items-center gap-1.5 text-xs text-gray-700 dark:text-gray-300">
                        <Checkbox
                            id="is_occupied"
                            checked={data.is_occupied}
                            onChange={(event) => setData('is_occupied', event.target.checked)}
                        />
                        {t('global.occupied')}
                    </label>
                </div>
            </div>
            {errors.is_occupied && <p className="text-[11px] text-red-600">{errors.is_occupied}</p>}

            <div className="flex justify-end gap-1.5 border-t border-gray-200 pt-2 dark:border-gray-700">
                <Button color="light" size="xs" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" size="xs" disabled={processing || !data.room_id}>
                    {processing ? <Spinner size="xs" /> : isEdit ? t('global.update') : t('global.create')}
                </Button>
            </div>
        </form>
    );
}
