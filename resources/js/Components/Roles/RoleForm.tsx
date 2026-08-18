import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';
import PermissionTree from './PermissionTree';
import { RoleFormData, RoleRecord } from './roleTypes';

interface RoleFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: RoleFormData;
    role?: RoleRecord;
}

export default function RoleForm({ mode, urls, formData, role }: RoleFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm<{
        name: string;
        name_dr: string;
        permission: number[];
    }>({
        name: role?.name ?? '',
        name_dr: role?.name_dr ?? '',
        permission: role?.permission_ids ?? [],
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
                    <Label htmlFor="name">{t('global.name_en')} *</Label>
                    <TextInput
                        id="name"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        required
                    />
                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                </div>
                <div>
                    <Label htmlFor="name_dr">{t('global.name_dr')} *</Label>
                    <TextInput
                        id="name_dr"
                        value={data.name_dr}
                        onChange={(event) => setData('name_dr', event.target.value)}
                        required
                    />
                    {errors.name_dr && <p className="mt-1 text-sm text-red-600">{errors.name_dr}</p>}
                </div>
            </div>
            <PermissionTree
                nodes={formData.permissionTree}
                selectedIds={data.permission}
                onChange={(ids) => setData('permission', ids)}
            />
            {errors.permission && <p className="mt-1 text-sm text-red-600">{errors.permission}</p>}
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
