import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

interface FloorFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { branches: OptionItem[] };
    floor?: { id: number; name: string; branch_id: string };
}

export default function FloorForm({ mode, urls, formData, floor }: FloorFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        branch_id: floor?.branch_id ?? '',
        name: floor?.name ?? '',
    });

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
            <div className="grid gap-2 sm:grid-cols-2">
                <div>
                    <Label htmlFor="branch_id" className="mb-0.5 !text-[11px]">
                        {t('global.branch')} *
                    </Label>
                    <SearchableSelect
                        compact
                        value={data.branch_id}
                        onChange={(value) => setData('branch_id', value)}
                        options={formData.branches.map((branch) => ({
                            value: String(branch.id),
                            label: branch.name,
                        }))}
                        placeholder={t('global.select')}
                    />
                    {errors.branch_id && <p className="mt-0.5 text-[11px] text-red-600">{errors.branch_id}</p>}
                </div>
                <div>
                    <Label htmlFor="name" className="mb-0.5 !text-[11px]">
                        {t('global.floor')} *
                    </Label>
                    <TextInput
                        id="name"
                        sizing="sm"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        placeholder={t('global.floor')}
                        disabled={!data.branch_id}
                        required
                    />
                    {errors.name && <p className="mt-0.5 text-[11px] text-red-600">{errors.name}</p>}
                </div>
            </div>

            <div className="flex justify-end gap-1.5 border-t border-gray-200 pt-2 dark:border-gray-700">
                <Button color="light" size="xs" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" size="xs" disabled={processing || !data.branch_id}>
                    {processing ? <Spinner size="xs" /> : isEdit ? t('global.update') : t('global.create')}
                </Button>
            </div>
        </form>
    );
}
