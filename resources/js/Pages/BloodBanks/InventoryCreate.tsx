import { Head, Link, useForm } from '@inertiajs/react';
import { Alert, Button, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, ReactNode } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import BloodFormSegmented from '../../Components/BloodBanks/BloodFormSegmented';
import { BLOOD_BANK_PRIMARY_BTN_CLASS } from '../../Components/BloodBanks/bloodBankUi';
import FormSection from '../../Components/Patients/ui/FormSection';
import { FormField, GridDivider, IconTextInput } from '../../Components/Patients/ui/FormField';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import PersianDateTimeField from '../../Components/ui/PersianDateTimeField';
import { useTranslation } from '../../hooks/useTranslation';
import { BloodBankListUrls, BloodUnitReceiveForm } from '../../types/bloodBank';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface InventoryCreateProps {
    departments: { id: number; name: string }[];
    filterOptions: {
        bloodGroups: string[];
        bloodComponentTypes: string[];
    };
    urls: BloodBankListUrls & { back: string; store: string };
}

const EMPTY_FORM: BloodUnitReceiveForm = {
    donor_record_department: false,
    department_id: '',
    donor_name: '',
    donor_father_name: '',
    donor_age: '',
    donor_gender: '',
    donor_phone: '',
    donor_national_id: '',
    donor_blood_pressure: '',
    donor_type: 'civilian',
    donor_military_department: '',
    donor_comorbidities: '',
    donor_receiver: '',
    phlebotomy_date: '',
    phlebotomy_time: '',
    blood_group: 'A',
    rh: '+',
    component_type: 'Fresh',
    bag_number: '',
    volume_ml: '',
    collected_date: '',
    collected_time: '',
    expires_date: '',
    expires_time: '23:59',
    notes: '',
};

function FormCard({ children }: { children: ReactNode }) {
    return (
        <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            {children}
        </div>
    );
}

export default function BloodBanksInventoryCreate({ departments, filterOptions, urls }: InventoryCreateProps) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm<BloodUnitReceiveForm>(EMPTY_FORM);

    const showDepartmentSelect = data.donor_record_department;
    const showDonorDetails =
        !showDepartmentSelect || (showDepartmentSelect && data.department_id !== '');
    const showMilitaryDepartment = data.donor_type === 'military';
    const hasErrors = Object.keys(errors).length > 0;

    const setDonorType = (type: string) => {
        setData((prev) => ({
            ...prev,
            donor_type: type,
            donor_military_department: type === 'military' ? prev.donor_military_department : '',
        }));
    };

    const toggleRecordDepartment = () => {
        setData((prev) => ({
            ...prev,
            donor_record_department: !prev.donor_record_department,
            department_id: !prev.donor_record_department ? prev.department_id : '',
        }));
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(urls.store, {
            preserveScroll: true,
            transform: (form) => ({
                ...form,
                donor_record_department: form.donor_record_department ? 1 : 0,
                donor_age: form.donor_age === '' ? null : form.donor_age,
                volume_ml: form.volume_ml === '' ? null : form.volume_ml,
                donor_gender: form.donor_gender === '' ? null : form.donor_gender,
            }),
        });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.add_blood_manually')} />

            <div className={`mx-auto space-y-5 pb-24 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.add_blood_manually')}
                    subtitle={t('global.blood_inventory')}
                    icon="bx-plus-circle"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                />

                <BloodBankNavTabs active="inventory" urls={urls} />

                {hasErrors && (
                    <Alert color="failure" icon={() => <i className="bx bx-error-circle text-lg" />}>
                        <span className="font-medium">{t('global.validation_errors')}</span>
                        <ul className="mt-2 list-inside list-disc text-sm">
                            {Object.values(errors).map((message, index) => (
                                <li key={index}>{message}</li>
                            ))}
                        </ul>
                    </Alert>
                )}

                <form onSubmit={submit} className="space-y-5">
                    <FormCard>
                        <div className="border-b border-gray-100 bg-gradient-to-r from-rose-50 to-red-50 px-6 py-4 dark:border-gray-800 dark:from-rose-950/40 dark:to-red-950/30">
                            <div className="flex items-center gap-3">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-rose-600 shadow-sm dark:bg-gray-900">
                                    <i className="bx bx-user text-xl" />
                                </span>
                                <div>
                                    <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                                        {t('global.donor_phlebotomy_section')}
                                    </h2>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        {t('global.blood_donor_no_patient_department_hint')}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="space-y-6 p-6">
                            <button
                                type="button"
                                onClick={toggleRecordDepartment}
                                className={`flex w-full items-start gap-4 rounded-xl border p-4 text-start transition ${
                                    data.donor_record_department
                                        ? 'border-rose-300 bg-rose-50/80 ring-1 ring-rose-200 dark:border-rose-800 dark:bg-rose-950/30 dark:ring-rose-900'
                                        : 'border-gray-200 hover:border-rose-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:border-rose-900/40 dark:hover:bg-gray-800/50'
                                }`}
                            >
                                <span
                                    className={`mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md border transition ${
                                        data.donor_record_department
                                            ? 'border-rose-500 bg-rose-600 text-white'
                                            : 'border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800'
                                    }`}
                                >
                                    {data.donor_record_department && <i className="bx bx-check text-sm" />}
                                </span>
                                <span>
                                    <span className="block font-medium text-gray-900 dark:text-white">
                                        {t('global.blood_donor_record_department')}
                                    </span>
                                    <span className="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                                        {t('global.blood_donor_no_patient_department_hint')}
                                    </span>
                                </span>
                            </button>

                            {showDepartmentSelect && (
                                <FormField label={t('global.department')} icon="bx-buildings" error={errors.department_id}>
                                    <SearchableSelect
                                        value={data.department_id}
                                        onChange={(value) => setData('department_id', value)}
                                        options={[
                                            { value: '', label: t('global.select') },
                                            ...departments.map((d) => ({
                                                value: String(d.id),
                                                label: d.name,
                                            })),
                                        ]}
                                        placeholder={t('global.select')}
                                    />
                                </FormField>
                            )}

                            {showDepartmentSelect && !data.department_id && (
                                <p className="rounded-lg border border-dashed border-amber-200 bg-amber-50/60 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/20 dark:text-amber-200">
                                    <i className="bx bx-info-circle me-1" />
                                    {t('global.blood_donor_no_patient_department_hint')}
                                </p>
                            )}

                            {showDonorDetails && (
                                <>
                                    <FormSection
                                        icon="bx-id-card"
                                        title={t('global.donor_and_sample')}
                                        accent="rose"
                                        isFirst
                                        className="!mt-0 !border-0 !pt-0"
                                    >
                                        <FormField label={t('global.donor_name')} icon="bx-user" error={errors.donor_name}>
                                            <IconTextInput
                                                id="donor_name"
                                                icon="bx-user"
                                                value={data.donor_name}
                                                onChange={(value) => setData('donor_name', value)}
                                                placeholder={t('global.optional')}
                                            />
                                        </FormField>

                                        <FormField label={t('global.father_name')} icon="bx-male" error={errors.donor_father_name}>
                                            <IconTextInput
                                                id="donor_father_name"
                                                icon="bx-male"
                                                value={data.donor_father_name}
                                                onChange={(value) => setData('donor_father_name', value)}
                                                placeholder={t('global.optional')}
                                            />
                                        </FormField>

                                        <FormField label={t('global.age')} icon="bx-calendar" error={errors.donor_age}>
                                            <IconTextInput
                                                id="donor_age"
                                                icon="bx-calendar"
                                                type="number"
                                                min={0}
                                                max={130}
                                                value={data.donor_age}
                                                onChange={(value) => setData('donor_age', value)}
                                            />
                                        </FormField>

                                        <FormField
                                            label={t('global.gender')}
                                            icon="bx-male-female"
                                            error={errors.donor_gender}
                                            className="md:col-span-2 xl:col-span-1"
                                        >
                                            <BloodFormSegmented
                                                value={data.donor_gender}
                                                onChange={(value) => setData('donor_gender', value)}
                                                allowEmpty
                                                options={[
                                                    { value: 'male', label: t('global.male'), icon: 'bx-male' },
                                                    { value: 'female', label: t('global.female'), icon: 'bx-female' },
                                                ]}
                                            />
                                        </FormField>

                                        <FormField label={t('global.phone')} icon="bx-phone" error={errors.donor_phone}>
                                            <IconTextInput
                                                id="donor_phone"
                                                icon="bx-phone"
                                                value={data.donor_phone}
                                                onChange={(value) => setData('donor_phone', value)}
                                            />
                                        </FormField>

                                        <FormField
                                            label={t('global.national_id')}
                                            icon="bx-id-card"
                                            error={errors.donor_national_id}
                                        >
                                            <IconTextInput
                                                id="donor_national_id"
                                                icon="bx-id-card"
                                                value={data.donor_national_id}
                                                onChange={(value) => setData('donor_national_id', value)}
                                            />
                                        </FormField>

                                        <FormField
                                            label={t('global.blood_pressure')}
                                            icon="bx-pulse"
                                            error={errors.donor_blood_pressure}
                                        >
                                            <IconTextInput
                                                id="donor_blood_pressure"
                                                icon="bx-pulse"
                                                value={data.donor_blood_pressure}
                                                onChange={(value) => setData('donor_blood_pressure', value)}
                                                placeholder="120/80"
                                            />
                                        </FormField>

                                        <FormField
                                            label={t('global.donor_type')}
                                            icon="bx-group"
                                            error={errors.donor_type}
                                            className="md:col-span-2"
                                        >
                                            <BloodFormSegmented
                                                value={data.donor_type}
                                                onChange={setDonorType}
                                                options={[
                                                    {
                                                        value: 'civilian',
                                                        label: t('global.civilian'),
                                                        icon: 'bx-user',
                                                    },
                                                    {
                                                        value: 'military',
                                                        label: t('global.military'),
                                                        icon: 'bx-shield-quarter',
                                                    },
                                                ]}
                                            />
                                        </FormField>

                                        {showMilitaryDepartment && (
                                            <FormField
                                                label={t('global.military_department')}
                                                icon="bx-buildings"
                                                required
                                                error={errors.donor_military_department}
                                                className="md:col-span-2"
                                            >
                                                <IconTextInput
                                                    id="donor_military_department"
                                                    icon="bx-buildings"
                                                    value={data.donor_military_department}
                                                    onChange={(value) => setData('donor_military_department', value)}
                                                    required
                                                />
                                            </FormField>
                                        )}

                                        <FormField
                                            label={t('global.receiver')}
                                            icon="bx-user-check"
                                            error={errors.donor_receiver}
                                        >
                                            <IconTextInput
                                                id="donor_receiver"
                                                icon="bx-user-check"
                                                value={data.donor_receiver}
                                                onChange={(value) => setData('donor_receiver', value)}
                                                placeholder={t('global.optional')}
                                            />
                                        </FormField>

                                        <FormField
                                            label={t('global.phlebotomy_at')}
                                            icon="bx-time"
                                            hint={t('global.phlebotomy_at_hint')}
                                            error={errors.phlebotomy_date ?? errors.phlebotomy_time}
                                            className="md:col-span-2"
                                        >
                                            <PersianDateTimeField
                                                dateValue={data.phlebotomy_date}
                                                timeValue={data.phlebotomy_time}
                                                onDateChange={(value) => setData('phlebotomy_date', value)}
                                                onTimeChange={(value) => setData('phlebotomy_time', value)}
                                                timeHint={t('global.optional')}
                                            />
                                        </FormField>

                                        <div className="col-span-full">
                                            <FormField
                                                label={t('global.comorbidities')}
                                                icon="bx-note"
                                                error={errors.donor_comorbidities}
                                            >
                                                <Textarea
                                                    rows={2}
                                                    value={data.donor_comorbidities}
                                                    onChange={(e) => setData('donor_comorbidities', e.target.value)}
                                                />
                                            </FormField>
                                        </div>
                                    </FormSection>
                                </>
                            )}
                        </div>
                    </FormCard>

                    <FormCard>
                        <div className="border-b border-gray-100 bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 dark:border-gray-800 dark:from-red-950/40 dark:to-rose-950/30">
                            <div className="flex items-center gap-3">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-red-600 shadow-sm dark:bg-gray-900">
                                    <i className="bx bx-donate-blood text-xl" />
                                </span>
                                <div>
                                    <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                                        {t('global.unit_details')}
                                    </h2>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        {t('global.bag_number')} · {t('global.blood_group')} · {t('global.expires_at')}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="p-6">
                            <FormSection
                                icon="bx-test-tube"
                                title={t('global.blood_group')}
                                accent="rose"
                                isFirst
                                className="!mt-0 !border-0 !pt-0"
                            >
                                <FormField
                                    label={t('global.blood_group')}
                                    required
                                    error={errors.blood_group}
                                    className="col-span-full"
                                >
                                    <BloodFormSegmented
                                        value={data.blood_group}
                                        onChange={(value) => setData('blood_group', value)}
                                        columns={4}
                                        options={filterOptions.bloodGroups.map((group) => ({
                                            value: group,
                                            label: group,
                                            icon: 'bx-droplet',
                                        }))}
                                    />
                                </FormField>

                                <FormField label={t('global.blood_rh')} required error={errors.rh}>
                                    <BloodFormSegmented
                                        value={data.rh}
                                        onChange={(value) => setData('rh', value)}
                                        options={[
                                            { value: '+', label: 'Rh+', icon: 'bx-plus-medical' },
                                            { value: '-', label: 'Rh−', icon: 'bx-minus' },
                                        ]}
                                    />
                                </FormField>

                                <FormField
                                    label={t('global.component_type')}
                                    icon="bx-cylinder"
                                    required
                                    error={errors.component_type}
                                >
                                    <SearchableSelect
                                        value={data.component_type}
                                        onChange={(value) => setData('component_type', value)}
                                        options={filterOptions.bloodComponentTypes.map((type) => ({
                                            value: type,
                                            label: type,
                                        }))}
                                        placeholder={t('global.select')}
                                        required
                                    />
                                </FormField>

                                <FormField
                                    label={t('global.bag_number')}
                                    icon="bx-barcode"
                                    required
                                    error={errors.bag_number}
                                >
                                    <IconTextInput
                                        id="bag_number"
                                        icon="bx-barcode"
                                        value={data.bag_number}
                                        onChange={(value) => setData('bag_number', value)}
                                        required
                                    />
                                </FormField>

                                <GridDivider icon="bx-detail" title={t('global.dates')} />

                                <FormField label={t('global.volume_ml')} icon="bx-cylinder" error={errors.volume_ml}>
                                    <IconTextInput
                                        id="volume_ml"
                                        icon="bx-cylinder"
                                        type="number"
                                        min={1}
                                        value={data.volume_ml}
                                        onChange={(value) => setData('volume_ml', value)}
                                        placeholder="ml"
                                    />
                                </FormField>

                                <FormField
                                    label={t('global.collected_at')}
                                    icon="bx-calendar"
                                    error={errors.collected_date ?? errors.collected_time}
                                    className="md:col-span-2"
                                >
                                    <PersianDateTimeField
                                        dateValue={data.collected_date}
                                        timeValue={data.collected_time}
                                        onDateChange={(value) => setData('collected_date', value)}
                                        onTimeChange={(value) => setData('collected_time', value)}
                                        timeHint={t('global.optional')}
                                    />
                                </FormField>

                                <FormField
                                    label={t('global.expires_at')}
                                    icon="bx-calendar-exclamation"
                                    required
                                    error={errors.expires_date ?? errors.expires_time}
                                    className="md:col-span-2"
                                >
                                    <PersianDateTimeField
                                        dateValue={data.expires_date}
                                        timeValue={data.expires_time}
                                        onDateChange={(value) => setData('expires_date', value)}
                                        onTimeChange={(value) => setData('expires_time', value)}
                                        dateRequired
                                        timeHint={t('global.expires_time_default_hint')}
                                    />
                                </FormField>

                                <div className="col-span-full">
                                    <FormField label={t('global.notes')} icon="bx-note" error={errors.notes}>
                                        <Textarea
                                            rows={2}
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                        />
                                    </FormField>
                                </div>
                            </FormSection>
                        </div>
                    </FormCard>

                    <div className="sticky bottom-0 z-10 -mx-1 rounded-2xl border border-gray-200 bg-white/95 px-4 py-4 shadow-lg backdrop-blur dark:border-gray-700 dark:bg-gray-900/95">
                        <div className="flex flex-wrap items-center justify-end gap-3">
                            <Button as={Link} href={urls.back} color="light" disabled={processing}>
                                {t('global.cancel')}
                            </Button>
                            <button type="submit" className={BLOOD_BANK_PRIMARY_BTN_CLASS} disabled={processing}>
                                {processing ? <Spinner size="sm" /> : <i className="bx bx-save text-lg" />}
                                {t('global.save')}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
