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
        branch_id: depot?.branch_id ? String(depot.branch_id) : '',
        department_id: depot?.department_id ? String(depot.department_id) : '',
        pharmacy_id: depot?.pharmacy_id ? String(depot.pharmacy_id) : '',
        parent_depot_id: depot?.parent_depot_id ? String(depot.parent_depot_id) : '',
        is_active: depot?.is_active ?? true,
        is_base: depot?.is_base ?? false,
        assignments: depot?.assignments?.length ? depot.assignments : [{ ...EMPTY_ASSIGNMENT }],
    });

    transform((payload) => ({
        name: payload.name,
        address: payload.address,
        branch_id: payload.branch_id || null,
        department_id: payload.department_id || null,
        pharmacy_id: payload.pharmacy_id || null,
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
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="depot-name">{t('global.depot.name')} *</Label>
                    <TextInput id="depot-name" required value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                </div>
                <div>
                    <Label htmlFor="depot-address">{t('global.depot.address')}</Label>
                    <TextInput id="depot-address" value={data.address} onChange={(e) => setData('address', e.target.value)} />
                </div>
                <div>
                    <Label>{t('global.depot.branch')}</Label>
                    <SearchableSelect
                        value={data.branch_id}
                        onChange={(value) => setData('branch_id', value)}
                        options={[{ value: '', label: t('global.select') }, ...formData.branches.map((b) => ({ value: String(b.id), label: b.name }))]}
                    />
                </div>
                <div>
                    <Label>{t('global.depot.department')}</Label>
                    <SearchableSelect
                        value={data.department_id}
                        onChange={(value) => setData('department_id', value)}
                        options={[{ value: '', label: t('global.select') }, ...formData.departments.map((b) => ({ value: String(b.id), label: b.name }))]}
                    />
                </div>
                <div>
                    <Label>{t('global.depot.pharmacy')}</Label>
                    <SearchableSelect
                        value={data.pharmacy_id}
                        onChange={(value) => setData('pharmacy_id', value)}
                        options={[{ value: '', label: t('global.select') }, ...formData.pharmacies.map((b) => ({ value: String(b.id), label: b.name }))]}
                    />
                </div>
                <div>
                    <Label>{t('global.depot.parent_depot')}</Label>
                    <SearchableSelect
                        value={data.parent_depot_id}
                        onChange={(value) => setData('parent_depot_id', value)}
                        options={[{ value: '', label: t('global.select') }, ...formData.depots.map((b) => ({ value: String(b.id), label: b.name }))]}
                    />
                </div>
            </div>

            <div className="flex flex-wrap gap-6">
                <label className="flex items-center gap-2">
                    <Checkbox checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                    <span>{t('global.depot.is_active')}</span>
                </label>
                <label className="flex items-center gap-2">
                    <Checkbox checked={data.is_base} onChange={(e) => setData('is_base', e.target.checked)} />
                    <span>{t('global.depot.is_base')}</span>
                </label>
            </div>

            <div className="space-y-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <div className="flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white">{t('global.users')}</h3>
                    <Button type="button" size="xs" color="light" onClick={() => setData('assignments', [...data.assignments, { ...EMPTY_ASSIGNMENT }])}>
                        <i className="bx bx-plus" />
                    </Button>
                </div>
                {data.assignments.map((assignment, index) => (
                    <div key={index} className="grid gap-3 md:grid-cols-[1fr_180px_auto]">
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
                            color="light"
                            disabled={data.assignments.length <= 1}
                            onClick={() => setData('assignments', data.assignments.filter((_, i) => i !== index))}
                        >
                            <i className="bx bx-trash" />
                        </Button>
                    </div>
                ))}
            </div>

            <div className="flex gap-2">
                <button type="submit" className={DEPOT_PRIMARY_BTN_CLASS} disabled={processing}>
                    {processing && <Spinner size="sm" className="me-2" />}
                    {t('global.save')}
                </button>
                <Button color="light" as={Link} href={urls.index}>
                    {t('global.cancel')}
                </Button>
            </div>
        </form>
    );
}
