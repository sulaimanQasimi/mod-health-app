import { useForm } from '@inertiajs/react';
import { Button, Checkbox, Label, Spinner, TextInput } from 'flowbite-react';
import { ChangeEvent, FormEvent, useMemo, useRef, useState } from 'react';
import SearchableMultiSelect from '../ui/SearchableMultiSelect';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { UserFormData, UserFormUrls, UserFormValues } from '../../types/user';

interface UserFormProps {
    mode: 'create' | 'edit';
    formData: UserFormData;
    urls: UserFormUrls;
    user?: UserFormValues;
}

export default function UserForm({ mode, formData, urls, user }: UserFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [avatarPreview, setAvatarPreview] = useState(user?.avatar_url ?? formData.defaultAvatar);

    const { data, setData, post, put, processing, errors } = useForm({
        name: user?.name ?? '',
        last_name: user?.last_name ?? '',
        email: user?.email ?? '',
        password: '',
        password_confirmation: '',
        branch_id: user?.branch_id ?? '',
        department_id: user?.department_id ?? '',
        section_id: user?.section_id ?? '',
        category_id: user?.category_id ?? '',
        is_doctor: user?.is_doctor ?? false,
        clinic_type: user?.clinic_type ?? '',
        roles: user?.roles ?? [],
        permissions: user?.permissions ?? [],
        avatar: null as File | null,
    });

    const departmentOptions = useMemo(() => {
        if (!data.branch_id) {
            return formData.departments;
        }
        return formData.departments.filter(
            (department) => String(department.branch_id) === data.branch_id,
        );
    }, [data.branch_id, formData.departments]);

    const sectionOptions = useMemo(() => {
        if (!data.department_id) {
            return formData.sections;
        }
        return formData.sections.filter(
            (section) => String(section.department_id) === data.department_id,
        );
    }, [data.department_id, formData.sections]);

    const roleOptions = useMemo(
        () =>
            formData.roles.map((role) => ({
                value: String(role.id),
                label: role.name_dr ?? role.name,
            })),
        [formData.roles],
    );

    const permissionOptions = useMemo(
        () =>
            formData.permissions.map((permission) => ({
                value: String(permission.id),
                label: permission.name_dr ?? permission.name,
            })),
        [formData.permissions],
    );

    const handleAvatarChange = (event: ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0] ?? null;
        setData('avatar', file);
        if (file) {
            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                if (typeof loadEvent.target?.result === 'string') {
                    setAvatarPreview(loadEvent.target.result);
                }
            };
            reader.readAsDataURL(file);
        }
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();

        const options = {
            forceFormData: true,
            preserveScroll: true,
        };

        if (isEdit) {
            put(urls.update, options);
            return;
        }

        post(urls.store, options);
    };

    const toggleAll = (field: 'roles' | 'permissions', options: Array<{ value: string }>) => {
        const allValues = options.map((option) => option.value);
        setData(field, data[field].length === allValues.length ? [] : allValues);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid gap-6 lg:grid-cols-12">
                <div className="space-y-6 lg:col-span-4">
                    <div className="rounded-xl border border-gray-200 p-6 text-center dark:border-gray-700">
                        <img
                            src={avatarPreview}
                            alt={t('global.avatar')}
                            className="mx-auto mb-4 h-28 w-28 rounded-full object-cover ring-4 ring-gray-100 dark:ring-gray-700"
                        />
                        <input
                            ref={fileInputRef}
                            type="file"
                            accept="image/png,image/jpeg,image/jpg,image/gif"
                            className="hidden"
                            onChange={handleAvatarChange}
                        />
                        <Button
                            type="button"
                            size="sm"
                            color="blue"
                            onClick={() => fileInputRef.current?.click()}
                        >
                            <i className="bx bx-upload me-2" />
                            {t('global.upload_new_photo')}
                        </Button>
                        {errors.avatar && (
                            <p className="mt-2 text-xs text-red-600">{errors.avatar}</p>
                        )}
                    </div>

                    <div className="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                        <h2 className="mb-4 text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.basic_information')}
                        </h2>
                        <div className="space-y-4">
                            <div>
                                <Label htmlFor="user-name">{t('global.name')}</Label>
                                <TextInput
                                    id="user-name"
                                    required
                                    value={data.name}
                                    onChange={(event) => setData('name', event.target.value)}
                                    color={errors.name ? 'failure' : undefined}
                                />
                                {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                            </div>
                            <div>
                                <Label htmlFor="user-last-name">{t('global.last_name')}</Label>
                                <TextInput
                                    id="user-last-name"
                                    required
                                    value={data.last_name}
                                    onChange={(event) => setData('last_name', event.target.value)}
                                    color={errors.last_name ? 'failure' : undefined}
                                />
                                {errors.last_name && (
                                    <p className="mt-1 text-xs text-red-600">{errors.last_name}</p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="user-email">{t('global.email')}</Label>
                                <TextInput
                                    id="user-email"
                                    type="email"
                                    required
                                    value={data.email}
                                    onChange={(event) => setData('email', event.target.value)}
                                    color={errors.email ? 'failure' : undefined}
                                />
                                {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="space-y-6 lg:col-span-8">
                    <div className="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                        <h2 className="mb-4 text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.professional_details')}
                        </h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label>{t('global.branch')}</Label>
                                <SearchableSelect
                                    value={data.branch_id}
                                    onChange={(value) => {
                                        setData({
                                            ...data,
                                            branch_id: value,
                                            department_id: '',
                                            section_id: '',
                                        });
                                    }}
                                    options={formData.branches.map((branch) => ({
                                        value: String(branch.id),
                                        label: branch.name,
                                    }))}
                                    placeholder={t('global.select')}
                                    required
                                />
                                {errors.branch_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.branch_id}</p>
                                )}
                            </div>
                            <div>
                                <Label>{t('global.department')}</Label>
                                <SearchableSelect
                                    value={data.department_id}
                                    onChange={(value) => {
                                        setData({
                                            ...data,
                                            department_id: value,
                                            section_id: '',
                                        });
                                    }}
                                    options={departmentOptions.map((department) => ({
                                        value: String(department.id),
                                        label: department.name,
                                    }))}
                                    placeholder={t('global.select')}
                                    required
                                />
                                {errors.department_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.department_id}</p>
                                )}
                            </div>
                            <div>
                                <Label>{t('global.section')}</Label>
                                <SearchableSelect
                                    value={data.section_id}
                                    onChange={(value) => setData('section_id', value)}
                                    options={sectionOptions.map((section) => ({
                                        value: String(section.id),
                                        label: section.name,
                                    }))}
                                    placeholder={t('global.select')}
                                    required
                                />
                                {errors.section_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.section_id}</p>
                                )}
                            </div>
                            <div>
                                <Label>{t('global.category')}</Label>
                                <SearchableSelect
                                    value={data.category_id}
                                    onChange={(value) => setData('category_id', value)}
                                    options={formData.categories.map((category) => ({
                                        value: String(category.id),
                                        label: category.name,
                                    }))}
                                    placeholder={t('global.select')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.clinic_type')}</Label>
                                <SearchableSelect
                                    value={data.clinic_type}
                                    onChange={(value) => setData('clinic_type', value)}
                                    options={formData.clinicTypes.map((clinicType) => ({
                                        value: clinicType.value,
                                        label: t(clinicType.label_key),
                                    }))}
                                    placeholder={t('global.select')}
                                />
                            </div>
                            <div className="flex items-end">
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="user-is-doctor"
                                        checked={data.is_doctor}
                                        onChange={(event) => setData('is_doctor', event.target.checked)}
                                    />
                                    <Label htmlFor="user-is-doctor">{t('global.is_doctor')}</Label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                        <h2 className="mb-4 text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.security')}
                        </h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label htmlFor="user-password">
                                    {t('global.password')}
                                    {isEdit && (
                                        <span className="ms-1 text-xs text-gray-500">
                                            ({t('global.optional')})
                                        </span>
                                    )}
                                </Label>
                                <TextInput
                                    id="user-password"
                                    type="password"
                                    autoComplete="new-password"
                                    required={!isEdit}
                                    value={data.password}
                                    onChange={(event) => setData('password', event.target.value)}
                                    color={errors.password ? 'failure' : undefined}
                                />
                                {errors.password && (
                                    <p className="mt-1 text-xs text-red-600">{errors.password}</p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="user-password-confirmation">
                                    {t('global.password_confirmation')}
                                </Label>
                                <TextInput
                                    id="user-password-confirmation"
                                    type="password"
                                    autoComplete="new-password"
                                    required={!isEdit && Boolean(data.password)}
                                    value={data.password_confirmation}
                                    onChange={(event) =>
                                        setData('password_confirmation', event.target.value)
                                    }
                                />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-xl border border-gray-200 p-6 dark:border-gray-700">
                        <h2 className="mb-4 text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.access_control')}
                        </h2>
                        <div className="grid gap-6 lg:grid-cols-2">
                            <div>
                                <div className="mb-3 flex items-center justify-between">
                                    <Label>{t('global.roles')}</Label>
                                    <button
                                        type="button"
                                        className="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                        onClick={() => toggleAll('roles', roleOptions)}
                                    >
                                        {t('global.select_all')}
                                    </button>
                                </div>
                                <SearchableMultiSelect
                                    values={data.roles}
                                    onChange={(values) => setData('roles', values)}
                                    options={roleOptions}
                                    placeholder={t('global.select')}
                                />
                            </div>
                            <div>
                                <div className="mb-3 flex items-center justify-between">
                                    <Label>{t('global.permissions')}</Label>
                                    <button
                                        type="button"
                                        className="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                        onClick={() => toggleAll('permissions', permissionOptions)}
                                    >
                                        {t('global.select_all')}
                                    </button>
                                </div>
                                <SearchableMultiSelect
                                    values={data.permissions}
                                    onChange={(values) => setData('permissions', values)}
                                    options={permissionOptions}
                                    placeholder={t('global.select')}
                                />
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end gap-2">
                        <Button color="light" type="button" as="a" href={urls.back} disabled={processing}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={processing}>
                            {processing ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </div>
                </div>
            </div>
        </form>
    );
}
