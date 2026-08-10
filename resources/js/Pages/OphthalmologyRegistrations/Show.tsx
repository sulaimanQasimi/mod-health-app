import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Select, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, ReactNode, useEffect, useMemo, useRef, useState } from 'react';
import AppointmentPageHeader from '../../Components/Appointments/AppointmentPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import EyeExaminationDiagram from '../../Components/Ophthalmology/EyeExaminationDiagram';
import YesNoToggle from '../../Components/Ophthalmology/YesNoToggle';
import {
    ATTACHMENT_LABELS,
    DIAGNOSTIC_TEST_FIELDS,
    FUNDUS_STRUCTURED_FIELDS,
    IOP_METHODS,
    OCULAR_HISTORY_FIELDS,
    REFRACTION_FIELDS,
    SLIT_LAMP_FIELDS,
    SLIT_PHRASES,
    VA_PRESETS,
    VISUAL_FIELDS,
    axisValid,
    copyEyeData,
    inputClass,
    iopAlert,
    markSlitLampAllNormal,
    nestedGet,
    nestedSet,
    swapEyeData,
    tableCellClass,
    tableHeaderClass,
    tableRowClass,
    tableShellClass,
    type EyeSide,
} from '../../Components/Ophthalmology/examFields';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import TableBadge from '../../Components/ui/TableBadge';
import { LabTestSection, PrescriptionSection } from '../../Components/Appointments/Sections';
import { useTranslation } from '../../hooks/useTranslation';

type JsonMap = Record<string, any>;

interface DiagnosisItem {
    label: string;
    code: string;
    laterality: string;
}

interface AttachmentItem {
    path: string | null;
    label: string;
    original_name: string | null;
    mime: string | null;
    uploaded_at: string | null;
    url: string | null;
}

interface PriorVisit {
    id: number;
    ref_no: string;
    registration_date: string | null;
    status: string;
    examiner_name: string | null;
    diagnosis: string | null;
    visual_examination: JsonMap;
    refraction: JsonMap;
    show_url: string;
}

interface Props {
    registration: {
        id: number;
        appointment_id: number;
        ref_no: string;
        examiner_id: number | null;
        examiner_name: string | null;
        registration_date: string;
        appointment_date: string | null;
        appointment_completed: boolean;
        status: string;
        chief_complaint: string;
        medical_history: JsonMap;
        visual_examination: JsonMap;
        refraction: JsonMap;
        slit_lamp_examination: JsonMap;
        fundus_examination: JsonMap;
        diagnostic_tests: JsonMap;
        diagnosis: string;
        diagnosis_items: DiagnosisItem[];
        treatment_plan: string;
        follow_up_date: string;
        notes: string;
        fundus_image_url: string | null;
        attachments: AttachmentItem[];
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
    priorVisits: PriorVisit[];
    formOptions: {
        doctors: Array<{ id: number; name: string }>;
        diagnosisSuggestions: Array<{ code: string; label: string }>;
    };
    permissions: {
        edit: boolean;
        changeStatus: boolean;
        uploadImages: boolean;
    };
    urls: {
        update: string;
        appointment: string;
        print: string;
        patient: string | null;
        index: string;
    };
}

function SectionCard({
    id,
    title,
    icon,
    step,
    subtitle,
    children,
    complete,
}: {
    id: string;
    title: string;
    icon: string;
    step: string;
    subtitle?: string;
    children: ReactNode;
    complete?: boolean;
}) {
    return (
        <Card id={id} className="scroll-mt-28 overflow-hidden border border-gray-200/80 !shadow-sm dark:border-gray-700">
            <div className="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-white shadow-sm ${complete ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-500/20' : 'bg-gradient-to-br from-cyan-500 to-blue-600 shadow-cyan-500/20'}`}>
                    <i className={`bx ${complete ? 'bx-check' : icon} text-xl`} />
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

function EyeActionBar({
    disabled,
    onCopyOdToOs,
    onCopyOsToOd,
    onSwap,
    onAllNormalOd,
    onAllNormalOs,
}: {
    disabled: boolean;
    onCopyOdToOs?: () => void;
    onCopyOsToOd?: () => void;
    onSwap?: () => void;
    onAllNormalOd?: () => void;
    onAllNormalOs?: () => void;
}) {
    const { t } = useTranslation();
    return (
        <div className="mb-3 flex flex-wrap gap-2">
            {onCopyOdToOs && (
                <Button type="button" size="xs" color="light" disabled={disabled} onClick={onCopyOdToOs}>
                    {t('global.oph_copy_od_to_os')}
                </Button>
            )}
            {onCopyOsToOd && (
                <Button type="button" size="xs" color="light" disabled={disabled} onClick={onCopyOsToOd}>
                    {t('global.oph_copy_os_to_od')}
                </Button>
            )}
            {onSwap && (
                <Button type="button" size="xs" color="light" disabled={disabled} onClick={onSwap}>
                    {t('global.oph_swap_eyes')}
                </Button>
            )}
            {onAllNormalOd && (
                <Button type="button" size="xs" color="light" disabled={disabled} onClick={onAllNormalOd}>
                    {t('global.oph_all_normal_od')}
                </Button>
            )}
            {onAllNormalOs && (
                <Button type="button" size="xs" color="light" disabled={disabled} onClick={onAllNormalOs}>
                    {t('global.oph_all_normal_os')}
                </Button>
            )}
        </div>
    );
}

export default function OphthalmologyRegistrationShow({
    registration,
    priorVisits,
    formOptions,
    permissions,
    urls,
}: Props) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [dirty, setDirty] = useState(false);
    const [activeSection, setActiveSection] = useState('registration-history');
    const [compareId, setCompareId] = useState<number | null>(priorVisits[0]?.id ?? null);
    const [removeFundus, setRemoveFundus] = useState(false);
    const [removeAttachmentPaths, setRemoveAttachmentPaths] = useState<string[]>([]);
    const [pendingAttachments, setPendingAttachments] = useState<Array<{ file: File; label: string }>>([]);
    const initialStatus = useRef(registration.status);

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
        diagnostic_tests: registration.diagnostic_tests ?? {},
        diagnosis: registration.diagnosis,
        diagnosis_items: (registration.diagnosis_items ?? []) as DiagnosisItem[],
        treatment_plan: registration.treatment_plan,
        follow_up_date: registration.follow_up_date,
        notes: registration.notes,
        fundus_image: null as File | null,
    });

    const markDirty = () => setDirty(true);

    const doctorOptions = useMemo(
        () => formOptions.doctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
        [formOptions.doctors],
    );

    const setNested = (section: keyof typeof form, path: string[], value: any) => {
        setForm((current) => {
            const root = nestedSet((current[section] as JsonMap) ?? {}, path, value);
            return { ...current, [section]: root };
        });
        markDirty();
    };

    const nestedValue = (section: keyof typeof form, ...path: string[]) =>
        nestedGet(form[section] as JsonMap, ...path);

    const updateForm = (patch: Partial<typeof form>) => {
        setForm((current) => ({ ...current, ...patch }));
        markDirty();
    };

    useEffect(() => {
        if (!dirty) return;
        const onBeforeUnload = (event: BeforeUnloadEvent) => {
            event.preventDefault();
            event.returnValue = '';
        };
        window.addEventListener('beforeunload', onBeforeUnload);
        return () => window.removeEventListener('beforeunload', onBeforeUnload);
    }, [dirty]);

    useEffect(() => {
        const ids = [
            'registration-history',
            'visual-exam',
            'refraction',
            'slit-lamp',
            'fundus',
            'diagnostic-tests',
            'laboratory',
            'prescriptions',
            'assessment-plan',
            'prior-visits',
        ];
        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
                if (visible?.target?.id) setActiveSection(visible.target.id);
            },
            { rootMargin: '-20% 0px -55% 0px', threshold: [0.1, 0.4, 0.7] },
        );
        ids.forEach((id) => {
            const el = document.getElementById(id);
            if (el) observer.observe(el);
        });
        return () => observer.disconnect();
    }, []);

    const sectionComplete = useMemo(() => {
        const hasHistory = OCULAR_HISTORY_FIELDS.some(([key]) =>
            nestedGet(form.medical_history, 'ocular', key, 'value'),
        ) || Boolean(form.chief_complaint);
        const hasVisual = ['od', 'os'].some((eye) =>
            VISUAL_FIELDS.some(([key]) => nestedGet(form.visual_examination, eye, key)),
        );
        const hasRefraction = ['od', 'os'].some((eye) =>
            ['sphere', 'cylinder', 'axis'].some((key) => nestedGet(form.refraction, eye, key)),
        );
        const hasSlit = ['od', 'os'].some((eye) =>
            SLIT_LAMP_FIELDS.some(([key]) => nestedGet(form.slit_lamp_examination, eye, key, 'status')),
        );
        const hasFundus = Boolean(
            nestedGet(form.fundus_examination, 'od_findings')
            || nestedGet(form.fundus_examination, 'os_findings')
            || nestedGet(form.fundus_examination, 'od', 'disc')
            || nestedGet(form.fundus_examination, 'os', 'disc'),
        );
        const hasTests = DIAGNOSTIC_TEST_FIELDS.some(([key]) => {
            const item = form.diagnostic_tests?.[key];
            return item?.od || item?.os || item?.notes || item?.done;
        });
        const hasAssessment = Boolean(form.diagnosis || form.diagnosis_items.length || form.treatment_plan);
        return {
            'registration-history': hasHistory,
            'visual-exam': hasVisual,
            refraction: hasRefraction,
            'slit-lamp': hasSlit,
            fundus: hasFundus,
            'diagnostic-tests': hasTests,
            'assessment-plan': hasAssessment,
        };
    }, [form]);

    const completionPercent = useMemo(() => {
        const keys = Object.keys(sectionComplete) as Array<keyof typeof sectionComplete>;
        const done = keys.filter((key) => sectionComplete[key]).length;
        return Math.round((done / keys.length) * 100);
    }, [sectionComplete]);

    const compareVisit = priorVisits.find((item) => item.id === compareId) ?? null;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (form.status === 'completed' && !form.diagnosis && form.diagnosis_items.length === 0) {
            window.alert(t('global.ophthalmology_diagnosis_required_to_complete'));
            return;
        }

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
                diagnostic_tests: form.diagnostic_tests,
                diagnosis: form.diagnosis,
                diagnosis_items: form.diagnosis_items,
                treatment_plan: form.treatment_plan,
                follow_up_date: form.follow_up_date,
                notes: form.notes,
            });
        }
        if (permissions.changeStatus) payload.status = form.status;
        if (permissions.uploadImages) {
            if (form.fundus_image) payload.fundus_image = form.fundus_image;
            if (removeFundus) payload.remove_fundus_image = true;
            if (pendingAttachments.length) {
                payload.attachment_files = pendingAttachments.map((item) => item.file);
                payload.attachment_labels = pendingAttachments.map((item) => item.label);
            }
            if (removeAttachmentPaths.length) payload.remove_attachment_paths = removeAttachmentPaths;
        }

        setProcessing(true);
        router.post(urls.update, payload, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setDirty(false);
                setPendingAttachments([]);
                setRemoveAttachmentPaths([]);
                setRemoveFundus(false);
            },
            onFinish: () => setProcessing(false),
        });
    };

    const canSave = permissions.edit || permissions.changeStatus || permissions.uploadImages;
    const statusColor = ({ pending: 'warning', in_progress: 'info', completed: 'success', cancelled: 'failure' } as const)[registration.status] ?? 'gray';
    const genderLabel = String(registration.patient.gender) === '1'
        ? t('global.female')
        : String(registration.patient.gender) === '0'
            ? t('global.male')
            : registration.patient.gender;

    const patientFields = [
        [t('global.patient_name'), registration.patient.name, 'bx-user', urls.patient],
        [t('global.father_name'), registration.patient.father_name, 'bx-group', null],
        [t('global.id_card'), registration.patient.id_card, 'bx-id-card', null],
        [t('global.age'), registration.patient.age, 'bx-calendar', null],
        [t('global.gender'), genderLabel, 'bx-male-female', null],
        [t('global.phone'), registration.patient.phone, 'bx-phone', null],
        [t('global.occupation'), registration.patient.occupation, 'bx-briefcase', null],
        [t('global.examiner'), registration.examiner_name, 'bx-plus-medical', null],
    ] as const;

    const sectionLinks = [
        ['registration-history', t('global.registration_and_history'), 'bx-clipboard'],
        ['visual-exam', t('global.visual_examination'), 'bx-show'],
        ['refraction', t('global.refraction'), 'bx-glasses'],
        ['slit-lamp', t('global.slit_lamp_examination'), 'bx-bulb'],
        ['fundus', t('global.fundus_examination'), 'bx-camera'],
        ['diagnostic-tests', t('global.diagnostic_tests'), 'bx-radar'],
        ['laboratory', t('global.lab_tests'), 'bx-test-tube'],
        ['prescriptions', t('global.prescriptions'), 'bx-capsule'],
        ['assessment-plan', t('global.assessment_and_plan'), 'bx-notepad'],
        ['prior-visits', t('global.oph_prior_visits'), 'bx-history'],
    ] as const;

    const addDiagnosis = (suggestion?: { code: string; label: string }) => {
        updateForm({
            diagnosis_items: [
                ...form.diagnosis_items,
                {
                    label: suggestion?.label ?? '',
                    code: suggestion?.code ?? '',
                    laterality: 'ou',
                },
            ],
        });
    };

    const applyRefractionEye = (fn: (current: JsonMap) => JsonMap) => {
        updateForm({ refraction: fn(form.refraction) });
    };

    const applyVisualEye = (fn: (current: JsonMap) => JsonMap) => {
        updateForm({ visual_examination: fn(form.visual_examination) });
    };

    const applySlitEye = (fn: (current: JsonMap) => JsonMap) => {
        updateForm({ slit_lamp_examination: fn(form.slit_lamp_examination) });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.ophthalmology_examination')} />
            <form onSubmit={submit} className="mx-auto max-w-7xl space-y-5 pb-10">
                <Card className="overflow-hidden border border-cyan-100 bg-gradient-to-br from-white via-white to-cyan-50/70 !shadow-sm dark:border-cyan-950 dark:from-gray-900 dark:via-gray-900 dark:to-cyan-950/30">
                    <AppointmentPageHeader
                        title={t('global.ophthalmology_examination')}
                        subtitle={`${registration.ref_no} · ${registration.patient.name}`}
                        icon="bx-show"
                        action={
                            <div className="flex flex-wrap items-center gap-2">
                                <TableBadge color={statusColor}>{t(`global.status_${registration.status}`)}</TableBadge>
                                {dirty && (
                                    <span className="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                                        {t('global.oph_unsaved_changes')}
                                    </span>
                                )}
                                <Button as="a" href={urls.print} target="_blank" rel="noreferrer" color="light" size="sm" type="button">
                                    <i className="bx bx-printer me-2" />
                                    {t('global.print_eye_examination')}
                                </Button>
                                <Link href={urls.appointment}>
                                    <Button color="light" size="sm">
                                        <i className="bx bx-arrow-back me-2" />
                                        {t('global.appointment')}
                                    </Button>
                                </Link>
                                <Link href={urls.index}>
                                    <Button color="light" size="sm">{t('global.back')}</Button>
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

                    {registration.appointment_completed && (
                        <div className="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                            {t('global.oph_readonly_appointment_completed')}
                        </div>
                    )}

                    <div className="mb-3">
                        <div className="mb-1 flex items-center justify-between text-xs font-medium text-gray-500 dark:text-gray-400">
                            <span>{t('global.oph_completion')}</span>
                            <span>{completionPercent}%</span>
                        </div>
                        <div className="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div className="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500 transition-all" style={{ width: `${completionPercent}%` }} />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                        {patientFields.map(([label, value, icon, href]) => (
                            <div key={String(label)} className="flex min-w-0 items-center gap-3 rounded-xl border border-white/80 bg-white/80 px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800/70">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-300">
                                    <i className={`bx ${icon} text-lg`} />
                                </div>
                                <div className="min-w-0">
                                    <div className="truncate text-[11px] font-medium text-gray-500 dark:text-gray-400">{label}</div>
                                    {href && value ? (
                                        <Link href={href} className="mt-0.5 block truncate text-sm font-semibold text-cyan-700 hover:underline dark:text-cyan-300">
                                            {value}
                                        </Link>
                                    ) : (
                                        <div className="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white">{value ?? '—'}</div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                    {registration.appointment_date && (
                        <div className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {t('global.appointment')}: {registration.appointment_date}
                        </div>
                    )}
                </Card>

                <nav className="sticky top-16 z-20 -mx-1 overflow-x-auto rounded-2xl border border-gray-200/80 bg-white/95 p-2 shadow-sm backdrop-blur [scrollbar-width:none] [&::-webkit-scrollbar]:hidden dark:border-gray-700 dark:bg-gray-900/95" aria-label={t('global.ophthalmology_examination')}>
                    <div className="flex min-w-max gap-1">
                        {sectionLinks.map(([id, label, icon], index) => {
                            const complete = (sectionComplete as JsonMap)[id];
                            const active = activeSection === id;
                            return (
                                <a
                                    key={id}
                                    href={`#${id}`}
                                    className={`group flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium transition focus:outline-none focus:ring-2 focus:ring-cyan-500/30 ${
                                        active
                                            ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-200'
                                            : 'text-gray-600 hover:bg-cyan-50 hover:text-cyan-700 dark:text-gray-300 dark:hover:bg-cyan-950/40 dark:hover:text-cyan-300'
                                    }`}
                                >
                                    <span className={`flex h-6 w-6 items-center justify-center rounded-lg ${complete ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'}`}>
                                        <i className={`bx ${complete ? 'bx-check' : icon}`} />
                                    </span>
                                    <span>{index + 1}. {label}</span>
                                </a>
                            );
                        })}
                    </div>
                </nav>

                <SectionCard id="registration-history" step="01" title={t('global.registration_and_history')} icon="bx-clipboard" subtitle={t('global.chief_complaint')} complete={sectionComplete['registration-history']}>
                    <div className="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label>{t('global.examiner')}</Label>
                            <SearchableSelect
                                value={form.examiner_id}
                                onChange={(examiner_id) => updateForm({ examiner_id })}
                                options={doctorOptions}
                                disabled={!permissions.edit}
                            />
                        </div>
                        <div>
                            <Label>{t('global.registration_date')}</Label>
                            <PersianDateInput
                                value={form.registration_date}
                                onChange={(registration_date) => updateForm({ registration_date })}
                                disabled={!permissions.edit}
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')}</Label>
                            <Select
                                value={form.status}
                                onChange={(event) => updateForm({ status: event.target.value })}
                                disabled={!permissions.changeStatus}
                            >
                                <option value="pending">{t('global.status_pending')}</option>
                                <option value="in_progress">{t('global.status_in_progress')}</option>
                                <option value="completed">{t('global.status_completed')}</option>
                                <option value="cancelled">{t('global.status_cancelled')}</option>
                            </Select>
                            {initialStatus.current === 'pending' && form.status === 'pending' && (
                                <p className="mt-1 text-xs text-gray-500">{t('global.oph_status_auto_hint')}</p>
                            )}
                        </div>
                        <div className="md:col-span-3">
                            <Label>{t('global.chief_complaint')}</Label>
                            <Textarea
                                rows={3}
                                value={form.chief_complaint}
                                onChange={(event) => updateForm({ chief_complaint: event.target.value })}
                                disabled={!permissions.edit}
                            />
                        </div>
                    </div>

                    <div className="mt-4">
                        <div className="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                            <i className="bx bx-show text-cyan-600 dark:text-cyan-400" />
                            {t('global.oph_ocular_history')}
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            {OCULAR_HISTORY_FIELDS.map(([key, labelKey]) => (
                                <div key={key} className="rounded-2xl border border-gray-200/80 bg-gray-50/70 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                                    <div className="mb-2 flex items-center justify-between gap-3">
                                        <span className="text-sm font-semibold text-gray-800 dark:text-gray-100">{t(`global.${labelKey}`)}</span>
                                        <YesNoToggle
                                            value={nestedValue('medical_history', 'ocular', key, 'value')}
                                            onChange={(value) => setNested('medical_history', ['ocular', key, 'value'], value)}
                                            disabled={!permissions.edit}
                                        />
                                    </div>
                                    <TextInput
                                        placeholder={t('global.notes')}
                                        value={nestedValue('medical_history', 'ocular', key, 'notes')}
                                        onChange={(event) => setNested('medical_history', ['ocular', key, 'notes'], event.target.value)}
                                        disabled={!permissions.edit}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                </SectionCard>

                <SectionCard id="visual-exam" step="02" title={t('global.visual_examination')} icon="bx-show" complete={sectionComplete['visual-exam']}>
                    <EyeActionBar
                        disabled={!permissions.edit}
                        onCopyOdToOs={() => applyVisualEye((current) => copyEyeData(current, 'od', 'os'))}
                        onCopyOsToOd={() => applyVisualEye((current) => copyEyeData(current, 'os', 'od'))}
                        onSwap={() => applyVisualEye((current) => swapEyeData(current))}
                    />
                    <div className={`${tableShellClass} overflow-x-auto`}>
                        <table className="w-full text-sm">
                            <thead className={tableHeaderClass}>
                                <tr>
                                    <th className="p-3 text-start">{t('global.oph_measurement')}</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">OD</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">OS</th>
                                </tr>
                            </thead>
                            <tbody>
                                {VISUAL_FIELDS.map(([key, labelKey]) => (
                                    <tr key={key} className={tableRowClass}>
                                        <td className={tableCellClass}>
                                            {t(`global.${labelKey}`)}
                                            {key === 'intraocular_pressure' && <span className="ms-1 text-xs text-gray-400">(mmHg)</span>}
                                        </td>
                                        {(['od', 'os'] as EyeSide[]).map((eye) => {
                                            const value = nestedValue('visual_examination', eye, key);
                                            const isIop = key === 'intraocular_pressure';
                                            const isVa = ['visual_acuity', 'best_corrected_acuity', 'pinhole_vision', 'vision_with_glasses', 'near_vision'].includes(key);
                                            return (
                                                <td key={eye} className="p-2.5">
                                                    <div className="space-y-1">
                                                        <TextInput
                                                            list={isVa ? 'va-presets' : undefined}
                                                            value={value}
                                                            onChange={(event) => setNested('visual_examination', [eye, key], event.target.value)}
                                                            disabled={!permissions.edit}
                                                            color={isIop && iopAlert(value) ? 'failure' : undefined}
                                                        />
                                                        {isIop && iopAlert(value) && (
                                                            <div className="text-xs font-medium text-rose-600">{t('global.oph_iop_high')}</div>
                                                        )}
                                                    </div>
                                                </td>
                                            );
                                        })}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <datalist id="va-presets">
                        {VA_PRESETS.map((item) => <option key={item} value={item} />)}
                    </datalist>

                    <div className="mt-4 grid gap-4 md:grid-cols-3">
                        <div>
                            <Label>{t('global.oph_iop_method')}</Label>
                            <Select
                                value={nestedValue('visual_examination', 'iop_method')}
                                onChange={(event) => setNested('visual_examination', ['iop_method'], event.target.value)}
                                disabled={!permissions.edit}
                            >
                                <option value="">—</option>
                                {IOP_METHODS.map(([value, labelKey]) => (
                                    <option key={value} value={value}>{t(`global.${labelKey}`)}</option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.oph_iop_time')}</Label>
                            <TextInput
                                type="time"
                                value={nestedValue('visual_examination', 'iop_time')}
                                onChange={(event) => setNested('visual_examination', ['iop_time'], event.target.value)}
                                disabled={!permissions.edit}
                            />
                        </div>
                        <div>
                            <Label>{t('global.oph_rapd')}</Label>
                            <Select
                                value={nestedValue('visual_examination', 'rapd')}
                                onChange={(event) => setNested('visual_examination', ['rapd'], event.target.value)}
                                disabled={!permissions.edit}
                            >
                                <option value="">—</option>
                                <option value="none">{t('global.oph_rapd_none')}</option>
                                <option value="od">{t('global.oph_rapd_od')}</option>
                                <option value="os">{t('global.oph_rapd_os')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.oph_pupil_od')}</Label>
                            <TextInput
                                value={nestedValue('visual_examination', 'pupil_size', 'od')}
                                onChange={(event) => setNested('visual_examination', ['pupil_size', 'od'], event.target.value)}
                                disabled={!permissions.edit}
                                placeholder="mm"
                            />
                        </div>
                        <div>
                            <Label>{t('global.oph_pupil_os')}</Label>
                            <TextInput
                                value={nestedValue('visual_examination', 'pupil_size', 'os')}
                                onChange={(event) => setNested('visual_examination', ['pupil_size', 'os'], event.target.value)}
                                disabled={!permissions.edit}
                                placeholder="mm"
                            />
                        </div>
                        <div>
                            <Label>{t('global.oph_cover_test')}</Label>
                            <TextInput
                                value={nestedValue('visual_examination', 'cover_test')}
                                onChange={(event) => setNested('visual_examination', ['cover_test'], event.target.value)}
                                disabled={!permissions.edit}
                            />
                        </div>
                        {[
                            ['squint_assessment', 'oph_squint'],
                            ['blood_pressure', 'oph_blood_pressure'],
                            ['color_vision', 'oph_color_vision'],
                        ].map(([key, labelKey]) => (
                            <div key={key}>
                                <Label>{t(`global.${labelKey}`)}</Label>
                                <TextInput
                                    value={nestedValue('visual_examination', key)}
                                    onChange={(event) => setNested('visual_examination', [key], event.target.value)}
                                    disabled={!permissions.edit}
                                />
                            </div>
                        ))}
                    </div>

                    <div className="mt-4 rounded-2xl border border-dashed border-cyan-200 bg-gradient-to-br from-slate-50 via-white to-cyan-50/60 p-4 dark:border-cyan-900 dark:from-gray-900 dark:via-gray-900 dark:to-cyan-950/20">
                        <div className="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                            <i className="bx bx-show text-cyan-600 dark:text-cyan-400" />
                            {t('global.eye_diagram')}
                        </div>
                        <EyeExaminationDiagram
                            visualExamination={form.visual_examination}
                            refraction={form.refraction}
                        />
                    </div>
                </SectionCard>

                <SectionCard id="refraction" step="03" title={t('global.refraction')} icon="bx-glasses" complete={sectionComplete.refraction}>
                    <EyeActionBar
                        disabled={!permissions.edit}
                        onCopyOdToOs={() => applyRefractionEye((current) => copyEyeData(current, 'od', 'os'))}
                        onCopyOsToOd={() => applyRefractionEye((current) => copyEyeData(current, 'os', 'od'))}
                        onSwap={() => applyRefractionEye((current) => swapEyeData(current))}
                    />
                    <div className="mb-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>{t('global.oph_refraction_type')}</Label>
                            <Select
                                value={nestedValue('refraction', 'type')}
                                onChange={(event) => setNested('refraction', ['type'], event.target.value)}
                                disabled={!permissions.edit}
                            >
                                <option value="">—</option>
                                <option value="subjective">{t('global.oph_refraction_subjective')}</option>
                                <option value="objective">{t('global.oph_refraction_objective')}</option>
                                <option value="both">{t('global.oph_refraction_both')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>IPD</Label>
                            <TextInput value={nestedValue('refraction', 'ipd')} onChange={(event) => setNested('refraction', ['ipd'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                    </div>
                    <div className={`${tableShellClass} overflow-x-auto`}>
                        <table className="w-full text-sm">
                            <thead className={tableHeaderClass}>
                                <tr>
                                    <th className="p-3 text-start">{t('global.oph_measurement')}</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">OD</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">OS</th>
                                </tr>
                            </thead>
                            <tbody>
                                {REFRACTION_FIELDS.map(([key, labelKey]) => (
                                    <tr key={key} className={tableRowClass}>
                                        <td className={tableCellClass}>{t(`global.${labelKey}`)}</td>
                                        {(['od', 'os'] as EyeSide[]).map((eye) => {
                                            const value = nestedValue('refraction', eye, key);
                                            const invalidAxis = key === 'axis' && !axisValid(value);
                                            return (
                                                <td key={eye} className="p-2.5">
                                                    <TextInput
                                                        value={value}
                                                        onChange={(event) => setNested('refraction', [eye, key], event.target.value)}
                                                        disabled={!permissions.edit}
                                                        color={invalidAxis ? 'failure' : undefined}
                                                    />
                                                    {invalidAxis && <div className="mt-1 text-xs text-rose-600">{t('global.oph_axis_range')}</div>}
                                                </td>
                                            );
                                        })}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="mt-4">
                        <Label>{t('global.notes')}</Label>
                        <TextInput value={nestedValue('refraction', 'notes')} onChange={(event) => setNested('refraction', ['notes'], event.target.value)} disabled={!permissions.edit} />
                    </div>
                </SectionCard>

                <SectionCard id="slit-lamp" step="04" title={t('global.slit_lamp_examination')} icon="bx-bulb" complete={sectionComplete['slit-lamp']}>
                    <EyeActionBar
                        disabled={!permissions.edit}
                        onCopyOdToOs={() => applySlitEye((current) => copyEyeData(current, 'od', 'os'))}
                        onCopyOsToOd={() => applySlitEye((current) => copyEyeData(current, 'os', 'od'))}
                        onAllNormalOd={() => applySlitEye((current) => markSlitLampAllNormal(current, 'od'))}
                        onAllNormalOs={() => applySlitEye((current) => markSlitLampAllNormal(current, 'os'))}
                    />
                    <div className={`${tableShellClass} overflow-x-auto`}>
                        <table className="w-full min-w-[900px] text-sm">
                            <thead className={tableHeaderClass}>
                                <tr>
                                    <th className="p-3 text-start">{t('global.oph_finding')}</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">{t('global.oph_status_od')}</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">{t('global.oph_notes_od')}</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">{t('global.oph_status_os')}</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">{t('global.oph_notes_os')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {SLIT_LAMP_FIELDS.map(([key, labelKey]) => (
                                    <tr key={key} className={tableRowClass}>
                                        <td className={tableCellClass}>{t(`global.${labelKey}`)}</td>
                                        {(['od', 'os'] as EyeSide[]).flatMap((eye) => [
                                            <td key={`${eye}-status`} className="p-2.5">
                                                <select
                                                    className={inputClass}
                                                    value={nestedValue('slit_lamp_examination', eye, key, 'status')}
                                                    onChange={(event) => setNested('slit_lamp_examination', [eye, key, 'status'], event.target.value)}
                                                    disabled={!permissions.edit}
                                                >
                                                    <option value="">—</option>
                                                    <option value="normal">{t('global.oph_normal')}</option>
                                                    <option value="abnormal">{t('global.oph_abnormal')}</option>
                                                </select>
                                            </td>,
                                            <td key={`${eye}-notes`} className="p-2.5">
                                                <TextInput
                                                    list="slit-phrases"
                                                    value={nestedValue('slit_lamp_examination', eye, key, 'notes')}
                                                    onChange={(event) => setNested('slit_lamp_examination', [eye, key, 'notes'], event.target.value)}
                                                    disabled={!permissions.edit}
                                                />
                                            </td>,
                                        ])}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <datalist id="slit-phrases">
                        {SLIT_PHRASES.map((item) => <option key={item} value={item} />)}
                    </datalist>
                </SectionCard>

                <SectionCard id="fundus" step="05" title={t('global.fundus_examination')} icon="bx-camera" complete={sectionComplete.fundus}>
                    <div className="grid gap-4 md:grid-cols-2">
                        {(['od', 'os'] as EyeSide[]).map((eye) => (
                            <div key={eye} className={`rounded-2xl border p-4 ${eye === 'od' ? 'border-cyan-200 bg-cyan-50/50 dark:border-cyan-900 dark:bg-cyan-950/20' : 'border-violet-200 bg-violet-50/50 dark:border-violet-900 dark:bg-violet-950/20'}`}>
                                <div className="mb-3 text-sm font-semibold">{eye === 'od' ? t('global.oph_fundus_od') : t('global.oph_fundus_os')}</div>
                                <div className="space-y-3">
                                    {FUNDUS_STRUCTURED_FIELDS.map(([key, labelKey]) => (
                                        <div key={key}>
                                            <Label>{t(`global.${labelKey}`)}</Label>
                                            <TextInput
                                                value={nestedValue('fundus_examination', eye, key)}
                                                onChange={(event) => setNested('fundus_examination', [eye, key], event.target.value)}
                                                disabled={!permissions.edit}
                                            />
                                        </div>
                                    ))}
                                    <div>
                                        <Label>{t('global.notes')}</Label>
                                        <Textarea
                                            rows={3}
                                            value={nestedValue('fundus_examination', eye === 'od' ? 'od_findings' : 'os_findings')}
                                            onChange={(event) => setNested('fundus_examination', [eye === 'od' ? 'od_findings' : 'os_findings'], event.target.value)}
                                            disabled={!permissions.edit}
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}
                        <div>
                            <Label>{t('global.oph_dilation_status')}</Label>
                            <Select value={nestedValue('fundus_examination', 'dilation_status')} onChange={(event) => setNested('fundus_examination', ['dilation_status'], event.target.value)} disabled={!permissions.edit}>
                                <option value="">—</option>
                                <option value="not_dilated">{t('global.oph_not_dilated')}</option>
                                <option value="dilated">{t('global.oph_dilated')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.oph_dilation_time')}</Label>
                            <TextInput type="time" value={nestedValue('fundus_examination', 'dilation_time')} onChange={(event) => setNested('fundus_examination', ['dilation_time'], event.target.value)} disabled={!permissions.edit} />
                        </div>
                        <div className="md:col-span-2">
                            <Label>{t('global.oph_fundus_image')}</Label>
                            <input
                                type="file"
                                accept="image/*,.pdf"
                                className={inputClass}
                                onChange={(event) => {
                                    updateForm({ fundus_image: event.target.files?.[0] ?? null });
                                    setRemoveFundus(false);
                                }}
                                disabled={!permissions.uploadImages}
                            />
                            {registration.fundus_image_url && !removeFundus && (
                                <div className="mt-2 flex flex-wrap items-center gap-3">
                                    <a href={registration.fundus_image_url} target="_blank" rel="noreferrer" className="text-sm text-cyan-600 hover:underline">
                                        {t('global.oph_view_current_attachment')}
                                    </a>
                                    {permissions.uploadImages && (
                                        <button type="button" className="text-sm text-rose-600 hover:underline" onClick={() => { setRemoveFundus(true); markDirty(); }}>
                                            {t('global.oph_remove_attachment')}
                                        </button>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </SectionCard>

                <SectionCard id="diagnostic-tests" step="06" title={t('global.diagnostic_tests')} icon="bx-radar" complete={sectionComplete['diagnostic-tests']}>
                    <div className={`${tableShellClass} overflow-x-auto`}>
                        <table className="w-full min-w-[800px] text-sm">
                            <thead className={tableHeaderClass}>
                                <tr>
                                    <th className="p-3 text-start">{t('global.oph_test')}</th>
                                    <th className="p-3 text-start">{t('global.oph_done')}</th>
                                    <th className="bg-cyan-50 p-3 text-start text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">OD</th>
                                    <th className="bg-violet-50 p-3 text-start text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">OS</th>
                                    <th className="p-3 text-start">{t('global.notes')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {DIAGNOSTIC_TEST_FIELDS.map(([key, labelKey]) => (
                                    <tr key={key} className={tableRowClass}>
                                        <td className={tableCellClass}>{t(`global.${labelKey}`)}</td>
                                        <td className="p-2.5">
                                            <input
                                                type="checkbox"
                                                className="h-4 w-4 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500"
                                                checked={Boolean(nestedValue('diagnostic_tests', key, 'done'))}
                                                onChange={(event) => setNested('diagnostic_tests', [key, 'done'], event.target.checked)}
                                                disabled={!permissions.edit}
                                            />
                                        </td>
                                        <td className="p-2.5">
                                            <TextInput value={nestedValue('diagnostic_tests', key, 'od')} onChange={(event) => setNested('diagnostic_tests', [key, 'od'], event.target.value)} disabled={!permissions.edit} />
                                        </td>
                                        <td className="p-2.5">
                                            <TextInput value={nestedValue('diagnostic_tests', key, 'os')} onChange={(event) => setNested('diagnostic_tests', [key, 'os'], event.target.value)} disabled={!permissions.edit} />
                                        </td>
                                        <td className="p-2.5">
                                            <TextInput value={nestedValue('diagnostic_tests', key, 'notes')} onChange={(event) => setNested('diagnostic_tests', [key, 'notes'], event.target.value)} disabled={!permissions.edit} />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="mt-4">
                        <Label>{t('global.oph_test_notes')}</Label>
                        <Textarea rows={2} value={nestedValue('diagnostic_tests', 'general_notes')} onChange={(event) => setNested('diagnostic_tests', ['general_notes'], event.target.value)} disabled={!permissions.edit} />
                    </div>
                </SectionCard>

                <div id="laboratory" className="scroll-mt-28">
                    <LabTestSection appointmentId={registration.appointment_id} embedded />
                </div>
                <div id="prescriptions" className="scroll-mt-28">
                    <PrescriptionSection appointmentId={registration.appointment_id} embedded />
                </div>

                <SectionCard id="assessment-plan" step="08" title={t('global.assessment_and_plan')} icon="bx-notepad" complete={sectionComplete['assessment-plan']}>
                    <div className="mb-4">
                        <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <Label>{t('global.oph_diagnosis_items')}</Label>
                            <Button type="button" size="xs" color="light" disabled={!permissions.edit} onClick={() => addDiagnosis()}>
                                <i className="bx bx-plus me-1" />
                                {t('global.oph_add_diagnosis')}
                            </Button>
                        </div>
                        <div className="mb-3 flex flex-wrap gap-2">
                            {formOptions.diagnosisSuggestions.map((item) => (
                                <button
                                    key={item.code}
                                    type="button"
                                    disabled={!permissions.edit}
                                    onClick={() => addDiagnosis(item)}
                                    className="rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-xs font-medium text-cyan-800 hover:bg-cyan-100 disabled:opacity-50 dark:border-cyan-900 dark:bg-cyan-950/40 dark:text-cyan-200"
                                >
                                    {item.code} · {item.label}
                                </button>
                            ))}
                        </div>
                        <div className="space-y-2">
                            {form.diagnosis_items.map((item, index) => (
                                <div key={`${item.code}-${index}`} className="grid gap-2 rounded-xl border border-gray-200 p-3 md:grid-cols-4 dark:border-gray-700">
                                    <TextInput
                                        placeholder={t('global.oph_diagnosis_label')}
                                        value={item.label}
                                        disabled={!permissions.edit}
                                        onChange={(event) => {
                                            const next = [...form.diagnosis_items];
                                            next[index] = { ...next[index], label: event.target.value };
                                            updateForm({ diagnosis_items: next });
                                        }}
                                    />
                                    <TextInput
                                        placeholder={t('global.oph_diagnosis_code')}
                                        value={item.code}
                                        disabled={!permissions.edit}
                                        onChange={(event) => {
                                            const next = [...form.diagnosis_items];
                                            next[index] = { ...next[index], code: event.target.value };
                                            updateForm({ diagnosis_items: next });
                                        }}
                                    />
                                    <Select
                                        value={item.laterality}
                                        disabled={!permissions.edit}
                                        onChange={(event) => {
                                            const next = [...form.diagnosis_items];
                                            next[index] = { ...next[index], laterality: event.target.value };
                                            updateForm({ diagnosis_items: next });
                                        }}
                                    >
                                        <option value="ou">OU</option>
                                        <option value="od">OD</option>
                                        <option value="os">OS</option>
                                    </Select>
                                    <Button
                                        type="button"
                                        color="failure"
                                        size="sm"
                                        disabled={!permissions.edit}
                                        onClick={() => updateForm({ diagnosis_items: form.diagnosis_items.filter((_, i) => i !== index) })}
                                    >
                                        {t('global.delete')}
                                    </Button>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>{t('global.diagnosis')}</Label>
                            <Textarea rows={4} value={form.diagnosis} onChange={(event) => updateForm({ diagnosis: event.target.value })} disabled={!permissions.edit} />
                        </div>
                        <div>
                            <Label>{t('global.treatment_plan')}</Label>
                            <Textarea rows={4} value={form.treatment_plan} onChange={(event) => updateForm({ treatment_plan: event.target.value })} disabled={!permissions.edit} />
                        </div>
                        <div>
                            <Label>{t('global.follow_up_date')}</Label>
                            <PersianDateInput value={form.follow_up_date} onChange={(follow_up_date) => updateForm({ follow_up_date })} disabled={!permissions.edit} />
                            {form.follow_up_date && (
                                <p className="mt-1 text-xs text-gray-500">{t('global.oph_follow_up_hint')}</p>
                            )}
                        </div>
                        <div>
                            <Label>{t('global.notes')}</Label>
                            <Textarea rows={3} value={form.notes} onChange={(event) => updateForm({ notes: event.target.value })} disabled={!permissions.edit} />
                        </div>
                    </div>

                    <div className="mt-4 rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                        <div className="mb-3 flex items-center justify-between gap-2">
                            <div className="text-sm font-semibold text-gray-800 dark:text-gray-100">{t('global.attachments')}</div>
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            <div>
                                <Label>{t('global.oph_add_attachments')}</Label>
                                <input
                                    type="file"
                                    multiple
                                    accept="image/*,.pdf"
                                    className={inputClass}
                                    disabled={!permissions.uploadImages}
                                    onChange={(event) => {
                                        const files = Array.from(event.target.files ?? []);
                                        if (!files.length) return;
                                        setPendingAttachments((current) => [
                                            ...current,
                                            ...files.map((file) => ({ file, label: 'other' })),
                                        ]);
                                        markDirty();
                                        event.target.value = '';
                                    }}
                                />
                            </div>
                            <div className="space-y-2">
                                {pendingAttachments.map((item, index) => (
                                    <div key={`${item.file.name}-${index}`} className="flex items-center gap-2">
                                        <span className="min-w-0 flex-1 truncate text-xs text-gray-600 dark:text-gray-300">{item.file.name}</span>
                                        <select
                                            className={`${inputClass} !w-36`}
                                            value={item.label}
                                            onChange={(event) => {
                                                setPendingAttachments((current) => current.map((row, i) => (i === index ? { ...row, label: event.target.value } : row)));
                                                markDirty();
                                            }}
                                        >
                                            {ATTACHMENT_LABELS.map(([value, labelKey]) => (
                                                <option key={value} value={value}>{t(`global.${labelKey}`)}</option>
                                            ))}
                                        </select>
                                        <button type="button" className="text-xs text-rose-600" onClick={() => { setPendingAttachments((current) => current.filter((_, i) => i !== index)); markDirty(); }}>
                                            {t('global.delete')}
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="mt-3 space-y-2">
                            {registration.attachments
                                .filter((item) => item.path && !removeAttachmentPaths.includes(item.path))
                                .map((item) => {
                                    const labelKey = ATTACHMENT_LABELS.find(([value]) => value === item.label)?.[1];
                                    const labelText = labelKey
                                        ? t(`global.${labelKey}`)
                                        : (item.original_name || item.label || item.path);
                                    return (
                                    <div key={item.path} className="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-gray-50 px-3 py-2 text-sm dark:bg-gray-800/60">
                                        <a href={item.url ?? '#'} target="_blank" rel="noreferrer" className="text-cyan-700 hover:underline dark:text-cyan-300">
                                            {labelText}
                                        </a>
                                        {permissions.uploadImages && item.path && (
                                            <button
                                                type="button"
                                                className="text-xs text-rose-600"
                                                onClick={() => {
                                                    setRemoveAttachmentPaths((current) => [...current, item.path as string]);
                                                    markDirty();
                                                }}
                                            >
                                                {t('global.oph_remove_attachment')}
                                            </button>
                                        )}
                                    </div>
                                    );
                                })}
                        </div>
                    </div>
                </SectionCard>

                <SectionCard id="prior-visits" step="09" title={t('global.oph_prior_visits')} icon="bx-history" subtitle={t('global.oph_prior_visits_hint')}>
                    {priorVisits.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500 dark:border-gray-700">
                            {t('global.oph_no_prior_visits')}
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <div className="flex flex-wrap gap-2">
                                {priorVisits.map((visit) => (
                                    <button
                                        key={visit.id}
                                        type="button"
                                        onClick={() => setCompareId(visit.id)}
                                        className={`rounded-xl border px-3 py-2 text-xs font-medium transition ${
                                            compareId === visit.id
                                                ? 'border-cyan-400 bg-cyan-50 text-cyan-800 dark:border-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-200'
                                                : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        }`}
                                    >
                                        {visit.registration_date || visit.ref_no}
                                    </button>
                                ))}
                            </div>
                            {compareVisit && (
                                <div className="grid gap-4 lg:grid-cols-2">
                                    <div className="rounded-2xl border border-cyan-200 bg-cyan-50/40 p-4 dark:border-cyan-900 dark:bg-cyan-950/20">
                                        <div className="mb-2 text-sm font-semibold">{t('global.oph_current_visit')}</div>
                                        <CompareMetrics visual={form.visual_examination} refraction={form.refraction} diagnosis={form.diagnosis} />
                                    </div>
                                    <div className="rounded-2xl border border-violet-200 bg-violet-50/40 p-4 dark:border-violet-900 dark:bg-violet-950/20">
                                        <div className="mb-2 flex items-center justify-between gap-2">
                                            <div className="text-sm font-semibold">{t('global.oph_previous_visit')} · {compareVisit.registration_date}</div>
                                            <a href={compareVisit.show_url} className="text-xs text-cyan-700 hover:underline dark:text-cyan-300">{t('global.show')}</a>
                                        </div>
                                        <CompareMetrics
                                            visual={compareVisit.visual_examination}
                                            refraction={compareVisit.refraction}
                                            diagnosis={compareVisit.diagnosis}
                                        />
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                </SectionCard>

                {canSave && (
                    <div className="sticky bottom-4 flex flex-wrap justify-end gap-2">
                        <Button as="a" href={urls.print} target="_blank" rel="noreferrer" color="light" size="lg" type="button" className="shadow-lg">
                            <i className="bx bx-printer me-2" />
                            {t('global.print_eye_examination')}
                        </Button>
                        <Button type="submit" color="blue" size="lg" disabled={processing} className="shadow-lg">
                            {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-save me-2" />}
                            {t('global.save_ophthalmology_examination')}
                        </Button>
                    </div>
                )}
                {!canSave && (
                    <div className="sticky bottom-4 flex justify-end">
                        <Button as="a" href={urls.print} target="_blank" rel="noreferrer" color="light" size="lg" type="button" className="shadow-lg">
                            <i className="bx bx-printer me-2" />
                            {t('global.print_eye_examination')}
                        </Button>
                    </div>
                )}
            </form>
        </DashboardLayout>
    );
}

function CompareMetrics({
    visual,
    refraction,
    diagnosis,
}: {
    visual: JsonMap;
    refraction: JsonMap;
    diagnosis: string | null;
}) {
    const { t } = useTranslation();
    const rows = [
        [t('global.oph_va'), nestedGet(visual, 'od', 'visual_acuity'), nestedGet(visual, 'os', 'visual_acuity')],
        [t('global.oph_bcva'), nestedGet(visual, 'od', 'best_corrected_acuity'), nestedGet(visual, 'os', 'best_corrected_acuity')],
        [t('global.oph_iop'), nestedGet(visual, 'od', 'intraocular_pressure'), nestedGet(visual, 'os', 'intraocular_pressure')],
        ['SPH', nestedGet(refraction, 'od', 'sphere'), nestedGet(refraction, 'os', 'sphere')],
        ['CYL', nestedGet(refraction, 'od', 'cylinder'), nestedGet(refraction, 'os', 'cylinder')],
        ['Axis', nestedGet(refraction, 'od', 'axis'), nestedGet(refraction, 'os', 'axis')],
        [t('global.oph_add'), nestedGet(refraction, 'od', 'add'), nestedGet(refraction, 'os', 'add')],
    ];

    return (
        <div className="space-y-2 text-sm">
            <table className="w-full text-xs">
                <thead>
                    <tr className="text-gray-500">
                        <th className="py-1 text-start">{t('global.oph_measurement')}</th>
                        <th className="py-1 text-start">OD</th>
                        <th className="py-1 text-start">OS</th>
                    </tr>
                </thead>
                <tbody>
                    {rows.map(([label, od, os]) => (
                        <tr key={String(label)} className="border-t border-white/40 dark:border-gray-700/60">
                            <td className="py-1.5 font-medium">{label}</td>
                            <td className="py-1.5 tabular-nums">{od || '—'}</td>
                            <td className="py-1.5 tabular-nums">{os || '—'}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
            <div>
                <div className="text-xs text-gray-500">{t('global.diagnosis')}</div>
                <div className="mt-0.5 font-medium">{diagnosis || '—'}</div>
            </div>
        </div>
    );
}
