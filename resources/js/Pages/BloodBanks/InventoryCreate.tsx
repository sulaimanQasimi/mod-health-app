import { Head, Link, useForm } from '@inertiajs/react';
import { Button, Checkbox, Label, Select, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import { BLOOD_BANK_PANEL_ICON_CLASS } from '../../Components/BloodBanks/bloodBankUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import PersianDateInput from '../../Components/ui/PersianDateInput';
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
    phlebotomy_at: '',
    blood_group: 'A',
    rh: '+',
    component_type: 'Fresh',
    bag_number: '',
    volume_ml: '',
    collected_at: '',
    expires_date: '',
    expires_time: '23:59',
    notes: '',
};

function FieldError({ message }: { message?: string }) {
    if (!message) return null;
    return <p className="mt-1 text-sm text-red-600">{message}</p>;
}

export default function BloodBanksInventoryCreate({ departments, filterOptions, urls }: InventoryCreateProps) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm<BloodUnitReceiveForm>(EMPTY_FORM);

    const showDepartmentSelect = data.donor_record_department;
    const showDonorDetails =
        !showDepartmentSelect || (showDepartmentSelect && data.department_id !== '');
    const showMilitaryDepartment = data.donor_type === 'military';

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(urls.store, { preserveScroll: true });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.add_blood_manually')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.add_blood_manually')}
                    subtitle={t('global.blood_inventory')}
                    icon="bx-plus-circle"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                />

                <BloodBankNavTabs active="inventory" urls={urls} />

                <form onSubmit={submit} className="space-y-5">
                    <IcuPanel
                        variant="table"
                        title={t('global.blood_donor')}
                        icon="bx-user"
                        iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                    >
                        <div className="space-y-4">
                            <div className="flex items-start gap-3">
                                <Checkbox
                                    id="donor-record-department"
                                    checked={data.donor_record_department}
                                    onChange={(e) =>
                                        setData((prev) => ({
                                            ...prev,
                                            donor_record_department: e.target.checked,
                                            department_id: e.target.checked ? prev.department_id : '',
                                        }))
                                    }
                                />
                                <div>
                                    <Label htmlFor="donor-record-department" className="cursor-pointer">
                                        {t('global.blood_donor_record_department')}
                                    </Label>
                                    <p className="text-sm text-gray-500">
                                        {t('global.blood_donor_no_patient_department_hint')}
                                    </p>
                                </div>
                            </div>

                            {showDepartmentSelect && (
                                <div>
                                    <Label className="mb-2 block">{t('global.department')}</Label>
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
                                    />
                                    <FieldError message={errors.department_id} />
                                </div>
                            )}

                            {showDonorDetails && (
                                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <Label>{t('global.donor_name')}</Label>
                                        <TextInput
                                            value={data.donor_name}
                                            onChange={(e) => setData('donor_name', e.target.value)}
                                            placeholder={t('global.optional')}
                                        />
                                        <FieldError message={errors.donor_name} />
                                    </div>
                                    <div>
                                        <Label>{t('global.father_name')}</Label>
                                        <TextInput
                                            value={data.donor_father_name}
                                            onChange={(e) => setData('donor_father_name', e.target.value)}
                                            placeholder={t('global.optional')}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.age')}</Label>
                                        <TextInput
                                            type="number"
                                            min={0}
                                            max={130}
                                            value={data.donor_age}
                                            onChange={(e) => setData('donor_age', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.gender')}</Label>
                                        <Select
                                            value={data.donor_gender}
                                            onChange={(e) => setData('donor_gender', e.target.value)}
                                        >
                                            <option value="">{t('global.select')}</option>
                                            <option value="male">{t('global.male')}</option>
                                            <option value="female">{t('global.female')}</option>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label>{t('global.phone')}</Label>
                                        <TextInput
                                            value={data.donor_phone}
                                            onChange={(e) => setData('donor_phone', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.national_id')}</Label>
                                        <TextInput
                                            value={data.donor_national_id}
                                            onChange={(e) => setData('donor_national_id', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.blood_pressure')}</Label>
                                        <TextInput
                                            value={data.donor_blood_pressure}
                                            onChange={(e) => setData('donor_blood_pressure', e.target.value)}
                                            placeholder="120/80"
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.donor_type')}</Label>
                                        <Select
                                            value={data.donor_type}
                                            onChange={(e) => setData('donor_type', e.target.value)}
                                        >
                                            <option value="civilian">{t('global.civilian')}</option>
                                            <option value="military">{t('global.military')}</option>
                                        </Select>
                                    </div>
                                    {showMilitaryDepartment && (
                                        <div>
                                            <Label>{t('global.military_department')}</Label>
                                            <TextInput
                                                value={data.donor_military_department}
                                                onChange={(e) =>
                                                    setData('donor_military_department', e.target.value)
                                                }
                                                required
                                            />
                                            <FieldError message={errors.donor_military_department} />
                                        </div>
                                    )}
                                    <div>
                                        <Label>{t('global.receiver')}</Label>
                                        <TextInput
                                            value={data.donor_receiver}
                                            onChange={(e) => setData('donor_receiver', e.target.value)}
                                            placeholder={t('global.optional')}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.phlebotomy_at')}</Label>
                                        <TextInput
                                            type="datetime-local"
                                            value={data.phlebotomy_at}
                                            onChange={(e) => setData('phlebotomy_at', e.target.value)}
                                        />
                                        <p className="mt-1 text-xs text-gray-500">{t('global.phlebotomy_at_hint')}</p>
                                    </div>
                                    <div className="md:col-span-2 lg:col-span-3">
                                        <Label>{t('global.comorbidities')}</Label>
                                        <Textarea
                                            rows={2}
                                            value={data.donor_comorbidities}
                                            onChange={(e) => setData('donor_comorbidities', e.target.value)}
                                        />
                                    </div>
                                </div>
                            )}
                        </div>
                    </IcuPanel>

                    <IcuPanel
                        variant="table"
                        title={t('global.unit_details')}
                        icon="bx-donate-blood"
                        iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                    >
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <Label>{t('global.blood_group')} *</Label>
                                <Select
                                    value={data.blood_group}
                                    onChange={(e) => setData('blood_group', e.target.value)}
                                    required
                                >
                                    {filterOptions.bloodGroups.map((group) => (
                                        <option key={group} value={group}>
                                            {group}
                                        </option>
                                    ))}
                                </Select>
                                <FieldError message={errors.blood_group} />
                            </div>
                            <div>
                                <Label>{t('global.blood_rh')} *</Label>
                                <Select value={data.rh} onChange={(e) => setData('rh', e.target.value)} required>
                                    <option value="+">+</option>
                                    <option value="-">-</option>
                                </Select>
                                <FieldError message={errors.rh} />
                            </div>
                            <div>
                                <Label>{t('global.component_type')} *</Label>
                                <Select
                                    value={data.component_type}
                                    onChange={(e) => setData('component_type', e.target.value)}
                                    required
                                >
                                    {filterOptions.bloodComponentTypes.map((type) => (
                                        <option key={type} value={type}>
                                            {type}
                                        </option>
                                    ))}
                                </Select>
                                <FieldError message={errors.component_type} />
                            </div>
                            <div>
                                <Label>{t('global.bag_number')} *</Label>
                                <TextInput
                                    value={data.bag_number}
                                    onChange={(e) => setData('bag_number', e.target.value)}
                                    required
                                />
                                <FieldError message={errors.bag_number} />
                            </div>
                            <div>
                                <Label>{t('global.volume_ml')}</Label>
                                <TextInput
                                    type="number"
                                    min={1}
                                    value={data.volume_ml}
                                    onChange={(e) => setData('volume_ml', e.target.value)}
                                    placeholder="ml"
                                />
                            </div>
                            <div>
                                <Label>{t('global.collected_at')}</Label>
                                <TextInput
                                    type="datetime-local"
                                    value={data.collected_at}
                                    onChange={(e) => setData('collected_at', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('global.expires_date')} *</Label>
                                <PersianDateInput
                                    value={data.expires_date}
                                    onChange={(value) => setData('expires_date', value)}
                                />
                                <FieldError message={errors.expires_date} />
                            </div>
                            <div>
                                <Label>{t('global.expires_time')}</Label>
                                <TextInput
                                    type="time"
                                    value={data.expires_time}
                                    onChange={(e) => setData('expires_time', e.target.value)}
                                />
                                <p className="mt-1 text-xs text-gray-500">
                                    {t('global.expires_time_default_hint')}
                                </p>
                                <FieldError message={errors.expires_time} />
                            </div>
                            <div className="md:col-span-2 lg:col-span-4">
                                <Label>{t('global.notes')}</Label>
                                <Textarea
                                    rows={2}
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                />
                            </div>
                        </div>
                    </IcuPanel>

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" color="failure" disabled={processing}>
                            {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-save me-2" />}
                            {t('global.save')}
                        </Button>
                        <Button as={Link} href={urls.back} color="light" disabled={processing}>
                            {t('global.cancel')}
                        </Button>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
