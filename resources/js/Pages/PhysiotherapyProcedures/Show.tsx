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
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import PhysiotherapyProcedureProgressBar from '../../Components/PhysiotherapyProcedures/PhysiotherapyProcedureProgressBar';
import PhysiotherapyProcedureReviews from '../../Components/PhysiotherapyProcedures/PhysiotherapyProcedureReviews';
import PhysiotherapyProcedureStatusBadge from '../../Components/PhysiotherapyProcedures/PhysiotherapyProcedureStatusBadge';
import UpdateProgressModal from '../../Components/PhysiotherapyProcedures/UpdateProgressModal';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PhysiotherapyProcedureDetail,
    PhysiotherapyProcedureFormOptions,
    PhysiotherapyProcedureShowPermissions,
    PhysiotherapyProcedureStatus,
} from '../../types/physiotherapyProcedure';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowPhysiotherapyProcedureProps {
    procedure: PhysiotherapyProcedureDetail;
    formOptions: PhysiotherapyProcedureFormOptions | null;
    permissions: PhysiotherapyProcedureShowPermissions;
    urls: {
        index: string;
        myProcedures: string;
        show: string;
        update: string;
        destroy: string;
        updateCounter: string;
        reviews: string;
        appointment: string | null;
    };
}

export default function ShowPhysiotherapyProcedure({
    procedure,
    formOptions,
    permissions,
    urls,
}: ShowPhysiotherapyProcedureProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [editOpen, setEditOpen] = useState(false);
    const [progressOpen, setProgressOpen] = useState(false);
    const [form, setForm] = useState({
        physiotherapy_type_id: String(procedure.physiotherapy_type_id),
        doctor_id: String(procedure.doctor_id),
        type: procedure.type ?? '',
        duration: String(procedure.duration ?? ''),
        days_count: String(procedure.days_count ?? ''),
        description: procedure.description ?? '',
        notes: procedure.notes ?? '',
        status: procedure.status,
        start_date: procedure.start_date ?? '',
        end_date: procedure.end_date ?? '',
    });

    const canUpdateProgress =
        permissions.updateProgress && procedure.status !== 'completed' && procedure.status !== 'cancelled';

    const runAction = (callback: () => void) => {
        setProcessing(true);
        callback();
    };

    const handleUpdate = (event: FormEvent) => {
        event.preventDefault();
        runAction(() =>
            router.put(
                urls.update,
                {
                    appointment_id: procedure.appointment_id,
                    physiotherapy_type_id: Number(form.physiotherapy_type_id),
                    doctor_id: Number(form.doctor_id),
                    type: form.type,
                    duration: Number(form.duration),
                    days_count: Number(form.days_count),
                    description: form.description || null,
                    notes: form.notes || null,
                    status: form.status,
                    start_date: form.start_date,
                    end_date: form.end_date || null,
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
        if (!window.confirm(t('global.confirm_delete'))) return;
        runAction(() =>
            router.delete(urls.destroy, {
                onFinish: () => setProcessing(false),
            }),
        );
    };

    const handleProgress = (counter: number) => {
        runAction(() =>
            router.post(
                urls.updateCounter,
                { counter },
                {
                    preserveScroll: true,
                    onSuccess: () => setProgressOpen(false),
                    onFinish: () => setProcessing(false),
                },
            ),
        );
    };

    const reviewUrl = (reviewId?: number) =>
        reviewId ? `${urls.reviews}/${reviewId}` : urls.reviews;

    const typeOptions =
        formOptions?.physiotherapy_types.map((item) => ({ value: String(item.id), label: item.name })) ?? [];
    const physiotherapistOptions =
        formOptions?.physiotherapists.map((item) => ({ value: String(item.id), label: item.name })) ?? [];

    const infoCards = [
        [t('global.patient_name'), procedure.patient_name],
        [t('global.card_number'), procedure.patient_id_card],
        [t('global.physiotherapy_type'), procedure.physiotherapy_type],
        [t('global.physiotherapist'), procedure.physiotherapist],
        [t('global.type'), procedure.type],
        [t('global.duration'), procedure.duration != null ? `${procedure.duration} ${t('global.minutes')}` : '—'],
        [t('global.total_sessions'), procedure.days_count],
        [t('global.start_date'), procedure.start_date],
        [t('global.end_date'), procedure.end_date],
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.procedure_details')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.procedure_details')}
                    subtitle={`#${procedure.id}`}
                    icon="bx-health"
                    accent="from-cyan-500 to-teal-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            {urls.appointment && (
                                <Button as={Link} href={urls.appointment} color="light" size="sm">
                                    <i className="bx bx-calendar me-2" />
                                    {t('global.appointment')}
                                </Button>
                            )}
                            {canUpdateProgress && (
                                <Button color="warning" size="sm" onClick={() => setProgressOpen(true)}>
                                    <i className="bx bx-edit me-2" />
                                    {t('global.update_progress')}
                                </Button>
                            )}
                            {permissions.edit && formOptions && (
                                <Button color="light" size="sm" onClick={() => setEditOpen(true)}>
                                    <i className="bx bx-edit-alt me-2" />
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

                <Card className="overflow-hidden shadow-sm">
                    <div className="bg-gradient-to-r from-cyan-500/10 via-teal-500/10 to-emerald-500/10 px-6 py-5 dark:from-cyan-900/20 dark:via-teal-900/20 dark:to-emerald-900/20">
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {procedure.patient_name ?? '—'}
                                </h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    {procedure.physiotherapy_type} · {procedure.physiotherapist}
                                </p>
                            </div>
                            <PhysiotherapyProcedureStatusBadge status={procedure.status} />
                        </div>
                    </div>

                    <div className="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
                        {infoCards.map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/60 dark:bg-gray-800/40"
                            >
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</p>
                                <p className="mt-2 font-semibold text-gray-900 dark:text-white">{value ?? '—'}</p>
                            </div>
                        ))}
                    </div>

                    <div className="border-t border-gray-100 px-6 py-5 dark:border-gray-700">
                        <div className="mb-2 flex items-center justify-between">
                            <h3 className="font-medium text-gray-900 dark:text-white">{t('global.progress')}</h3>
                            <Badge color="info">
                                {procedure.counter}/{procedure.days_count}
                            </Badge>
                        </div>
                        <PhysiotherapyProcedureProgressBar
                            counter={procedure.counter}
                            total={procedure.days_count}
                            percentage={procedure.progress_percentage}
                        />
                    </div>

                    {(procedure.description || procedure.notes) && (
                        <div className="grid gap-4 border-t border-gray-100 px-6 py-5 dark:border-gray-700 lg:grid-cols-2">
                            {procedure.description && (
                                <div className="rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-sm dark:border-cyan-900/40 dark:bg-cyan-900/20">
                                    <strong>{t('global.description')}:</strong> {procedure.description}
                                </div>
                            )}
                            {procedure.notes && (
                                <div className="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-800/40">
                                    <strong>{t('global.notes')}:</strong> {procedure.notes}
                                </div>
                            )}
                        </div>
                    )}

                    <div className="flex flex-wrap gap-4 border-t border-gray-100 px-6 py-4 text-sm text-gray-500 dark:border-gray-700">
                        {procedure.created_by_name && (
                            <span>
                                {t('global.created_by')}: {procedure.created_by_name}
                            </span>
                        )}
                        {procedure.created_at && (
                            <span>
                                {t('global.created_at')}: {procedure.created_at}
                            </span>
                        )}
                    </div>
                </Card>

                <Card className="shadow-sm">
                    <PhysiotherapyProcedureReviews
                        reviews={procedure.reviews}
                        permissions={permissions}
                        processing={processing}
                        onCreate={(payload) =>
                            runAction(() =>
                                router.post(urls.reviews, payload, {
                                    preserveScroll: true,
                                    onFinish: () => setProcessing(false),
                                }),
                            )
                        }
                        onUpdate={(reviewId, payload) =>
                            runAction(() =>
                                router.put(reviewUrl(reviewId), payload, {
                                    preserveScroll: true,
                                    onFinish: () => setProcessing(false),
                                }),
                            )
                        }
                        onDelete={(reviewId) =>
                            runAction(() =>
                                router.delete(reviewUrl(reviewId), {
                                    preserveScroll: true,
                                    onFinish: () => setProcessing(false),
                                }),
                            )
                        }
                    />
                </Card>
            </div>

            <UpdateProgressModal
                open={progressOpen}
                procedure={procedure}
                submitting={processing}
                onClose={() => setProgressOpen(false)}
                onSubmit={handleProgress}
            />

            <Modal show={editOpen} onClose={() => setEditOpen(false)} size="4xl">
                <ModalHeader>{t('global.edit_physiotherapy_procedure')}</ModalHeader>
                <form onSubmit={handleUpdate}>
                    <ModalBody className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label>{t('global.physiotherapy_type')} *</Label>
                                <SearchableSelect
                                    value={form.physiotherapy_type_id}
                                    onChange={(value) => setForm((current) => ({ ...current, physiotherapy_type_id: value }))}
                                    options={typeOptions}
                                    required
                                />
                            </div>
                            <div>
                                <Label>{t('global.physiotherapist')} *</Label>
                                <SearchableSelect
                                    value={form.doctor_id}
                                    onChange={(value) => setForm((current) => ({ ...current, doctor_id: value }))}
                                    options={physiotherapistOptions}
                                    required
                                />
                            </div>
                            <div>
                                <Label>{t('global.type')} *</Label>
                                <TextInput
                                    value={form.type}
                                    onChange={(event) => setForm((current) => ({ ...current, type: event.target.value }))}
                                    required
                                />
                            </div>
                            <div>
                                <Label>{t('global.duration')} ({t('global.minutes')}) *</Label>
                                <TextInput
                                    type="number"
                                    min={1}
                                    value={form.duration}
                                    onChange={(event) => setForm((current) => ({ ...current, duration: event.target.value }))}
                                    required
                                />
                            </div>
                            <div>
                                <Label>{t('global.total_sessions')} *</Label>
                                <TextInput
                                    type="number"
                                    min={1}
                                    value={form.days_count}
                                    onChange={(event) =>
                                        setForm((current) => ({ ...current, days_count: event.target.value }))
                                    }
                                    required
                                />
                            </div>
                            <div>
                                <Label>{t('global.status')} *</Label>
                                <select
                                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    value={form.status}
                                    onChange={(event) =>
                                        setForm((current) => ({
                                            ...current,
                                            status: event.target.value as PhysiotherapyProcedureStatus,
                                        }))
                                    }
                                >
                                    <option value="pending">{t('global.status_pending')}</option>
                                    <option value="in_progress">{t('global.status_in_progress')}</option>
                                    <option value="completed">{t('global.status_completed')}</option>
                                    <option value="cancelled">{t('global.status_cancelled')}</option>
                                </select>
                            </div>
                            <div>
                                <Label>{t('global.start_date')} *</Label>
                                <PersianDateInput
                                    value={form.start_date}
                                    onChange={(start_date) => setForm((current) => ({ ...current, start_date }))}
                                    required
                                />
                            </div>
                            <div>
                                <Label>{t('global.end_date')}</Label>
                                <PersianDateInput
                                    value={form.end_date}
                                    onChange={(end_date) => setForm((current) => ({ ...current, end_date }))}
                                />
                            </div>
                            <div className="md:col-span-2">
                                <Label>{t('global.description')}</Label>
                                <Textarea
                                    rows={3}
                                    value={form.description}
                                    onChange={(event) =>
                                        setForm((current) => ({ ...current, description: event.target.value }))
                                    }
                                />
                            </div>
                            <div className="md:col-span-2">
                                <Label>{t('global.notes')}</Label>
                                <Textarea
                                    rows={2}
                                    value={form.notes}
                                    onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))}
                                />
                            </div>
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setEditOpen(false)} disabled={processing}>
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
