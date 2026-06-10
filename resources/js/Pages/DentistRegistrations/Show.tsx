import { Head, Link, router } from '@inertiajs/react';
import {
    Badge,
    Button,
    Card,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
    TextInput,
} from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import LabTestSection from '../../Components/Appointments/Sections/LabTestSection';
import PrescriptionSection from '../../Components/Appointments/Sections/PrescriptionSection';
import DentistRegistrationStatusBadge from '../../Components/DentistRegistrations/DentistRegistrationStatusBadge';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    DentistRegistrationDetail,
    DentistRegistrationFormOptions,
    DentistRegistrationShowPermissions,
} from '../../types/dentistRegistration';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowDentistRegistrationProps {
    registration: DentistRegistrationDetail;
    formOptions: DentistRegistrationFormOptions | null;
    permissions: DentistRegistrationShowPermissions;
    urls: Record<string, string | null>;
}

type TabKey = 'examinations' | 'treatments' | 'xrays' | 'notes' | 'prescription' | 'dental_chart';

const TOOTH_OPTIONS = [
    ...Array.from({ length: 8 }, (_, index) => 11 + index),
    ...Array.from({ length: 8 }, (_, index) => 21 + index),
    ...Array.from({ length: 8 }, (_, index) => 31 + index),
    ...Array.from({ length: 8 }, (_, index) => 41 + index),
];

const selectClassName =
    'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white';

export default function ShowDentistRegistration({
    registration,
    formOptions,
    permissions,
    urls,
}: ShowDentistRegistrationProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const initialTab = (): TabKey => {
        if (typeof window === 'undefined') return 'examinations';
        const tab = new URLSearchParams(window.location.search).get('tab');
        const allowed: TabKey[] = ['examinations', 'treatments', 'xrays', 'notes', 'prescription', 'dental_chart'];
        return allowed.includes(tab as TabKey) ? (tab as TabKey) : 'examinations';
    };

    const [activeTab, setActiveTab] = useState<TabKey>(initialTab);
    const [editOpen, setEditOpen] = useState(false);
    const [treatmentOpen, setTreatmentOpen] = useState(false);
    const [xrayOpen, setXrayOpen] = useState(false);
    const [noteOpen, setNoteOpen] = useState(false);

    const [editForm, setEditForm] = useState({
        dentist_id: registration.dentist_id ? String(registration.dentist_id) : '',
        registration_date: registration.registration_date ?? '',
        status: registration.status,
        notes: registration.notes ?? '',
    });

    const [treatmentForm, setTreatmentForm] = useState({
        treatment_type: '',
        tooth_number: '',
        treatment_description: '',
        treatment_date: '',
        status: 'planned',
        cost: '',
        notes: '',
    });

    const [xrayForm, setXrayForm] = useState({
        xray_type: '',
        xray_date: '',
        description: '',
        notes: '',
        file: null as File | null,
    });

    const [noteForm, setNoteForm] = useState({
        note_date: '',
        note_type: 'general',
        content: '',
    });

    useEffect(() => {
        if (typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('edit') === '1') {
            setEditOpen(true);
        }
    }, []);

    const dentistOptions = useMemo(
        () =>
            formOptions?.dentists.map((item) => ({
                value: String(item.id),
                label: item.name,
            })) ?? [],
        [formOptions],
    );

    const tabs: Array<{ key: TabKey; label: string; icon: string; count?: number }> = [
        { key: 'examinations', label: t('global.examinations'), icon: 'bx-search-alt' },
        { key: 'treatments', label: t('global.treatments'), icon: 'bx-plus-medical', count: registration.counts.treatments },
        { key: 'xrays', label: t('global.xrays'), icon: 'bx-image', count: registration.counts.xrays },
        { key: 'notes', label: t('global.notes'), icon: 'bx-note', count: registration.counts.notes },
        { key: 'prescription', label: t('global.prescription'), icon: 'bx-notepad', count: registration.counts.prescriptions },
        { key: 'dental_chart', label: t('global.dental_chart'), icon: 'bx-grid-alt', count: registration.counts.charts },
    ];

    const runAction = (callback: () => void) => {
        setProcessing(true);
        callback();
    };

    const handleUpdate = (event: FormEvent) => {
        event.preventDefault();
        runAction(() =>
            router.put(
                urls.update!,
                {
                    dentist_id: editForm.dentist_id ? Number(editForm.dentist_id) : null,
                    registration_date: editForm.registration_date,
                    status: editForm.status,
                    notes: editForm.notes || null,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => setEditOpen(false),
                    onFinish: () => setProcessing(false),
                },
            ),
        );
    };

    const handleDelete = () => {
        if (!window.confirm(t('global.are_you_sure'))) return;
        runAction(() =>
            router.delete(urls.destroy!, {
                onFinish: () => setProcessing(false),
            }),
        );
    };

    const postStatus = (url: string) => {
        runAction(() =>
            router.post(url, {}, { preserveScroll: true, onFinish: () => setProcessing(false) }),
        );
    };

    const handleTreatment = (event: FormEvent) => {
        event.preventDefault();
        runAction(() =>
            router.post(
                urls.storeTreatment!,
                {
                    ...treatmentForm,
                    tooth_number: treatmentForm.tooth_number ? Number(treatmentForm.tooth_number) : null,
                    cost: treatmentForm.cost ? Number(treatmentForm.cost) : null,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        setTreatmentOpen(false);
                        setTreatmentForm({
                            treatment_type: '',
                            tooth_number: '',
                            treatment_description: '',
                            treatment_date: '',
                            status: 'planned',
                            cost: '',
                            notes: '',
                        });
                    },
                    onFinish: () => setProcessing(false),
                },
            ),
        );
    };

    const handleXray = (event: FormEvent) => {
        event.preventDefault();
        const formData = new FormData();
        formData.append('xray_type', xrayForm.xray_type);
        formData.append('xray_date', xrayForm.xray_date);
        if (xrayForm.description) formData.append('description', xrayForm.description);
        if (xrayForm.notes) formData.append('notes', xrayForm.notes);
        if (xrayForm.file) formData.append('file', xrayForm.file);

        runAction(() =>
            router.post(urls.storeXray!, formData, {
                preserveScroll: true,
                forceFormData: true,
                onSuccess: () => {
                    setXrayOpen(false);
                    setXrayForm({ xray_type: '', xray_date: '', description: '', notes: '', file: null });
                },
                onFinish: () => setProcessing(false),
            }),
        );
    };

    const handleNote = (event: FormEvent) => {
        event.preventDefault();
        runAction(() =>
            router.post(urls.storeNote!, noteForm, {
                preserveScroll: true,
                onSuccess: () => {
                    setNoteOpen(false);
                    setNoteForm({ note_date: '', note_type: 'general', content: '' });
                },
                onFinish: () => setProcessing(false),
            }),
        );
    };

    const destroyItem = (url: string) => {
        if (!window.confirm(t('global.are_you_sure'))) return;
        runAction(() => router.delete(url, { preserveScroll: true, onFinish: () => setProcessing(false) }));
    };

    const infoCards = [
        [t('global.ref_no'), registration.ref_no],
        [t('global.patient_name'), registration.patient_name],
        [t('global.dentist'), registration.dentist_name],
        [t('global.status'), null],
        [t('global.registration_date'), registration.registration_date],
        [t('global.appointment_date'), registration.appointment_date],
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.dentist_registration_details')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.dentist_registration_details')}
                    subtitle={`#${registration.ref_no ?? registration.id}`}
                    icon="bx-plus-medical"
                    accent="from-blue-500 to-indigo-600"
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            {permissions.edit && (
                                <Button color="warning" size="sm" onClick={() => setEditOpen(true)}>
                                    <i className="bx bx-edit me-2" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            {permissions.delete && (
                                <Button color="failure" size="sm" onClick={handleDelete} disabled={processing}>
                                    <i className="bx bx-trash me-2" />
                                    {t('global.delete')}
                                </Button>
                            )}
                        </div>
                    }
                />

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-center text-sm text-gray-900 dark:text-white">
                        {t('global.registration_information')}
                    </h2>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {infoCards.map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-800/40"
                            >
                                <p className="text-xs text-gray-500 dark:text-gray-400">{label}</p>
                                {label === t('global.status') ? (
                                    <div className="mt-2">
                                        <DentistRegistrationStatusBadge status={registration.status} />
                                    </div>
                                ) : (
                                    <p className="mt-2 text-gray-900 dark:text-white">{value ?? '—'}</p>
                                )}
                            </div>
                        ))}
                    </div>

                    {registration.notes && (
                        <div className="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-800/40">
                            <span className="text-gray-600 dark:text-gray-300">{t('global.notes')}:</span>{' '}
                            {registration.notes}
                        </div>
                    )}

                    {permissions.markStatus && (
                        <div className="mt-4 flex flex-wrap justify-center gap-2">
                            {registration.status !== 'completed' && (
                                <Button size="sm" color="success" onClick={() => postStatus(urls.markCompleted!)}>
                                    {t('global.mark_completed')}
                                </Button>
                            )}
                            {registration.status !== 'in_progress' && registration.status !== 'completed' && (
                                <Button size="sm" color="info" onClick={() => postStatus(urls.markInProgress!)}>
                                    {t('global.mark_in_progress')}
                                </Button>
                            )}
                            {registration.status !== 'cancelled' && registration.status !== 'completed' && (
                                <Button size="sm" color="failure" onClick={() => postStatus(urls.cancel!)}>
                                    {t('global.cancel')}
                                </Button>
                            )}
                        </div>
                    )}
                </Card>

                <div className="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div className="flex min-w-max gap-1 border-b border-gray-200 p-2 dark:border-gray-700">
                        {tabs.map((tab) => (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => setActiveTab(tab.key)}
                                className={`flex items-center gap-2 rounded-lg px-4 py-2 text-sm transition ${
                                    activeTab === tab.key
                                        ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50'
                                }`}
                            >
                                <i className={`bx ${tab.icon}`} />
                                {tab.label}
                                {tab.count != null && tab.count > 0 && (
                                    <Badge color="info" size="xs" className="font-normal">
                                        {tab.count}
                                    </Badge>
                                )}
                            </button>
                        ))}
                    </div>

                    <div className="p-4">
                        {activeTab === 'examinations' && registration.appointment_id && (
                            <LabTestSection appointmentId={registration.appointment_id} embedded />
                        )}

                        {activeTab === 'treatments' && (
                            <div className="space-y-4">
                                {permissions.manageTreatments && (
                                    <div className="flex justify-end">
                                        <Button size="sm" color="blue" onClick={() => setTreatmentOpen(true)}>
                                            <i className="bx bx-plus me-2" />
                                            {t('global.add_treatment')}
                                        </Button>
                                    </div>
                                )}
                                {registration.treatments.length > 0 ? (
                                    <Table>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.date')}</TableHeader>
                                                <TableHeader>{t('global.treatment_type')}</TableHeader>
                                                <TableHeader>{t('global.tooth_number')}</TableHeader>
                                                <TableHeader>{t('global.description')}</TableHeader>
                                                <TableHeader>{t('global.status')}</TableHeader>
                                                <TableHeader>{t('global.cost')}</TableHeader>
                                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {registration.treatments.map((item) => (
                                                <TableRow key={item.id}>
                                                    <TableCell muted dir="ltr">{item.treatment_date ?? '—'}</TableCell>
                                                    <TableCell>{item.treatment_type}</TableCell>
                                                    <TableCell>
                                                        {item.tooth_number ? `FDI ${item.tooth_number}` : '—'}
                                                    </TableCell>
                                                    <TableCell>{item.treatment_description}</TableCell>
                                                    <TableCell>
                                                        <DentistRegistrationStatusBadge status={item.status} />
                                                    </TableCell>
                                                    <TableCell>{item.cost ?? '—'}</TableCell>
                                                    <TableCell align="center">
                                                        {permissions.manageTreatments && (
                                                            <Button
                                                                size="xs"
                                                                color="failure"
                                                                onClick={() =>
                                                                    destroyItem(
                                                                        `${urls.show}/treatments/${item.id}`,
                                                                    )
                                                                }
                                                            >
                                                                <i className="bx bx-trash" />
                                                            </Button>
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                ) : (
                                    <p className="text-center text-sm text-gray-500">{t('global.no_treatments_found')}</p>
                                )}
                            </div>
                        )}

                        {activeTab === 'xrays' && (
                            <div className="space-y-4">
                                {permissions.manageXrays && (
                                    <div className="flex justify-end">
                                        <Button size="sm" color="blue" onClick={() => setXrayOpen(true)}>
                                            <i className="bx bx-plus me-2" />
                                            {t('global.add_xray')}
                                        </Button>
                                    </div>
                                )}
                                {registration.xrays.length > 0 ? (
                                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        {registration.xrays.map((item) => (
                                            <Card key={item.id} className="shadow-sm">
                                                <h3 className="text-gray-900 dark:text-white">{item.xray_type}</h3>
                                                <p className="text-sm text-gray-500" dir="ltr">
                                                    {item.xray_date ?? '—'}
                                                </p>
                                                {item.file_url && (
                                                    <Button
                                                        as="a"
                                                        href={item.file_url}
                                                        target="_blank"
                                                        size="xs"
                                                        color="blue"
                                                        className="mt-2"
                                                    >
                                                        <i className="bx bx-image me-1" />
                                                        {t('global.view_image')}
                                                    </Button>
                                                )}
                                                {item.description && (
                                                    <p className="mt-2 text-sm">{item.description}</p>
                                                )}
                                                {permissions.manageXrays && (
                                                    <Button
                                                        size="xs"
                                                        color="failure"
                                                        className="mt-3"
                                                        onClick={() =>
                                                            destroyItem(`${urls.show}/xrays/${item.id}`)
                                                        }
                                                    >
                                                        <i className="bx bx-trash me-1" />
                                                        {t('global.delete')}
                                                    </Button>
                                                )}
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-center text-sm text-gray-500">{t('global.no_xrays_found')}</p>
                                )}
                            </div>
                        )}

                        {activeTab === 'notes' && (
                            <div className="space-y-4">
                                {permissions.manageNotes && (
                                    <div className="flex justify-end">
                                        <Button size="sm" color="blue" onClick={() => setNoteOpen(true)}>
                                            <i className="bx bx-plus me-2" />
                                            {t('global.add_note')}
                                        </Button>
                                    </div>
                                )}
                                {registration.dental_notes.length > 0 ? (
                                    <div className="space-y-3">
                                        {registration.dental_notes.map((item) => (
                                            <Card key={item.id} className="shadow-sm">
                                                <div className="flex items-start justify-between gap-3">
                                                    <div>
                                                        <div className="mb-2 flex flex-wrap items-center gap-2">
                                                            <span className="text-gray-900 dark:text-white" dir="ltr">
                                                                {item.note_date ?? '—'}
                                                            </span>
                                                            <Badge color="gray" className="font-normal">
                                                                {item.note_type}
                                                            </Badge>
                                                        </div>
                                                        <p className="text-sm">{item.content}</p>
                                                    </div>
                                                    {permissions.manageNotes && (
                                                        <Button
                                                            size="xs"
                                                            color="failure"
                                                            onClick={() =>
                                                                destroyItem(`${urls.show}/notes/${item.id}`)
                                                            }
                                                        >
                                                            <i className="bx bx-trash" />
                                                        </Button>
                                                    )}
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-center text-sm text-gray-500">{t('global.no_notes_found')}</p>
                                )}
                            </div>
                        )}

                        {activeTab === 'prescription' && registration.appointment_id && (
                            <PrescriptionSection appointmentId={registration.appointment_id} embedded />
                        )}

                        {activeTab === 'dental_chart' && (
                            <div className="space-y-4">
                                <div className="flex flex-wrap gap-2">
                                    {urls.chartIndex && (
                                        <Button as={Link} href={urls.chartIndex} color="light" size="sm">
                                            <i className="bx bx-list-ul me-2" />
                                            {t('global.dental_chart')}
                                        </Button>
                                    )}
                                    {urls.chartHistory && (
                                        <Button as={Link} href={urls.chartHistory} color="light" size="sm">
                                            <i className="bx bx-history me-2" />
                                            {t('global.history')}
                                        </Button>
                                    )}
                                    {urls.chartCompare && (
                                        <Button as={Link} href={urls.chartCompare} color="light" size="sm">
                                            <i className="bx bx-git-compare me-2" />
                                            {t('global.compare_dates')}
                                        </Button>
                                    )}
                                    {urls.chartPrint && (
                                        <Button as="a" href={urls.chartPrint} target="_blank" color="light" size="sm">
                                            <i className="bx bx-printer me-2" />
                                            {t('global.print')}
                                        </Button>
                                    )}
                                    {urls.chartExport && (
                                        <Button as="a" href={urls.chartExport} color="light" size="sm">
                                            <i className="bx bx-download me-2" />
                                            {t('global.export_pdf')}
                                        </Button>
                                    )}
                                    {urls.chartCreate && (
                                        <Button as={Link} href={urls.chartCreate} color="blue" size="sm">
                                            <i className="bx bx-plus me-2" />
                                            {t('global.add_tooth_record')}
                                        </Button>
                                    )}
                                </div>

                                {registration.chart_entries.length > 0 ? (
                                    <Table>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.tooth_number')}</TableHeader>
                                                <TableHeader>{t('global.condition')}</TableHeader>
                                                <TableHeader>{t('global.gum_health')}</TableHeader>
                                                <TableHeader>{t('global.chart_date')}</TableHeader>
                                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {registration.chart_entries.map((item) => (
                                                <TableRow key={item.id}>
                                                    <TableCell>FDI {item.tooth_number}</TableCell>
                                                    <TableCell>{item.tooth_condition ?? '—'}</TableCell>
                                                    <TableCell>{item.gum_health ?? '—'}</TableCell>
                                                    <TableCell muted dir="ltr">
                                                        {item.chart_date ?? '—'}
                                                    </TableCell>
                                                    <TableCell align="center">
                                                        <Button
                                                            as={Link}
                                                            href={item.edit_url}
                                                            size="xs"
                                                            color="warning"
                                                        >
                                                            <i className="bx bx-edit" />
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                ) : (
                                    <p className="text-center text-sm text-gray-500">{t('global.no_charts_found')}</p>
                                )}

                                <p className="text-center text-xs text-gray-500">
                                    {t('global.visual_tooth_chart')}
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <Modal show={editOpen} onClose={() => setEditOpen(false)} size="lg">
                <ModalHeader>{t('global.edit')}</ModalHeader>
                <form onSubmit={handleUpdate}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label>{t('global.dentist')}</Label>
                            <SearchableSelect
                                value={editForm.dentist_id}
                                onChange={(value) => setEditForm((current) => ({ ...current, dentist_id: value }))}
                                options={dentistOptions}
                                placeholder={t('global.please_select_dentist')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.registration_date')} *</Label>
                            <PersianDateInput
                                value={editForm.registration_date}
                                onChange={(registration_date) =>
                                    setEditForm((current) => ({ ...current, registration_date }))
                                }
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')} *</Label>
                            <select
                                className={selectClassName}
                                value={editForm.status}
                                onChange={(event) =>
                                    setEditForm((current) => ({
                                        ...current,
                                        status: event.target.value as typeof editForm.status,
                                    }))
                                }
                                required
                            >
                                <option value="pending">{t('global.pending')}</option>
                                <option value="in_progress">{t('global.in_progress')}</option>
                                <option value="completed">{t('global.completed')}</option>
                                <option value="cancelled">{t('global.cancelled')}</option>
                            </select>
                        </div>
                        <div>
                            <Label>{t('global.notes')}</Label>
                            <Textarea
                                rows={3}
                                value={editForm.notes}
                                onChange={(event) =>
                                    setEditForm((current) => ({ ...current, notes: event.target.value }))
                                }
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setEditOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={processing}>
                            {processing ? <Spinner size="sm" /> : t('global.update')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={treatmentOpen} onClose={() => setTreatmentOpen(false)} size="3xl">
                <ModalHeader>{t('global.add_treatment')}</ModalHeader>
                <form onSubmit={handleTreatment}>
                    <ModalBody className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>{t('global.treatment_type')} *</Label>
                            <TextInput
                                value={treatmentForm.treatment_type}
                                onChange={(event) =>
                                    setTreatmentForm((current) => ({
                                        ...current,
                                        treatment_type: event.target.value,
                                    }))
                                }
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.treatment_date')} *</Label>
                            <PersianDateInput
                                value={treatmentForm.treatment_date}
                                onChange={(treatment_date) =>
                                    setTreatmentForm((current) => ({ ...current, treatment_date }))
                                }
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.tooth_number')} (FDI)</Label>
                            <select
                                className={selectClassName}
                                value={treatmentForm.tooth_number}
                                onChange={(event) =>
                                    setTreatmentForm((current) => ({
                                        ...current,
                                        tooth_number: event.target.value,
                                    }))
                                }
                            >
                                <option value="">{t('global.none')}</option>
                                {TOOTH_OPTIONS.map((tooth) => (
                                    <option key={tooth} value={tooth}>
                                        FDI {tooth}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <Label>{t('global.status')} *</Label>
                            <select
                                className={selectClassName}
                                value={treatmentForm.status}
                                onChange={(event) =>
                                    setTreatmentForm((current) => ({ ...current, status: event.target.value }))
                                }
                                required
                            >
                                <option value="planned">{t('global.planned')}</option>
                                <option value="in_progress">{t('global.in_progress')}</option>
                                <option value="completed">{t('global.completed')}</option>
                                <option value="cancelled">{t('global.cancelled')}</option>
                            </select>
                        </div>
                        <div>
                            <Label>{t('global.cost')}</Label>
                            <TextInput
                                type="number"
                                step="0.01"
                                min={0}
                                value={treatmentForm.cost}
                                onChange={(event) =>
                                    setTreatmentForm((current) => ({ ...current, cost: event.target.value }))
                                }
                            />
                        </div>
                        <div className="md:col-span-2">
                            <Label>{t('global.description')} *</Label>
                            <Textarea
                                rows={3}
                                value={treatmentForm.treatment_description}
                                onChange={(event) =>
                                    setTreatmentForm((current) => ({
                                        ...current,
                                        treatment_description: event.target.value,
                                    }))
                                }
                                required
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setTreatmentOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={processing}>
                            {processing ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={xrayOpen} onClose={() => setXrayOpen(false)} size="lg">
                <ModalHeader>{t('global.add_xray')}</ModalHeader>
                <form onSubmit={handleXray}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label>{t('global.xray_type')} *</Label>
                            <TextInput
                                value={xrayForm.xray_type}
                                onChange={(event) =>
                                    setXrayForm((current) => ({ ...current, xray_type: event.target.value }))
                                }
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.xray_date')} *</Label>
                            <PersianDateInput
                                value={xrayForm.xray_date}
                                onChange={(xray_date) => setXrayForm((current) => ({ ...current, xray_date }))}
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.file')}</Label>
                            <input
                                type="file"
                                accept="image/*,.pdf"
                                className={selectClassName}
                                onChange={(event) =>
                                    setXrayForm((current) => ({
                                        ...current,
                                        file: event.target.files?.[0] ?? null,
                                    }))
                                }
                            />
                        </div>
                        <div>
                            <Label>{t('global.description')}</Label>
                            <Textarea
                                rows={3}
                                value={xrayForm.description}
                                onChange={(event) =>
                                    setXrayForm((current) => ({ ...current, description: event.target.value }))
                                }
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setXrayOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={processing}>
                            {processing ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={noteOpen} onClose={() => setNoteOpen(false)} size="lg">
                <ModalHeader>{t('global.add_note')}</ModalHeader>
                <form onSubmit={handleNote}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label>{t('global.note_date')} *</Label>
                            <PersianDateInput
                                value={noteForm.note_date}
                                onChange={(note_date) => setNoteForm((current) => ({ ...current, note_date }))}
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.note_type')} *</Label>
                            <TextInput
                                value={noteForm.note_type}
                                onChange={(event) =>
                                    setNoteForm((current) => ({ ...current, note_type: event.target.value }))
                                }
                                required
                            />
                        </div>
                        <div>
                            <Label>{t('global.content')} *</Label>
                            <Textarea
                                rows={4}
                                value={noteForm.content}
                                onChange={(event) =>
                                    setNoteForm((current) => ({ ...current, content: event.target.value }))
                                }
                                required
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setNoteOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={processing}>
                            {processing ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </DashboardLayout>
    );
}
