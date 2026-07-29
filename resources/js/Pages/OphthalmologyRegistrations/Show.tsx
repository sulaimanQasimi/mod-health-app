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
    ['diabetes', 'دیابت'],
    ['cardiac_disease', 'بیماری قلبی'],
    ['arthritis', 'التهاب مفاصل'],
    ['pregnancy', 'حاملگی'],
    ['asthma', 'آسم'],
    ['thyroid_disease', 'بیماری تیروئید'],
    ['hypertension', 'فشار خون بلند'],
    ['allergies', 'حساسیت‌ها'],
] as const;

const VISUAL_FIELDS = [
    ['visual_acuity', 'حدت بینایی'],
    ['pinhole_vision', 'دید با سوراخ سوزنی'],
    ['vision_with_glasses', 'دید با عینک'],
    ['near_vision', 'دید نزدیک'],
    ['intraocular_pressure', 'فشار داخل چشم'],
] as const;

const REFRACTION_FIELDS = [
    ['sphere', 'اسفیر (SPH)'],
    ['cylinder', 'سیلندر (CYL)'],
    ['axis', 'محور (Axis)'],
    ['distance_vision', 'دید دور'],
    ['near_vision', 'دید نزدیک'],
    ['present_glasses', 'عینک فعلی'],
    ['recommended_prescription', 'نسخه پیشنهادی'],
] as const;

const SLIT_LAMP_FIELDS = [
    ['lids', 'پلک‌ها'],
    ['conjunctiva', 'ملتحمه'],
    ['cornea', 'قرنیه'],
    ['sclera', 'صلبیه'],
    ['anterior_chamber', 'اتاق قدامی'],
    ['iris', 'عنبیه'],
    ['pupil', 'مردمک'],
    ['lens', 'عدسی'],
    ['gonioscopy', 'گونیوسکوپی'],
    ['extraocular_movement', 'حرکت خارج چشمی'],
] as const;

const inputClass = 'block w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white';
const tableShellClass = 'overflow-hidden rounded-2xl border border-gray-200/80 dark:border-gray-700';
const tableHeaderClass = 'bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-gray-800/80 dark:text-gray-300';
const tableRowClass = 'border-t border-gray-100 text-gray-700 transition hover:bg-cyan-50/40 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-cyan-950/20';
const tableCellClass = 'p-3 text-start font-medium text-gray-700 dark:text-gray-200';

function SectionCard({
    id,
    title,
    icon,
    step,
    subtitle,
    children,
}: {
    id: string;
    title: string;
    icon: string;
    step: string;
    subtitle?: string;
    children: ReactNode;
}) {
    return (
        <Card id={id} className="scroll-mt-24 overflow-hidden border border-gray-200/80 !shadow-sm dark:border-gray-700">
            <div className="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-sm shadow-cyan-500/20">
                    <i className={`bx ${icon} text-xl`} />
                </div>
                <div className="min-w-0 flex-1">
                    <div className="mb-0.5 text-xs font-semibold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">{step}</div>
                    <h2 className="text-base font-semibold text-gray-900 dark:text-white">{title}</h2>
                    {subtitle && <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{subtitle}</p>}
                </div>
            </div>
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
    const statusLabel = t(`global.status_${registration.status}`);
    const genderLabel = String(registration.patient.gender) === '1'
        ? t('global.female')
        : String(registration.patient.gender) === '0'
            ? t('global.male')
            : registration.patient.gender;
    const patientFields = [
        [t('global.patient_name'), registration.patient.name, 'bx-user'],
        [t('global.father_name'), registration.patient.father_name, 'bx-group'],
        [t('global.id_card'), registration.patient.id_card, 'bx-id-card'],
        [t('global.age'), registration.patient.age, 'bx-calendar'],
        [t('global.gender'), genderLabel, 'bx-male-female'],
        [t('global.phone'), registration.patient.phone, 'bx-phone'],
        [t('global.occupation'), registration.patient.occupation, 'bx-briefcase'],
        [t('global.examiner'), registration.examiner_name, 'bx-plus-medical'],
    ] as const;
    const sectionLinks = [
        ['registration-history', t('global.registration_and_history'), 'bx-clipboard'],
        ['visual-exam', t('global.visual_examination'), 'bx-show'],
        ['refraction', t('global.refraction'), 'bx-glasses'],
        ['slit-lamp', t('global.slit_lamp_examination'), 'bx-bulb'],
        ['fundus', t('global.fundus_examination'), 'bx-camera'],
        ['laboratory', t('global.lab_tests'), 'bx-test-tube'],
        ['prescriptions', t('global.prescriptions'), 'bx-capsule'],
        ['assessment-plan', t('global.assessment_and_plan'), 'bx-notepad'],
    ] as const;

    return (
        <DashboardLayout>
            <Head title={t('global.ophthalmology_examination')} />
            <form onSubmit={submit} className="mx-auto max-w-7xl space-y-5 pb-10">
                <Card className="overflow-hidden border border-cyan-100 bg-gradient-to-br from-white via-white to-cyan-50/70 !shadow-sm dark:border-cyan-950 dark:from-gray-900 dark:via-gray-900 dark:to-cyan-950/30">
                    <AppointmentPageHeader
                        title={t('global.ophthalmology_examination')}
                        subtitle={`${registration.ref_no} · ${registration.patient.name}`}
                        icon="bx-low-vision"
                        action={
                            <div className="flex flex-wrap items-center gap-2">
                                <TableBadge color={statusColor}>{statusLabel}</TableBadge>
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

                    <div className="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                        {patientFields.map(([label, value, icon]) => (
                            <div key={String(label)} className="flex min-w-0 items-center gap-3 rounded-xl border border-white/80 bg-white/80 px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800/70">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-300">
                                    <i className={`bx ${icon} text-lg`} />
                                </div>
                                <div className="min-w-0">
                                    <div className="truncate text-[11px] font-medium text-gray-500 dark:text-gray-400">{label}</div>
                                    <div className="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white">{value ?? '—'}</div>
                                </div>
                            </div>
                        ))}
                    </div>
                </Card>

                <nav className="sticky top-16 z-20 -mx-1 overflow-x-auto rounded-2xl border border-gray-200/80 bg-white/95 p-2 shadow-sm backdrop-blur [scrollbar-width:none] [&::-webkit-scrollbar]:hidden dark:border-gray-700 dark:bg-gray-900/95" aria-label={t('global.ophthalmology_examination')}>
                    <div className="flex min-w-max gap-1">
                        {sectionLinks.map(([id, label, icon], index) => (
                            <a key={id} href={`#${id}`} className="group flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium text-gray-600 transition hover:bg-cyan-50 hover:text-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:text-gray-300 dark:hover:bg-cyan-950/40 dark:hover:text-cyan-300">
                                <span className="flex h-6 w-6 items-center justify-center rounded-lg bg-gray-100 text-gray-500 group-hover:bg-cyan-100 group-hover:text-cyan-600 dark:bg-gray-800 dark:text-gray-400 dark:group-hover:bg-cyan-900/60 dark:group-hover:text-cyan-300">
                                    <i className={`bx ${icon}`} />
                                </span>
                                <span>{index + 1}. {label}</span>
                            </a>
                        ))}
                    </div>
                </nav>

                <SectionCard id="registration-history" step="01" title={t('global.registration_and_history')} icon="bx-clipboard" subtitle={t('global.chief_complaint')}>
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

                    <div>
                        <div className="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                            <i className="bx bx-history text-cyan-600 dark:text-cyan-400" />
                            تاریخچه طبی
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            {HISTORY_FIELDS.map(([key, label]) => (
                                <div key={key} className="rounded-2xl border border-gray-200/80 bg-gray-50/70 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                                    <div className="mb-2 flex items-center justify-between gap-3">
                                        <span className="text-sm font-semibold text-gray-800 dark:text-gray-100">{label}</span>
                                        <select
                                            className={`${inputClass} !w-28 shrink-0`}
                                            value={nestedValue('medical_history', key, 'value')}
                                            onChange={(event) => setNested('medical_history', [key, 'value'], event.target.value)}
                                            disabled={!permissions.edit}
                                        >
                                            <option value="">—</option>
                                            <option value="no">نخیر</option>
                                            <option value="yes">بلی</option>
                                        </select>
                                    </div>
                                    <TextInput
                                        placeholder={t('global.notes')}
                                        value={nestedValue('medical_history', key, 'notes')}
                                        onChange={(event) => setNested('medical_history', [key, 'notes'], event.target.value)}
                                        disabled={!permissions.edit}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                </SectionCard>

                <SectionCard id="visual-exam" step="02" title={t('global.visual_examination')} icon="bx-show">
                    <div className={`${tableShellClass} overflow-x-auto`}>
                        <table className="w-full text-sm">
                            <thead className={tableHeaderClass}>
                                <tr>
                                    <th className="p-3 text-start">اندازه‌گیری</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">OD (چشم راست)</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">OS (چشم چپ)</th>
                                </tr>
                            </thead>
                            <tbody>
                                {VISUAL_FIELDS.map(([key, label]) => (
                                    <tr key={key} className={tableRowClass}>
                                        <td className={tableCellClass}>{label}</td>
                                        {['od', 'os'].map((eye) => (
                                            <td key={eye} className="p-2.5">
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
                            ['squint_assessment', 'ارزیابی انحراف چشم'],
                            ['blood_pressure', 'فشار خون'],
                            ['color_vision', 'دید رنگی'],
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

                <SectionCard id="refraction" step="03" title={t('global.refraction')} icon="bx-glasses">
                    <div className={`${tableShellClass} overflow-x-auto`}>
                        <table className="w-full text-sm">
                            <thead className={tableHeaderClass}>
                                <tr>
                                    <th className="p-3 text-start">اندازه‌گیری</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">OD (چشم راست)</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">OS (چشم چپ)</th>
                                </tr>
                            </thead>
                            <tbody>
                                {REFRACTION_FIELDS.map(([key, label]) => (
                                    <tr key={key} className={tableRowClass}>
                                        <td className={tableCellClass}>{label}</td>
                                        {['od', 'os'].map((eye) => (
                                            <td key={eye} className="p-2.5">
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

                <SectionCard id="slit-lamp" step="04" title={t('global.slit_lamp_examination')} icon="bx-bulb">
                    <div className={`${tableShellClass} overflow-x-auto`}>
                        <table className="w-full min-w-[900px] text-sm">
                            <thead className={tableHeaderClass}>
                                <tr>
                                    <th className="p-3 text-start">یافته</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">وضعیت OD</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">یادداشت OD</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">وضعیت OS</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">یادداشت OS</th>
                                </tr>
                            </thead>
                            <tbody>
                                {SLIT_LAMP_FIELDS.map(([key, label]) => (
                                    <tr key={key} className={tableRowClass}>
                                        <td className={tableCellClass}>{label}</td>
                                        {['od', 'os'].flatMap((eye) => [
                                            <td key={`${eye}-status`} className="p-2.5">
                                                <select className={inputClass} value={nestedValue('slit_lamp_examination', eye, key, 'status')} onChange={(event) => setNested('slit_lamp_examination', [eye, key, 'status'], event.target.value)} disabled={!permissions.edit}>
                                                    <option value="">—</option><option value="normal">طبیعی</option><option value="abnormal">غیرطبیعی</option>
                                                </select>
                                            </td>,
                                            <td key={`${eye}-notes`} className="p-2.5">
                                                <TextInput value={nestedValue('slit_lamp_examination', eye, key, 'notes')} onChange={(event) => setNested('slit_lamp_examination', [eye, key, 'notes'], event.target.value)} disabled={!permissions.edit} />
                                            </td>,
                                        ])}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </SectionCard>

                <SectionCard id="fundus" step="05" title={t('global.fundus_examination')} icon="bx-camera">
                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="rounded-2xl border border-cyan-200 bg-cyan-50/50 p-4 dark:border-cyan-900 dark:bg-cyan-950/20">
                            <Label className="text-cyan-800 dark:text-cyan-200">یافته‌های OD (چشم راست)</Label>
                            <Textarea rows={4} value={nestedValue('fundus_examination', 'od_findings')} onChange={(event) => setNested('fundus_examination', ['od_findings'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                        <div className="rounded-2xl border border-violet-200 bg-violet-50/50 p-4 dark:border-violet-900 dark:bg-violet-950/20">
                            <Label className="text-violet-800 dark:text-violet-200">یافته‌های OS (چشم چپ)</Label>
                            <Textarea rows={4} value={nestedValue('fundus_examination', 'os_findings')} onChange={(event) => setNested('fundus_examination', ['os_findings'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                        <div>
                            <Label>وضعیت گشادسازی مردمک</Label>
                            <Select value={nestedValue('fundus_examination', 'dilation_status')} onChange={(event) => setNested('fundus_examination', ['dilation_status'], event.target.value)} disabled={!permissions.edit}>
                                <option value="">—</option><option value="not_dilated">گشاد نشده</option><option value="dilated">گشاد شده</option>
                            </Select>
                        </div>
                        <div>
                            <Label>زمان گشادسازی</Label>
                            <TextInput type="time" value={nestedValue('fundus_examination', 'dilation_time')} onChange={(event) => setNested('fundus_examination', ['dilation_time'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                        <div className="md:col-span-2">
                            <Label>تصویر یا گزارش فوندوس</Label>
                                <input type="file" accept="image/*,.pdf" className={inputClass} onChange={(event) => setForm((current) => ({ ...current, fundus_image: event.target.files?.[0] ?? null }))} disabled={!permissions.uploadImages} />
                            {registration.fundus_image_url && <a href={registration.fundus_image_url} target="_blank" rel="noreferrer" className="mt-2 inline-flex text-sm text-cyan-600 hover:underline">مشاهده ضمیمه فعلی</a>}
                        </div>
                    </div>
                </SectionCard>

                <div id="laboratory" className="scroll-mt-24">
                    <LabTestSection appointmentId={registration.appointment_id} embedded />
                </div>
                <div id="prescriptions" className="scroll-mt-24">
                    <PrescriptionSection appointmentId={registration.appointment_id} embedded />
                </div>

                <SectionCard id="assessment-plan" step="08" title={t('global.assessment_and_plan')} icon="bx-notepad">
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
