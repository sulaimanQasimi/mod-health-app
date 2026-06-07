import { useForm } from '@inertiajs/react';
import { Button, Checkbox, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent, useMemo } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { DoctorFormData, DoctorFormUrls, DoctorFormValues } from '../../types/doctor';

interface DoctorFormProps {
    mode: 'create' | 'edit';
    formData: DoctorFormData;
    urls: DoctorFormUrls;
    doctor?: DoctorFormValues;
}

export default function DoctorForm({ mode, formData, urls, doctor }: DoctorFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';

    const { data, setData, post, put, processing, errors } = useForm({
        name: doctor?.name ?? '',
        father_name: doctor?.father_name ?? '',
        gender: doctor?.gender ?? '',
        contact_number: doctor?.contact_number ?? '',
        address: doctor?.address ?? '',
        specialization: doctor?.specialization ?? '',
        qualification: doctor?.qualification ?? '',
        room_no: doctor?.room_no ?? '',
        clinic_type: doctor?.clinic_type ?? '',
        join_date: doctor?.join_date ?? '',
        department_id: doctor?.department_id ?? '',
        user_id: doctor?.user_id ?? '',
        active_status: doctor?.active_status ?? true,
        is_dentist: doctor?.is_dentist ?? false,
        is_nephrologist: doctor?.is_nephrologist ?? false,
    });

    const departmentOptions = useMemo(
        () =>
            formData.departments.map((department) => ({
                value: String(department.id),
                label: department.name,
            })),
        [formData.departments],
    );

    const userOptions = useMemo(
        () =>
            formData.doctorUsers.map((user) => ({
                value: String(user.id),
                label: `${user.name}${user.last_name ? ` ${user.last_name}` : ''} (${user.email})`,
            })),
        [formData.doctorUsers],
    );

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
        };

        if (isEdit) {
            put(urls.update, options);
            return;
        }

        post(urls.store, options);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="name">
                        {t('global.name')} <span className="text-red-500">*</span>
                    </Label>
                    <TextInput
                        id="name"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        color={errors.name ? 'failure' : undefined}
                        required
                    />
                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                </div>

                <div>
                    <Label htmlFor="father_name">{t('global.father_name')}</Label>
                    <TextInput
                        id="father_name"
                        value={data.father_name}
                        onChange={(event) => setData('father_name', event.target.value)}
                        placeholder={t('global.enter_father_name')}
                        color={errors.father_name ? 'failure' : undefined}
                    />
                    {errors.father_name && (
                        <p className="mt-1 text-sm text-red-600">{errors.father_name}</p>
                    )}
                </div>

                <div>
                    <Label htmlFor="gender">
                        {t('global.gender')} <span className="text-red-500">*</span>
                    </Label>
                    <SearchableSelect
                        value={data.gender}
                        onChange={(value) => setData('gender', value)}
                        options={formData.genders.map((gender) => ({
                            value: gender.value,
                            label: t(gender.label_key),
                        }))}
                        placeholder={t('global.select')}
                    />
                    {errors.gender && <p className="mt-1 text-sm text-red-600">{errors.gender}</p>}
                </div>

                <div>
                    <Label htmlFor="contact_number">
                        {t('global.contact_number')} <span className="text-red-500">*</span>
                    </Label>
                    <TextInput
                        id="contact_number"
                        value={data.contact_number}
                        onChange={(event) => setData('contact_number', event.target.value)}
                        placeholder={t('global.phone')}
                        color={errors.contact_number ? 'failure' : undefined}
                        required
                    />
                    {errors.contact_number && (
                        <p className="mt-1 text-sm text-red-600">{errors.contact_number}</p>
                    )}
                </div>

                <div className="md:col-span-2">
                    <Label htmlFor="address">{t('global.address')}</Label>
                    <Textarea
                        id="address"
                        rows={3}
                        value={data.address}
                        onChange={(event) => setData('address', event.target.value)}
                        placeholder={t('global.enter_full_address')}
                        color={errors.address ? 'failure' : undefined}
                    />
                    {errors.address && <p className="mt-1 text-sm text-red-600">{errors.address}</p>}
                </div>

                <div>
                    <Label htmlFor="specialization">{t('global.specialization')}</Label>
                    <TextInput
                        id="specialization"
                        value={data.specialization}
                        onChange={(event) => setData('specialization', event.target.value)}
                        placeholder={t('global.example_specialization')}
                        color={errors.specialization ? 'failure' : undefined}
                    />
                    {errors.specialization && (
                        <p className="mt-1 text-sm text-red-600">{errors.specialization}</p>
                    )}
                </div>

                <div>
                    <Label htmlFor="qualification">{t('global.qualification')}</Label>
                    <TextInput
                        id="qualification"
                        value={data.qualification}
                        onChange={(event) => setData('qualification', event.target.value)}
                        placeholder={t('global.example_qualification')}
                        color={errors.qualification ? 'failure' : undefined}
                    />
                    {errors.qualification && (
                        <p className="mt-1 text-sm text-red-600">{errors.qualification}</p>
                    )}
                </div>

                <div>
                    <Label htmlFor="room_no">{t('global.room_no')}</Label>
                    <TextInput
                        id="room_no"
                        value={data.room_no}
                        onChange={(event) => setData('room_no', event.target.value)}
                        placeholder={t('global.room_number')}
                        color={errors.room_no ? 'failure' : undefined}
                    />
                    {errors.room_no && <p className="mt-1 text-sm text-red-600">{errors.room_no}</p>}
                </div>

                <div>
                    <Label htmlFor="clinic_type">{t('global.clinic_type')}</Label>
                    <SearchableSelect
                        value={data.clinic_type}
                        onChange={(value) => setData('clinic_type', value)}
                        options={formData.clinicTypes.map((type) => ({
                            value: type.value,
                            label: t(type.label_key),
                        }))}
                        placeholder={t('global.select')}
                    />
                    {errors.clinic_type && (
                        <p className="mt-1 text-sm text-red-600">{errors.clinic_type}</p>
                    )}
                </div>

                <div>
                    <Label htmlFor="join_date">{t('global.join_date')}</Label>
                    <TextInput
                        id="join_date"
                        value={data.join_date}
                        onChange={(event) => setData('join_date', event.target.value)}
                        placeholder="1403/01/01"
                        color={errors.join_date ? 'failure' : undefined}
                    />
                    {errors.join_date && (
                        <p className="mt-1 text-sm text-red-600">{errors.join_date}</p>
                    )}
                </div>

                <div>
                    <Label htmlFor="department_id">
                        {t('global.department')} <span className="text-red-500">*</span>
                    </Label>
                    <SearchableSelect
                        value={data.department_id}
                        onChange={(value) => setData('department_id', value)}
                        options={departmentOptions}
                        placeholder={t('global.select')}
                    />
                    {errors.department_id && (
                        <p className="mt-1 text-sm text-red-600">{errors.department_id}</p>
                    )}
                </div>

                <div>
                    <Label htmlFor="user_id">{t('global.user')}</Label>
                    <SearchableSelect
                        value={data.user_id}
                        onChange={(value) => setData('user_id', value)}
                        options={userOptions}
                        placeholder={t('global.select')}
                    />
                    {errors.user_id && <p className="mt-1 text-sm text-red-600">{errors.user_id}</p>}
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-3">
                <div className="flex items-center gap-2">
                    <Checkbox
                        id="active_status"
                        checked={data.active_status}
                        onChange={(event) => setData('active_status', event.target.checked)}
                    />
                    <Label htmlFor="active_status">{t('global.active_status')}</Label>
                </div>
                <div className="flex items-center gap-2">
                    <Checkbox
                        id="is_dentist"
                        checked={data.is_dentist}
                        onChange={(event) => setData('is_dentist', event.target.checked)}
                    />
                    <Label htmlFor="is_dentist">{t('global.is_dentist')}</Label>
                </div>
                <div className="flex items-center gap-2">
                    <Checkbox
                        id="is_nephrologist"
                        checked={data.is_nephrologist}
                        onChange={(event) => setData('is_nephrologist', event.target.checked)}
                    />
                    <Label htmlFor="is_nephrologist">
                        {t('global.is_nephrologist') || t('global.nephrology')}
                    </Label>
                </div>
            </div>

            <div className="flex flex-wrap justify-end gap-2 border-t border-gray-200 pt-6 dark:border-gray-700">
                <Button color="light" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" disabled={processing}>
                    {processing ? (
                        <Spinner size="sm" />
                    ) : isEdit ? (
                        t('global.update')
                    ) : (
                        t('global.create')
                    )}
                </Button>
            </div>
        </form>
    );
}
