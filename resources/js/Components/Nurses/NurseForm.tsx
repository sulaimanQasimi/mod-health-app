import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

export interface NurseFormValues {
    id?: number;
    first_name: string;
    last_name: string;
    gender: string;
    date_of_birth: string;
    phone: string;
    email: string;
    address: string;
    employee_id: string;
    department_id: string;
    branch_id: string;
    user_id: string;
    specialization: string;
    shift: string;
    employment_status: string;
    date_of_joining: string;
}

interface NurseUserOption {
    id: number;
    name: string;
    email: string;
}

interface NurseFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: {
        branches: OptionItem[];
        departments: OptionItem[];
        users: NurseUserOption[];
    };
    nurse?: NurseFormValues;
}

export default function NurseForm({ mode, urls, formData, nurse }: NurseFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        first_name: nurse?.first_name ?? '',
        last_name: nurse?.last_name ?? '',
        gender: nurse?.gender ?? '',
        date_of_birth: nurse?.date_of_birth ?? '',
        phone: nurse?.phone ?? '',
        email: nurse?.email ?? '',
        address: nurse?.address ?? '',
        employee_id: nurse?.employee_id ?? '',
        department_id: nurse?.department_id ?? '',
        branch_id: nurse?.branch_id ?? '',
        user_id: nurse?.user_id ?? '',
        specialization: nurse?.specialization ?? '',
        shift: nurse?.shift ?? '',
        employment_status: nurse?.employment_status ?? '',
        date_of_joining: nurse?.date_of_joining ?? '',
    });

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        const opts = { preserveScroll: true };
        if (isEdit) put(urls.update, opts);
        else post(urls.store, opts);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div>
                <h3 className="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {t('global.user_account')}
                </h3>
                <Label htmlFor="user_id">{t('global.link_to_user_account')}</Label>
                <SearchableSelect
                    value={data.user_id}
                    onChange={(value) => setData('user_id', value)}
                    options={formData.users.map((user) => ({
                        value: String(user.id),
                        label: `${user.name} (${user.email})`,
                    }))}
                    placeholder={t('global.nurse_without_login_access')}
                />
                {errors.user_id && <p className="mt-1 text-sm text-red-600">{errors.user_id}</p>}
            </div>

            <div>
                <h3 className="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {t('global.personal_information')}
                </h3>
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label htmlFor="first_name">{t('global.first_name')} *</Label>
                        <TextInput
                            id="first_name"
                            value={data.first_name}
                            onChange={(event) => setData('first_name', event.target.value)}
                            required
                        />
                        {errors.first_name && (
                            <p className="mt-1 text-sm text-red-600">{errors.first_name}</p>
                        )}
                    </div>
                    <div>
                        <Label htmlFor="last_name">{t('global.last_name')} *</Label>
                        <TextInput
                            id="last_name"
                            value={data.last_name}
                            onChange={(event) => setData('last_name', event.target.value)}
                            required
                        />
                        {errors.last_name && (
                            <p className="mt-1 text-sm text-red-600">{errors.last_name}</p>
                        )}
                    </div>
                    <div>
                        <Label htmlFor="gender">{t('global.gender')} *</Label>
                        <SearchableSelect
                            value={data.gender}
                            onChange={(value) => setData('gender', value)}
                        >
                            <option value="">{t('global.please_select')}</option>
                            <option value="male">{t('global.male')}</option>
                            <option value="female">{t('global.female')}</option>
                        </SearchableSelect>
                        {errors.gender && <p className="mt-1 text-sm text-red-600">{errors.gender}</p>}
                    </div>
                    <div>
                        <Label htmlFor="date_of_birth">{t('global.date_of_birth')}</Label>
                        <TextInput
                            id="date_of_birth"
                            value={data.date_of_birth}
                            onChange={(event) => setData('date_of_birth', event.target.value)}
                            placeholder="1403/01/01"
                        />
                        {errors.date_of_birth && (
                            <p className="mt-1 text-sm text-red-600">{errors.date_of_birth}</p>
                        )}
                    </div>
                </div>
            </div>

            <div>
                <h3 className="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {t('global.contact_information')}
                </h3>
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label htmlFor="phone">{t('global.phone')}</Label>
                        <TextInput
                            id="phone"
                            value={data.phone}
                            onChange={(event) => setData('phone', event.target.value)}
                        />
                        {errors.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
                    </div>
                    <div>
                        <Label htmlFor="email">{t('global.email')}</Label>
                        <TextInput
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                        />
                        {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                    </div>
                    <div className="md:col-span-2">
                        <Label htmlFor="address">{t('global.address')}</Label>
                        <Textarea
                            id="address"
                            rows={3}
                            value={data.address}
                            onChange={(event) => setData('address', event.target.value)}
                        />
                        {errors.address && <p className="mt-1 text-sm text-red-600">{errors.address}</p>}
                    </div>
                </div>
            </div>

            <div>
                <h3 className="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {t('global.employment_information')}
                </h3>
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label htmlFor="employee_id">{t('global.employee_id')} *</Label>
                        <TextInput
                            id="employee_id"
                            value={data.employee_id}
                            onChange={(event) => setData('employee_id', event.target.value)}
                            required
                        />
                        {errors.employee_id && (
                            <p className="mt-1 text-sm text-red-600">{errors.employee_id}</p>
                        )}
                    </div>
                    <div>
                        <Label htmlFor="branch_id">{t('global.branch')}</Label>
                        <SearchableSelect
                            value={data.branch_id}
                            onChange={(value) => setData('branch_id', value)}
                            options={formData.branches.map((branch) => ({
                                value: String(branch.id),
                                label: branch.name,
                            }))}
                            placeholder={t('global.select_branch')}
                        />
                        {errors.branch_id && (
                            <p className="mt-1 text-sm text-red-600">{errors.branch_id}</p>
                        )}
                    </div>
                    <div>
                        <Label htmlFor="department_id">{t('global.department')}</Label>
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
                    <div>
                        <Label htmlFor="specialization">{t('global.specialization')}</Label>
                        <TextInput
                            id="specialization"
                            value={data.specialization}
                            onChange={(event) => setData('specialization', event.target.value)}
                        />
                        {errors.specialization && (
                            <p className="mt-1 text-sm text-red-600">{errors.specialization}</p>
                        )}
                    </div>
                    <div>
                        <Label htmlFor="shift">{t('global.shift')} *</Label>
                        <SearchableSelect
                            value={data.shift}
                            onChange={(value) => setData('shift', value)}
                        >
                            <option value="">{t('global.please_select')}</option>
                            <option value="morning">{t('global.morning_shift')}</option>
                            <option value="evening">{t('global.evening_shift')}</option>
                            <option value="night">{t('global.night_shift')}</option>
                        </SearchableSelect>
                        {errors.shift && <p className="mt-1 text-sm text-red-600">{errors.shift}</p>}
                    </div>
                    <div>
                        <Label htmlFor="employment_status">{t('global.employment_status')} *</Label>
                        <SearchableSelect
                            value={data.employment_status}
                            onChange={(value) => setData('employment_status', value)}
                        >
                            <option value="">{t('global.please_select')}</option>
                            <option value="active">{t('global.active')}</option>
                            <option value="inactive">{t('global.inactive')}</option>
                            <option value="on_leave">{t('global.on_leave')}</option>
                        </SearchableSelect>
                        {errors.employment_status && (
                            <p className="mt-1 text-sm text-red-600">{errors.employment_status}</p>
                        )}
                    </div>
                    <div>
                        <Label htmlFor="date_of_joining">{t('global.date_of_joining')}</Label>
                        <TextInput
                            id="date_of_joining"
                            value={data.date_of_joining}
                            onChange={(event) => setData('date_of_joining', event.target.value)}
                            placeholder="1403/01/01"
                        />
                        {errors.date_of_joining && (
                            <p className="mt-1 text-sm text-red-600">{errors.date_of_joining}</p>
                        )}
                    </div>
                </div>
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
