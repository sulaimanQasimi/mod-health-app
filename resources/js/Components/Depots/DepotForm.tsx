import { Link, useForm } from '@inertiajs/react';
import { Button, Checkbox, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotDetail, DepotFormData, DepotUserAssignment } from '../../types/depot';
import { DEPOT_PRIMARY_BTN_CLASS } from './depotUi';

interface DepotFormProps {
    mode: 'create' | 'edit';
    formData: DepotFormData;
    depot?: DepotDetail;
    urls: { store?: string; update?: string; index: string };
}

const EMPTY_ASSIGNMENT: DepotUserAssignment = { user_id: '', role: 'staff' };

export default function DepotForm({ mode, formData, depot, urls }: DepotFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';

    const { data, setData, transform, post, put, processing, errors } = useForm({
        name: depot?.name ?? '',
        address: depot?.address ?? '',
        parent_depot_id: depot?.parent_depot_id ? String(depot.parent_depot_id) : '',
        is_active: depot?.is_active ?? true,
        is_base: depot?.is_base ?? false,
        assignments: depot?.assignments?.length ? depot.assignments : [{ ...EMPTY_ASSIGNMENT }],
    });

    transform((payload) => ({
        name: payload.name,
        address: payload.address || null,
        parent_depot_id: payload.parent_depot_id || null,
        is_active: payload.is_active,
        is_base: payload.is_base,
        user_ids: payload.assignments.filter((a) => a.user_id).map((a) => a.user_id),
        roles: payload.assignments.filter((a) => a.user_id).map((a) => a.role),
    }));

    const roleLabel = (role: string) => {
        if (role === 'manager') return t('global.manager');
        if (role === 'staff') return t('global.staff');
        if (role === 'procurement') return t('global.procurement');
        if (role === 'viewer') return t('global.viewer');
        return role;
    };

    const updateAssignment = (index: number, field: keyof DepotUserAssignment, value: string) => {
        setData(
            'assignments',
            data.assignments.map((assignment, i) =>
                i === index ? { ...assignment, [field]: value } : assignment,
            ),
        );
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const opts = { preserveScroll: true };
        if (isEdit && urls.update) put(urls.update, opts);
        else if (urls.store) post(urls.store, opts);
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <div className="grid gap-3 sm:grid-cols-2">
                <div>
                    <Label htmlFor="depot-name">{t('global.depot.name')} *</Label>
                    <TextInput
                        id="depot-name"
                        sizing="sm"
                        required
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder={t('global.depot.name')}
                    />
                    {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                </div>
                <div>
                    <Label htmlFor="depot-address">{t('global.depot.address')}</Label>
                    <TextInput
                        id="depot-address"
                        sizing="sm"
                        value={data.address}
                        onChange={(e) => setData('address', e.target.value)}
                        placeholder={t('global.depot.address')}
                    />
                </div>
                <div className="sm:col-span-2">
                    <Label>{t('global.depot.parent_depot')}</Label>
                    <SearchableSelect
                        value={data.parent_depot_id}
                        onChange={(value) => setData('parent_depot_id', value)}
                        options={[
                            { value: '', label: t('global.select') },
                            ...formData.depots.map((b) => ({ value: String(b.id), label: b.name })),
                        ]}
                    />
                    <p className="mt-1 text-xs text-gray-500">{t('global.depot.child')}</p>
                </div>
            </div>

            <div className="flex flex-wrap gap-4 rounded-lg border border-gray-200 bg-gray-50/80 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-800/40">
                <label className="flex items-center gap-2 text-sm">
                    <Checkbox checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                    <span>{t('global.depot.is_active')}</span>
                </label>
                <label className="flex items-center gap-2 text-sm">
                    <Checkbox checked={data.is_base} onChange={(e) => setData('is_base', e.target.checked)} />
                    <span>{t('global.depot.is_base')}</span>
                </label>
            </div>

            <div className="space-y-2.5 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <div className="flex items-center justify-between">
                    <div>
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">{t('global.users')}</h3>
                        <p className="text-xs text-gray-500">{t('global.depot.title')}</p>
                    </div>
                    <Button
                        type="button"
                        size="xs"
                        color="light"
                        onClick={() => setData('assignments', [...data.assignments, { ...EMPTY_ASSIGNMENT }])}
                    >
                        <i className="bx bx-plus" />
                    </Button>
                </div>
                {data.assignments.map((assignment, index) => (
                    <div key={index} className="grid gap-2 sm:grid-cols-[1fr_140px_auto]">
                        <SearchableSelect
                            value={assignment.user_id}
                            onChange={(value) => updateAssignment(index, 'user_id', value)}
                            options={[
                                { value: '', label: t('global.select') },
                                ...formData.users
                                    .filter((user) => !data.assignments.some((a, i) => i !== index && a.user_id === String(user.id)))
                                    .map((user) => ({ value: String(user.id), label: `${user.full_name} (${user.email})` })),
                            ]}
                        />
                        <SearchableSelect
                            value={assignment.role}
                            onChange={(value) => updateAssignment(index, 'role', value)}
                            options={formData.roles.map((role) => ({ value: role, label: roleLabel(role) }))}
                        />
                        <Button
                            type="button"
                            size="sm"
                            color="light"
                            disabled={data.assignments.length <= 1}
                            onClick={() => setData('assignments', data.assignments.filter((_, i) => i !== index))}
                        >
                            <i className="bx bx-trash" />
                        </Button>
                    </div>
                ))}
            </div>

            <div className="flex gap-2 pt-1">
                <button type="submit" className={DEPOT_PRIMARY_BTN_CLASS} disabled={processing}>
                    {processing && <Spinner size="sm" className="me-2" />}
                    {t('global.save')}
                </button>
                <Button color="light" size="sm" as={Link} href={urls.index}>
                    {t('global.cancel')}
                </Button>
            </div>
        </form>
    );
}
