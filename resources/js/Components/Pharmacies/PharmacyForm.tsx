import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export interface PharmacyUserAssignment {
    user_id: string;
    role: string;
}

interface PharmacyUserOption {
    id: number;
    full_name: string;
    email: string;
}

interface PharmacyFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { users: PharmacyUserOption[]; roles: string[] };
    pharmacy?: {
        id: number;
        name: string;
        phone: string;
        address: string;
        assignments: PharmacyUserAssignment[];
    };
}

const EMPTY_ASSIGNMENT: PharmacyUserAssignment = { user_id: '', role: 'staff' };

export default function PharmacyForm({ mode, urls, formData, pharmacy }: PharmacyFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';

    const { data, setData, transform, post, put, processing, errors } = useForm({
        name: pharmacy?.name ?? '',
        phone: pharmacy?.phone ?? '',
        address: pharmacy?.address ?? '',
        assignments:
            pharmacy?.assignments?.length ? pharmacy.assignments : [{ ...EMPTY_ASSIGNMENT }],
    });

    transform((formData) => ({
        name: formData.name,
        phone: formData.phone,
        address: formData.address,
        user_ids: formData.assignments
            .filter((assignment) => assignment.user_id)
            .map((assignment) => assignment.user_id),
        roles: formData.assignments
            .filter((assignment) => assignment.user_id)
            .map((assignment) => assignment.role),
    }));

    const roleLabel = (role: string) => {
        if (role === 'manager') return t('global.manager');
        if (role === 'staff') return t('global.staff');
        if (role === 'procurement') return t('global.procurement');
        if (role === 'viewer') return t('global.viewer');
        return role;
    };

    const updateAssignment = (index: number, field: keyof PharmacyUserAssignment, value: string) => {
        const next = data.assignments.map((assignment, i) =>
            i === index ? { ...assignment, [field]: value } : assignment,
        );
        setData('assignments', next);
    };

    const addAssignment = () => {
        setData('assignments', [...data.assignments, { ...EMPTY_ASSIGNMENT }]);
    };

    const removeAssignment = (index: number) => {
        if (data.assignments.length <= 1) return;
        setData(
            'assignments',
            data.assignments.filter((_, i) => i !== index),
        );
    };

    const usedUserIds = (currentIndex: number) =>
        data.assignments
            .filter((_, i) => i !== currentIndex)
            .map((assignment) => assignment.user_id)
            .filter(Boolean);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const opts = { preserveScroll: true };
        if (isEdit) put(urls.update, opts);
        else post(urls.store, opts);
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="name">{t('global.pharmacy_name')} *</Label>
                    <TextInput
                        id="name"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        placeholder={t('global.enter_pharmacy_name')}
                        required
                    />
                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                </div>
                <div>
                    <Label htmlFor="phone">{t('global.pharmacy_phone')} *</Label>
                    <TextInput
                        id="phone"
                        value={data.phone}
                        onChange={(event) => setData('phone', event.target.value)}
                        placeholder={t('global.enter_phone_number')}
                        required
                    />
                    {errors.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
                </div>
            </div>

            <div>
                <Label htmlFor="address">{t('global.pharmacy_address')} *</Label>
                <Textarea
                    id="address"
                    rows={3}
                    value={data.address}
                    onChange={(event) => setData('address', event.target.value)}
                    placeholder={t('global.enter_pharmacy_address')}
                    required
                />
                {errors.address && <p className="mt-1 text-sm text-red-600">{errors.address}</p>}
            </div>

            <div className="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <div className="mb-4 flex items-center justify-between">
                    <div>
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.pharmacy_users')} *
                        </h3>
                        <p className="text-xs text-gray-500">{t('global.select_user_description')}</p>
                    </div>
                    <Button type="button" color="light" size="sm" onClick={addAssignment}>
                        <i className="bx bx-plus me-1" />
                        {t('global.add_user')}
                    </Button>
                </div>

                <div className="space-y-3">
                    {data.assignments.map((assignment, index) => (
                        <div
                            key={`assignment-${index}`}
                            className="grid gap-3 rounded-lg border border-gray-100 bg-gray-50/50 p-3 md:grid-cols-[1fr_180px_auto] dark:border-gray-700 dark:bg-gray-800/30"
                        >
                            <div>
                                <Label>{t('global.select_user')}</Label>
                                <SearchableSelect
                                    value={assignment.user_id}
                                    onChange={(value) => updateAssignment(index, 'user_id', value)}
                                    options={formData.users
                                        .filter(
                                            (user) =>
                                                String(user.id) === assignment.user_id ||
                                                !usedUserIds(index).includes(String(user.id)),
                                        )
                                        .map((user) => ({
                                            value: String(user.id),
                                            label: `${user.full_name} (${user.email})`,
                                        }))}
                                    placeholder={t('global.select_user')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.role')}</Label>
                                <SearchableSelect
                                    value={assignment.role}
                                    onChange={(value) => updateAssignment(index, 'role', value)}
                                >
                                    {formData.roles.map((role) => (
                                        <option key={role} value={role}>
                                            {roleLabel(role)}
                                        </option>
                                    ))}
                                </SearchableSelect>
                            </div>
                            <div className="flex items-end">
                                <Button
                                    type="button"
                                    color="light"
                                    disabled={data.assignments.length <= 1}
                                    onClick={() => removeAssignment(index)}
                                    className="w-full"
                                >
                                    <i className="bx bx-trash text-lg text-red-600" />
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>
                {(errors.user_ids || errors.roles) && (
                    <p className="mt-2 text-sm text-red-600">
                        {errors.user_ids ?? errors.roles}
                    </p>
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
