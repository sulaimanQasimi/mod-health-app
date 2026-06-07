import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

interface OperationTypeFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { branches: OptionItem[]; departments: OptionItem[] };
    operationType?: {
        id: number;
        name: string;
        branch_id: string;
        department_id: string;
    };
}

export default function OperationTypeForm({
    mode,
    urls,
    formData,
    operationType,
}: OperationTypeFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: operationType?.name ?? '',
        branch_id: operationType?.branch_id ?? '',
        department_id: operationType?.department_id ?? '',
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
                <Label htmlFor="branch_id">{t('global.branch')} *</Label>
                <SearchableSelect
                    value={data.branch_id}
                    onChange={(value) => setData('branch_id', value)}
                    options={formData.branches.map((branch) => ({
                        value: String(branch.id),
                        label: branch.name,
                    }))}
                    placeholder={t('global.select_branch')}
                />
                {errors.branch_id && <p className="mt-1 text-sm text-red-600">{errors.branch_id}</p>}
            </div>
            <div>
                <Label htmlFor="department_id">{t('global.department')} *</Label>
                <SearchableSelect
                    value={data.department_id}
                    onChange={(value) => setData('department_id', value)}
                    options={formData.departments.map((department) => ({
                        value: String(department.id),
                        label: department.name,
                    }))}
                    placeholder={t('global.select_department')}
                />
                {errors.department_id && (
                    <p className="mt-1 text-sm text-red-600">{errors.department_id}</p>
                )}
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
