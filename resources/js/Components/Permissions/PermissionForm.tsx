import { useForm } from '@inertiajs/react';
import { Button, Label, Select, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { PermissionFormData, PermissionRecord } from '../../types/permission';
import { SettingsFormUrls } from '../../types/settings';

interface PermissionFormProps {
    mode: 'create' | 'edit';
    formData: PermissionFormData;
    urls: SettingsFormUrls;
    permission?: PermissionRecord;
}

export default function PermissionForm({ mode, formData, urls, permission }: PermissionFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors, transform } = useForm<{
        name: string;
        name_dr: string;
        parent_id: string;
    }>({
        name: permission?.name ?? '',
        name_dr: permission?.name_dr ?? '',
        parent_id: permission?.parent_id ? String(permission.parent_id) : '',
    });

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();

        transform((currentData) => ({
            ...currentData,
            parent_id: currentData.parent_id || null,
        }));

        const options = { preserveScroll: true };

        if (isEdit) {
            put(urls.update, options);
            return;
        }

        post(urls.store, options);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="permission-name">{t('global.name_en')} *</Label>
                    <TextInput
                        id="permission-name"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        required
                    />
                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                </div>
                <div>
                    <Label htmlFor="permission-name-dr">{t('global.name_dr')} *</Label>
                    <TextInput
                        id="permission-name-dr"
                        value={data.name_dr}
                        onChange={(event) => setData('name_dr', event.target.value)}
                        required
                    />
                    {errors.name_dr && <p className="mt-1 text-sm text-red-600">{errors.name_dr}</p>}
                </div>
            </div>

            <div>
                <Label htmlFor="permission-parent">{t('global.parent')}</Label>
                <Select
                    id="permission-parent"
                    value={data.parent_id}
                    onChange={(event) => setData('parent_id', event.target.value)}
                >
                    <option value="">{t('global.no_parent')}</option>
                    {formData.parentOptions.map((option) => (
                        <option key={option.id} value={option.id}>
                            {option.name_dr ?? option.name}
                        </option>
                    ))}
                </Select>
                {errors.parent_id && <p className="mt-1 text-sm text-red-600">{errors.parent_id}</p>}
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
