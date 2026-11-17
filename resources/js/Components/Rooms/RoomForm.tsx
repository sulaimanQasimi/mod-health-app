import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useMemo } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

interface FloorOption {
    id: number;
    name: string;
    branch_id: number;
}

interface OptionItem {
    id: number;
    name: string;
}

interface RoomFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: {
        branches: OptionItem[];
        floors: FloorOption[];
        departments: OptionItem[];
    };
    room?: {
        id: number;
        name: string;
        branch_id: string;
        floor_id: string;
        department_id: string | null;
    };
}

export default function RoomForm({ mode, urls, formData, room }: RoomFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        branch_id: room?.branch_id ?? '',
        floor_id: room?.floor_id ?? '',
        name: room?.name ?? '',
        department_id: room?.department_id ?? '',
    });

    const floorsForBranch = useMemo(
        () =>
            formData.floors.filter((floor) =>
                data.branch_id ? String(floor.branch_id) === String(data.branch_id) : false,
            ),
        [data.branch_id, formData.floors],
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
                        onChange={(value) => setData({ ...data, branch_id: value, floor_id: '' })}
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
                        onChange={(value) => setData('floor_id', value)}
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
                    <Label htmlFor="name" className="mb-0.5 !text-[11px]">
                        {t('global.room')} *
                    </Label>
                    <TextInput
                        id="name"
                        sizing="sm"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        placeholder={t('global.room')}
                        disabled={!data.floor_id}
                        required
                    />
                    {errors.name && <p className="mt-0.5 text-[11px] text-red-600">{errors.name}</p>}
                </div>
                <div>
                    <Label htmlFor="department_id" className="mb-0.5 !text-[11px]">
                        {t('global.department')}
                    </Label>
                    <SearchableSelect
                        compact
                        value={data.department_id}
                        onChange={(value) => setData('department_id', value)}
                        options={[
                            { value: '', label: t('global.select') },
                            ...formData.departments.map((department) => ({
                                value: String(department.id),
                                label: department.name,
                            })),
                        ]}
                        placeholder={t('global.select')}
                    />
                    {errors.department_id && (
                        <p className="mt-0.5 text-[11px] text-red-600">{errors.department_id}</p>
                    )}
                </div>
            </div>

            <div className="flex justify-end gap-1.5 border-t border-gray-200 pt-2 dark:border-gray-700">
                <Button color="light" size="xs" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" size="xs" disabled={processing || !data.floor_id}>
                    {processing ? <Spinner size="xs" /> : isEdit ? t('global.update') : t('global.create')}
                </Button>
            </div>
        </form>
    );
}
