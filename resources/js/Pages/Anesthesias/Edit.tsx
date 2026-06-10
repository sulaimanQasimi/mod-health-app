import { Head, Link, useForm } from '@inertiajs/react';
import { Button, Label, Select, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import { ANESTHESIA_APPLY_BTN_CLASS } from '../../Components/Anesthesias/anesthesiaUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import { useTranslation } from '../../hooks/useTranslation';
import { AnesthesiaDoctorOption, AnesthesiaNurseOption, SelectOption } from '../../types/anesthesia';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface EditAnesthesia {
    id: number;
    plan: string;
    other_problems: string;
    anesthesia_plan: string;
    position_on_bed: string;
    planned_duration: string;
    estimated_blood_waste: string;
    date: string;
    time: string;
    operation_type_id: string;
    anesthesia_type: string;
    operation_surgion_id: string;
    operation_assistants_id: string[];
    operation_anesthesia_log_id: string;
    operation_anesthesist_id: string;
    operation_scrub_nurse_id: string;
    operation_circulation_nurse_id: string;
    patient_id: number;
    appointment_id: number;
    doctor_id: number;
    branch_id: number;
    patient_name?: string | null;
}

interface EditProps {
    anesthesia: EditAnesthesia;
    operationTypes: SelectOption[];
    hospitalDoctors: AnesthesiaDoctorOption[];
    nurses: AnesthesiaNurseOption[];
    urls: {
        update: string;
        show: string;
        back: string;
    };
}

export default function AnesthesiasEdit({
    anesthesia,
    operationTypes,
    hospitalDoctors,
    nurses,
    urls,
}: EditProps) {
    const { t } = useTranslation();
    const { data, setData, put, processing, errors } = useForm({
        plan: anesthesia.plan,
        other_problems: anesthesia.other_problems,
        anesthesia_plan: anesthesia.anesthesia_plan,
        position_on_bed: anesthesia.position_on_bed,
        planned_duration: anesthesia.planned_duration,
        estimated_blood_waste: anesthesia.estimated_blood_waste,
        date: anesthesia.date,
        time: anesthesia.time,
        operation_type_id: anesthesia.operation_type_id,
        anesthesia_type: anesthesia.anesthesia_type,
        operation_surgion_id: anesthesia.operation_surgion_id,
        operation_assistants_id: anesthesia.operation_assistants_id,
        operation_anesthesia_log_id: anesthesia.operation_anesthesia_log_id,
        operation_anesthesist_id: anesthesia.operation_anesthesist_id,
        operation_scrub_nurse_id: anesthesia.operation_scrub_nurse_id,
        operation_circulation_nurse_id: anesthesia.operation_circulation_nurse_id,
        patient_id: anesthesia.patient_id,
        appointment_id: anesthesia.appointment_id,
        doctor_id: anesthesia.doctor_id,
        branch_id: anesthesia.branch_id,
    });

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        put(urls.update);
    };

    const toggleAssistant = (doctorId: string) => {
        const current = data.operation_assistants_id;
        if (current.includes(doctorId)) {
            setData(
                'operation_assistants_id',
                current.filter((id) => id !== doctorId)
            );
        } else {
            setData('operation_assistants_id', [...current, doctorId]);
        }
    };

    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.edit')}
                    subtitle={anesthesia.patient_name ?? `#${anesthesia.id}`}
                    icon="bx-edit"
                    accent="from-violet-600 to-indigo-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                />

                <form
                    onSubmit={handleSubmit}
                    className="space-y-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
                >
                    <section className="space-y-4">
                        <h2 className="text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.operation_plan')}
                        </h2>
                        <div className="grid gap-4 lg:grid-cols-2">
                            <div>
                                <Label htmlFor="plan">{t('global.plan')}</Label>
                                <Textarea
                                    id="plan"
                                    rows={3}
                                    value={data.plan}
                                    onChange={(e) => setData('plan', e.target.value)}
                                    color={errors.plan ? 'failure' : undefined}
                                />
                            </div>
                            <div>
                                <Label htmlFor="other-problems">{t('global.other_problems')}</Label>
                                <Textarea
                                    id="other-problems"
                                    rows={3}
                                    value={data.other_problems}
                                    onChange={(e) => setData('other_problems', e.target.value)}
                                    color={errors.other_problems ? 'failure' : undefined}
                                />
                            </div>
                        </div>
                    </section>

                    <section className="space-y-4">
                        <h2 className="text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.operation_team')}
                        </h2>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <Label htmlFor="operation-type">{t('global.operation_type')}</Label>
                                <Select
                                    id="operation-type"
                                    value={data.operation_type_id}
                                    onChange={(e) => setData('operation_type_id', e.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {operationTypes.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="anesthesia-type">{t('global.anesthesia_type')}</Label>
                                <Select
                                    id="anesthesia-type"
                                    value={data.anesthesia_type}
                                    onChange={(e) => setData('anesthesia_type', e.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    <option value="local">{t('global.local')}</option>
                                    <option value="spinal">{t('global.spinal')}</option>
                                    <option value="general">{t('global.general')}</option>
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="surgion">{t('global.operation_surgion')}</Label>
                                <Select
                                    id="surgion"
                                    value={data.operation_surgion_id}
                                    onChange={(e) => setData('operation_surgion_id', e.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {hospitalDoctors.map((doctor) => (
                                        <option key={doctor.id} value={doctor.id}>
                                            {doctor.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="anesthesia-log">{t('global.anesthesia_log')}</Label>
                                <Select
                                    id="anesthesia-log"
                                    value={data.operation_anesthesia_log_id}
                                    onChange={(e) => setData('operation_anesthesia_log_id', e.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {hospitalDoctors.map((doctor) => (
                                        <option key={doctor.id} value={doctor.id}>
                                            {doctor.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="anesthesist">{t('global.anesthesist')}</Label>
                                <Select
                                    id="anesthesist"
                                    value={data.operation_anesthesist_id}
                                    onChange={(e) => setData('operation_anesthesist_id', e.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {hospitalDoctors.map((doctor) => (
                                        <option key={doctor.id} value={doctor.id}>
                                            {doctor.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="scrub-nurse">{t('global.scrub_nurse')}</Label>
                                <Select
                                    id="scrub-nurse"
                                    value={data.operation_scrub_nurse_id}
                                    onChange={(e) => setData('operation_scrub_nurse_id', e.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {nurses.map((nurse) => (
                                        <option key={nurse.id} value={nurse.id}>
                                            {nurse.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="circulation-nurse">{t('global.circulation_nurse')}</Label>
                                <Select
                                    id="circulation-nurse"
                                    value={data.operation_circulation_nurse_id}
                                    onChange={(e) => setData('operation_circulation_nurse_id', e.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {nurses.map((nurse) => (
                                        <option key={nurse.id} value={nurse.id}>
                                            {nurse.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                        </div>

                        <div>
                            <Label>{t('global.operation_assistants')}</Label>
                            <div className="mt-2 flex flex-wrap gap-2">
                                {hospitalDoctors.map((doctor) => {
                                    const selected = data.operation_assistants_id.includes(String(doctor.id));
                                    return (
                                        <button
                                            key={doctor.id}
                                            type="button"
                                            onClick={() => toggleAssistant(String(doctor.id))}
                                            className={`rounded-full px-3 py-1 text-xs font-medium transition ${
                                                selected
                                                    ? 'bg-violet-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                                            }`}
                                        >
                                            {doctor.name}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    </section>

                    <section className="space-y-4">
                        <h2 className="text-sm font-semibold text-gray-900 dark:text-white">
                            {t('global.date')}
                        </h2>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <Label>{t('global.date')}</Label>
                                <PersianDateInput value={data.date} onChange={(date) => setData('date', date)} />
                            </div>
                            <div>
                                <Label htmlFor="time">{t('global.time')}</Label>
                                <TextInput
                                    id="time"
                                    type="time"
                                    value={data.time}
                                    onChange={(e) => setData('time', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="duration">{t('global.operation_duration')}</Label>
                                <TextInput
                                    id="duration"
                                    value={data.planned_duration}
                                    onChange={(e) => setData('planned_duration', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="position">{t('global.position_on_bed')}</Label>
                                <TextInput
                                    id="position"
                                    value={data.position_on_bed}
                                    onChange={(e) => setData('position_on_bed', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label htmlFor="blood-waste">{t('global.estimated_blood_waste')}</Label>
                                <TextInput
                                    id="blood-waste"
                                    value={data.estimated_blood_waste}
                                    onChange={(e) => setData('estimated_blood_waste', e.target.value)}
                                />
                            </div>
                        </div>
                        <div>
                            <Label htmlFor="anesthesia-plan">{t('global.anesthesia_plan')}</Label>
                            <Textarea
                                id="anesthesia-plan"
                                rows={3}
                                value={data.anesthesia_plan}
                                onChange={(e) => setData('anesthesia_plan', e.target.value)}
                            />
                        </div>
                    </section>

                    <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button type="submit" disabled={processing} className={ANESTHESIA_APPLY_BTN_CLASS}>
                            {processing ? <Spinner size="sm" /> : <i className="bx bx-save" />}
                            {t('global.save')}
                        </button>
                        <Button as={Link} color="light" href={urls.show}>
                            {t('global.cancel')}
                        </Button>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
