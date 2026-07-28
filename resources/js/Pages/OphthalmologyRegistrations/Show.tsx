import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Select, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, ReactNode, useMemo, useState } from 'react';
import AppointmentPageHeader from '../../Components/Appointments/AppointmentPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import TableBadge from '../../Components/ui/TableBadge';
import { LabTestSection, PrescriptionSection } from '../../Components/Appointments/Sections';
import { useTranslation } from '../../hooks/useTranslation';

type JsonMap = Record<string, any>;

interface Props {
    registration: {
        id: number;
        appointment_id: number;
        ref_no: string;
        examiner_id: number | null;
        examiner_name: string | null;
        registration_date: string;
        status: string;
        chief_complaint: string;
        medical_history: JsonMap;
        visual_examination: JsonMap;
        refraction: JsonMap;
        slit_lamp_examination: JsonMap;
        fundus_examination: JsonMap;
        diagnosis: string;
        treatment_plan: string;
        follow_up_date: string;
        notes: string;
        fundus_image_url: string | null;
        patient: {
            id: number | null;
            name: string;
            father_name: string | null;
            id_card: string | number | null;
            age: string | null;
            gender: string | number | null;
            phone: string | null;
            occupation: string | null;
        };
    };
    formOptions: { doctors: Array<{ id: number; name: string }> };
    permissions: {
        edit: boolean;
        changeStatus: boolean;
        uploadImages: boolean;
    };
    urls: { update: string; appointment: string };
}

const HISTORY_FIELDS = [
    ['diabetes', 'Diabetes'],
    ['cardiac_disease', 'Cardiac disease'],
    ['arthritis', 'Arthritis'],
    ['pregnancy', 'Pregnancy'],
    ['asthma', 'Asthma'],
    ['thyroid_disease', 'Thyroid disease'],
    ['hypertension', 'Hypertension'],
    ['allergies', 'Allergies'],
] as const;

const VISUAL_FIELDS = [
    ['visual_acuity', 'Visual acuity'],
    ['pinhole_vision', 'Pinhole vision'],
    ['vision_with_glasses', 'Vision with glasses'],
    ['near_vision', 'Near vision'],
    ['intraocular_pressure', 'Intraocular pressure'],
] as const;

const REFRACTION_FIELDS = [
    ['sphere', 'Sphere'],
    ['cylinder', 'Cylinder'],
    ['axis', 'Axis'],
    ['distance_vision', 'Distance vision'],
    ['near_vision', 'Near vision'],
    ['present_glasses', 'Present glasses'],
    ['recommended_prescription', 'Recommended prescription'],
] as const;

const SLIT_LAMP_FIELDS = [
    ['lids', 'Lids'],
    ['conjunctiva', 'Conjunctiva'],
    ['cornea', 'Cornea'],
    ['sclera', 'Sclera'],
    ['anterior_chamber', 'Anterior chamber'],
    ['iris', 'Iris'],
    ['pupil', 'Pupil'],
    ['lens', 'Lens'],
    ['gonioscopy', 'Gonioscopy'],
    ['extraocular_movement', 'Extraocular movement'],
] as const;

const inputClass = 'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-cyan-500 focus:ring-cyan-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white';

function SectionCard({ title, icon, children }: { title: string; icon: string; children: ReactNode }) {
    return (
        <Card className="border !shadow-sm">
            <h2 className="flex items-center gap-2 border-b border-gray-100 pb-3 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-white">
                <i className={`bx ${icon} text-xl text-cyan-600`} />
                {title}
            </h2>
            {children}
        </Card>
    );
}

export default function OphthalmologyRegistrationShow({ registration, formOptions, permissions, urls }: Props) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState({
        examiner_id: registration.examiner_id ? String(registration.examiner_id) : '',
        registration_date: registration.registration_date,
        status: registration.status,
        chief_complaint: registration.chief_complaint,
        medical_history: registration.medical_history ?? {},
        visual_examination: registration.visual_examination ?? {},
        refraction: registration.refraction ?? {},
        slit_lamp_examination: registration.slit_lamp_examination ?? {},
        fundus_examination: registration.fundus_examination ?? {},
        diagnosis: registration.diagnosis,
        treatment_plan: registration.treatment_plan,
        follow_up_date: registration.follow_up_date,
        notes: registration.notes,
        fundus_image: null as File | null,
    });

    const doctorOptions = useMemo(
        () => formOptions.doctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
        [formOptions.doctors],
    );

    const setNested = (section: keyof typeof form, path: string[], value: any) => {
        setForm((current) => {
            const root = { ...(current[section] as JsonMap) };
            let cursor = root;
            path.slice(0, -1).forEach((part) => {
                cursor[part] = { ...(cursor[part] ?? {}) };
                cursor = cursor[part];
            });
            cursor[path[path.length - 1]] = value;
            return { ...current, [section]: root };
        });
    };

    const nestedValue = (section: keyof typeof form, ...path: string[]) => {
        let value: any = form[section];
        path.forEach((part) => {
            value = value?.[part];
        });
        return value ?? '';
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const payload: JsonMap = { _method: 'put' };
        if (permissions.edit) {
            Object.assign(payload, {
                examiner_id: form.examiner_id || null,
                registration_date: form.registration_date,
                chief_complaint: form.chief_complaint,
                medical_history: form.medical_history,
                visual_examination: form.visual_examination,
                refraction: form.refraction,
                slit_lamp_examination: form.slit_lamp_examination,
                fundus_examination: form.fundus_examination,
                diagnosis: form.diagnosis,
                treatment_plan: form.treatment_plan,
                follow_up_date: form.follow_up_date,
                notes: form.notes,
            });
        }
        if (permissions.changeStatus) payload.status = form.status;
        if (permissions.uploadImages && form.fundus_image) payload.fundus_image = form.fundus_image;

        setProcessing(true);
        router.post(
            urls.update,
            payload,
            {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    const canSave = permissions.edit
        || permissions.changeStatus
        || permissions.uploadImages;

    const statusColor = ({ pending: 'warning', in_progress: 'info', completed: 'success', cancelled: 'failure' } as const)[registration.status] ?? 'gray';

    return (
        <DashboardLayout>
            <Head title={t('global.ophthalmology_examination')} />
            <form onSubmit={submit} className="mx-auto max-w-7xl space-y-6 pb-10">
                <Card className="border !shadow-sm">
                    <AppointmentPageHeader
                        title={t('global.ophthalmology_examination')}
                        subtitle={`${registration.ref_no} · ${registration.patient.name}`}
                        icon="bx-low-vision"
                        action={
                            <div className="flex flex-wrap items-center gap-2">
                                <TableBadge color={statusColor}>{registration.status.replace('_', ' ')}</TableBadge>
                                <Link href={urls.appointment}>
                                    <Button color="light" size="sm">
                                        <i className="bx bx-arrow-back me-2" />
                                        {t('global.appointment')}
                                    </Button>
                                </Link>
                                {canSave && (
                                    <Button type="submit" color="blue" size="sm" disabled={processing}>
                                        {processing ? <Spinner size="sm" /> : <i className="bx bx-save me-2" />}
                                        {t('global.save')}
                                    </Button>
                                )}
                            </div>
                        }
                    />

                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            [t('global.patient_name'), registration.patient.name],
                            [t('global.father_name'), registration.patient.father_name],
                            [t('global.id_card'), registration.patient.id_card],
                            [t('global.age'), registration.patient.age],
                            [t('global.gender'), registration.patient.gender],
                            [t('global.phone'), registration.patient.phone],
                            [t('global.occupation'), registration.patient.occupation],
                            [t('global.examiner'), registration.examiner_name],
                        ].map(([label, value]) => (
                            <div key={String(label)} className="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                                <div className="text-xs text-gray-500">{label}</div>
                                <div className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value ?? '—'}</div>
                            </div>
                        ))}
                    </div>
                </Card>

                <SectionCard title={t('global.registration_and_history')} icon="bx-clipboard">
                    <div className="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label>{t('global.examiner')}</Label>
                            <SearchableSelect
                                value={form.examiner_id}
                                onChange={(examiner_id) => setForm((current) => ({ ...current, examiner_id }))}
                                options={doctorOptions}
                                disabled={!permissions.edit}
                            />
                        </div>
                        <div>
                            <Label>{t('global.registration_date')}</Label>
                            <PersianDateInput
                                value={form.registration_date}
                                onChange={(registration_date) => setForm((current) => ({ ...current, registration_date }))}
                                disabled={!permissions.edit}
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')}</Label>
                            <Select
                                value={form.status}
                                onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}
                                disabled={!permissions.changeStatus}
                            >
                                <option value="pending">{t('global.status_pending')}</option>
                                <option value="in_progress">{t('global.status_in_progress')}</option>
                                <option value="completed">{t('global.status_completed')}</option>
                                <option value="cancelled">{t('global.status_cancelled')}</option>
                            </Select>
                        </div>
                        <div className="md:col-span-3">
                            <Label>{t('global.chief_complaint')}</Label>
                            <Textarea
                                rows={3}
                                value={form.chief_complaint}
                                onChange={(event) => setForm((current) => ({ ...current, chief_complaint: event.target.value }))}
                                disabled={!permissions.edit}
                            />
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <tr><th className="p-2 text-start">Condition</th><th className="p-2 text-start">Yes / No</th><th className="p-2 text-start">Notes</th></tr>
                            </thead>
                            <tbody>
                                {HISTORY_FIELDS.map(([key, label]) => (
                                    <tr key={key} className="border-t border-gray-100 dark:border-gray-700">
                                        <td className="p-2 font-medium">{label}</td>
                                        <td className="p-2">
                                            <select
                                                className={inputClass}
                                                value={nestedValue('medical_history', key, 'value')}
                                                onChange={(event) => setNested('medical_history', [key, 'value'], event.target.value)}
                                                disabled={!permissions.edit}
                                            >
                                                <option value="">—</option>
                                                <option value="no">No</option>
                                                <option value="yes">Yes</option>
                                            </select>
                                        </td>
                                        <td className="p-2">
                                            <TextInput
                                                value={nestedValue('medical_history', key, 'notes')}
                                                onChange={(event) => setNested('medical_history', [key, 'notes'], event.target.value)}
                                                disabled={!permissions.edit}
                                            />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </SectionCard>

                <SectionCard title={t('global.visual_examination')} icon="bx-show">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 dark:bg-gray-800">
                                <tr><th className="p-2 text-start">Measurement</th><th className="p-2 text-start">OD (Right)</th><th className="p-2 text-start">OS (Left)</th></tr>
                            </thead>
                            <tbody>
                                {VISUAL_FIELDS.map(([key, label]) => (
                                    <tr key={key} className="border-t border-gray-100 dark:border-gray-700">
                                        <td className="p-2 font-medium">{label}</td>
                                        {['od', 'os'].map((eye) => (
                                            <td key={eye} className="p-2">
                                                <TextInput
                                                    value={nestedValue('visual_examination', eye, key)}
                                                    onChange={(event) => setNested('visual_examination', [eye, key], event.target.value)}
                                                    disabled={!permissions.edit}
                                                />
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="grid gap-4 md:grid-cols-3">
                        {[
                            ['squint_assessment', 'Squint assessment'],
                            ['blood_pressure', 'Blood pressure'],
                            ['color_vision', 'Color vision'],
                        ].map(([key, label]) => (
                            <div key={key}>
                                <Label>{label}</Label>
                                <TextInput
                                    value={nestedValue('visual_examination', key)}
                                    onChange={(event) => setNested('visual_examination', [key], event.target.value)}
                                    disabled={!permissions.edit}
                                />
                            </div>
                        ))}
                    </div>
                </SectionCard>

                <SectionCard title={t('global.refraction')} icon="bx-glasses">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 dark:bg-gray-800">
                                <tr><th className="p-2 text-start">Measurement</th><th className="p-2 text-start">OD (Right)</th><th className="p-2 text-start">OS (Left)</th></tr>
                            </thead>
                            <tbody>
                                {REFRACTION_FIELDS.map(([key, label]) => (
                                    <tr key={key} className="border-t border-gray-100 dark:border-gray-700">
                                        <td className="p-2 font-medium">{label}</td>
                                        {['od', 'os'].map((eye) => (
                                            <td key={eye} className="p-2">
                                                <TextInput
                                                    value={nestedValue('refraction', eye, key)}
                                                    onChange={(event) => setNested('refraction', [eye, key], event.target.value)}
                                                    disabled={!permissions.edit}
                                                />
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label>IPD</Label>
                            <TextInput value={nestedValue('refraction', 'ipd')} onChange={(event) => setNested('refraction', ['ipd'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                        <div className="md:col-span-2">
                            <Label>{t('global.notes')}</Label>
                            <TextInput value={nestedValue('refraction', 'notes')} onChange={(event) => setNested('refraction', ['notes'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                    </div>
                </SectionCard>

                <SectionCard title={t('global.slit_lamp_examination')} icon="bx-bulb">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[900px] text-sm">
                            <thead className="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th className="p-2 text-start">Finding</th>
                                    <th className="p-2 text-start">OD status</th><th className="p-2 text-start">OD notes</th>
                                    <th className="p-2 text-start">OS status</th><th className="p-2 text-start">OS notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                {SLIT_LAMP_FIELDS.map(([key, label]) => (
                                    <tr key={key} className="border-t border-gray-100 dark:border-gray-700">
                                        <td className="p-2 font-medium">{label}</td>
                                        {['od', 'os'].flatMap((eye) => [
                                            <td key={`${eye}-status`} className="p-2">
                                                <select className={inputClass} value={nestedValue('slit_lamp_examination', eye, key, 'status')} onChange={(event) => setNested('slit_lamp_examination', [eye, key, 'status'], event.target.value)} disabled={!permissions.edit}>
                                                    <option value="">—</option><option value="normal">Normal</option><option value="abnormal">Abnormal</option>
                                                </select>
                                            </td>,
                                            <td key={`${eye}-notes`} className="p-2">
                                                <TextInput value={nestedValue('slit_lamp_examination', eye, key, 'notes')} onChange={(event) => setNested('slit_lamp_examination', [eye, key, 'notes'], event.target.value)} disabled={!permissions.edit} />
                                            </td>,
                                        ])}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </SectionCard>

                <SectionCard title={t('global.fundus_examination')} icon="bx-camera">
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>OD (Right eye) findings</Label>
                            <Textarea rows={4} value={nestedValue('fundus_examination', 'od_findings')} onChange={(event) => setNested('fundus_examination', ['od_findings'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                        <div>
                            <Label>OS (Left eye) findings</Label>
                            <Textarea rows={4} value={nestedValue('fundus_examination', 'os_findings')} onChange={(event) => setNested('fundus_examination', ['os_findings'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                        <div>
                            <Label>Dilation status</Label>
                            <Select value={nestedValue('fundus_examination', 'dilation_status')} onChange={(event) => setNested('fundus_examination', ['dilation_status'], event.target.value)} disabled={!permissions.edit}>
                                <option value="">—</option><option value="not_dilated">Not dilated</option><option value="dilated">Dilated</option>
                            </Select>
                        </div>
                        <div>
                            <Label>Dilation time</Label>
                            <TextInput type="time" value={nestedValue('fundus_examination', 'dilation_time')} onChange={(event) => setNested('fundus_examination', ['dilation_time'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                        <div className="md:col-span-2">
                            <Label>Fundus image / report</Label>
                                <input type="file" accept="image/*,.pdf" className={inputClass} onChange={(event) => setForm((current) => ({ ...current, fundus_image: event.target.files?.[0] ?? null }))} disabled={!permissions.uploadImages} />
                            {registration.fundus_image_url && <a href={registration.fundus_image_url} target="_blank" rel="noreferrer" className="mt-2 inline-flex text-sm text-cyan-600 hover:underline">View current attachment</a>}
                        </div>
                    </div>
                </SectionCard>

                <LabTestSection appointmentId={registration.appointment_id} embedded />
                <PrescriptionSection appointmentId={registration.appointment_id} embedded />

                <SectionCard title={t('global.assessment_and_plan')} icon="bx-notepad">
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>{t('global.diagnosis')}</Label>
                            <Textarea rows={4} value={form.diagnosis} onChange={(event) => setForm((current) => ({ ...current, diagnosis: event.target.value }))} disabled={!permissions.edit} />
                        </div>
                        <div>
                            <Label>{t('global.treatment_plan')}</Label>
                            <Textarea rows={4} value={form.treatment_plan} onChange={(event) => setForm((current) => ({ ...current, treatment_plan: event.target.value }))} disabled={!permissions.edit} />
                        </div>
                        <div>
                            <Label>{t('global.follow_up_date')}</Label>
                            <PersianDateInput value={form.follow_up_date} onChange={(follow_up_date) => setForm((current) => ({ ...current, follow_up_date }))} disabled={!permissions.edit} />
                        </div>
                        <div>
                            <Label>{t('global.notes')}</Label>
                            <Textarea rows={3} value={form.notes} onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))} disabled={!permissions.edit} />
                        </div>
                    </div>
                </SectionCard>

                {canSave && (
                    <div className="sticky bottom-4 flex justify-end">
                        <Button type="submit" color="blue" size="lg" disabled={processing} className="shadow-lg">
                            {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-save me-2" />}
                            {t('global.save_ophthalmology_examination')}
                        </Button>
                    </div>
                )}
            </form>
        </DashboardLayout>
    );
}
