import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import { bloodGroupLabel, bloodRhLabel, bloodStatusBadgeColor } from '../../Components/BloodBanks/bloodBankUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
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
import { BloodBankListUrls, BloodRequestDetail } from '../../types/bloodBank';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowProps {
    bloodRequest: BloodRequestDetail;
    receiverDepartments: { id: number; name: string }[];
    permissions: {
        approve: boolean;
        reject: boolean;
        deliver: boolean;
        manageCrossmatch: boolean;
    };
    urls: BloodBankListUrls & {
        back: string;
        approve: string;
        reject: string;
        deliver: string;
        inventory: string;
        legacyInventoryShow: string;
        nursesByDepartment: string;
    };
}

export default function BloodBanksShow({
    bloodRequest,
    receiverDepartments,
    permissions,
    urls,
}: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [rejectOpen, setRejectOpen] = useState(false);
    const [deliverOpen, setDeliverOpen] = useState(false);
    const [rejectReason, setRejectReason] = useState('');
    const [receiverDepartmentId, setReceiverDepartmentId] = useState('');
    const [receiverNurseId, setReceiverNurseId] = useState('');
    const [nurseOptions, setNurseOptions] = useState<{ value: string; label: string }[]>([]);
    const [nursesLoading, setNursesLoading] = useState(false);

    const patientLabel = bloodRequest.patient.name ?? `#${bloodRequest.id}`;

    useEffect(() => {
        if (!receiverDepartmentId) {
            setNurseOptions([]);
            setReceiverNurseId('');
            return;
        }

        const nursesUrl = urls.nursesByDepartment.replace('__DEPARTMENT__', receiverDepartmentId);
        setNursesLoading(true);
        fetch(nursesUrl, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => r.json())
            .then((data) => {
                setNurseOptions(
                    (data.nurses ?? []).map((n: { id: number; name: string }) => ({
                        value: String(n.id),
                        label: n.name,
                    })),
                );
            })
            .finally(() => setNursesLoading(false));
    }, [receiverDepartmentId, urls.nursesByDepartment]);

    const post = (url: string, data: Record<string, string> = {}, onSuccess?: () => void) => {
        setProcessing(true);
        router.post(url, data, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const put = (url: string, data: Record<string, string>, onSuccess?: () => void) => {
        setProcessing(true);
        router.put(url, data, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
            onFinish: () => setProcessing(false),
        });
    };

    const handleApprove = () => {
        if (!window.confirm(t('global.approve'))) return;
        post(urls.approve);
    };

    const handleReject = (event: FormEvent) => {
        event.preventDefault();
        put(urls.reject, { reject_reason: rejectReason }, () => setRejectOpen(false));
    };

    const handleDeliver = (event: FormEvent) => {
        event.preventDefault();
        post(
            urls.deliver,
            {
                receiver_department_id: receiverDepartmentId,
                receiver_nurse_id: receiverNurseId,
            },
            () => setDeliverOpen(false),
        );
    };

    return (
        <DashboardLayout>
            <Head title={patientLabel} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.blood_request_details')}
                    subtitle={patientLabel}
                    icon="bx-donate-blood"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                    action={
                        <SettingsPageActions>
                            <Link
                                href={urls.inventory}
                                className="inline-flex items-center gap-2 rounded-xl border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50 dark:border-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-950/30"
                            >
                                <i className="bx bx-box" />
                                {t('global.open_full_inventory')}
                            </Link>
                        </SettingsPageActions>
                    }
                />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {[
                        { label: t('global.requested_quantity'), value: bloodRequest.requested_qty, color: 'rose' },
                        {
                            label: t('global.crossmatch_reserved_compatible_summary'),
                            value: bloodRequest.reserved_compatible_qty,
                            color: 'sky',
                        },
                        { label: t('global.issued_blood_units'), value: bloodRequest.issued_qty, color: 'emerald' },
                        { label: t('global.remaining_quantity'), value: bloodRequest.remaining_qty, color: 'amber' },
                    ].map((card) => (
                        <div
                            key={card.label}
                            className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900"
                        >
                            <p className="text-xs font-medium uppercase text-gray-500">{card.label}</p>
                            <p className="mt-2 text-2xl font-bold">{card.value}</p>
                        </div>
                    ))}
                </div>

                {bloodRequest.workflow.steps.length > 0 && (
                    <IcuPanel
                        variant="table"
                        title={t('global.blood_bank_workflow_title')}
                        icon="bx-git-branch"
                        iconClassName="text-rose-600"
                    >
                        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            {bloodRequest.workflow.steps.map((step) => (
                                <div
                                    key={step.number}
                                    className={`rounded-xl border p-4 text-center ${
                                        step.current
                                            ? 'border-rose-500 bg-rose-50 dark:bg-rose-950/30'
                                            : step.done
                                              ? 'border-emerald-300 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20'
                                              : 'border-gray-200 dark:border-gray-700'
                                    }`}
                                >
                                    <div className="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white text-sm font-bold shadow dark:bg-gray-900">
                                        {step.done ? <i className="bx bx-check text-emerald-600" /> : step.number}
                                    </div>
                                    <p className="text-xs font-medium">
                                        {t(`global.blood_bank_workflow_step_${step.number}`)}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </IcuPanel>
                )}

                <IcuPanel variant="table" title={t('global.blood_request_details')} icon="bx-info-circle">
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <p className="text-xs text-gray-500">{t('global.patient_name')}</p>
                            <p className="font-medium">{bloodRequest.patient.name ?? '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500">{t('global.card_number')}</p>
                            <p className="font-medium">{bloodRequest.patient.id_card ?? '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500">{t('global.requested_department')}</p>
                            <p className="font-medium">{bloodRequest.department_name ?? '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500">{t('global.blood_group')}</p>
                            <p className="font-medium">{bloodGroupLabel(bloodRequest.group)}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500">{t('global.rh')}</p>
                            <p className="font-medium">{bloodRhLabel(bloodRequest.rh)}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500">{t('global.blood_type')}</p>
                            <p className="font-medium">{bloodRequest.type ?? '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500">{t('global.status')}</p>
                            <Badge color={bloodStatusBadgeColor(bloodRequest.status)} className="mt-1 w-fit">
                                {bloodRequest.status}
                            </Badge>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500">{t('global.date')}</p>
                            <p className="font-medium" dir="ltr">
                                {bloodRequest.created_at ?? '—'}
                            </p>
                        </div>
                    </div>
                </IcuPanel>

                {bloodRequest.crossmatches.length > 0 && (
                    <IcuPanel variant="table" title={t('global.crossmatch')} icon="bx-test-tube">
                        <Table embedded>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.bag_number')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {bloodRequest.crossmatches.map((cx) => (
                                    <TableRow key={cx.id}>
                                        <TableCell>{cx.bag_number ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color="info" className="w-fit font-normal">
                                                {cx.status}
                                                {cx.is_reserved ? ` · ${t('global.reserved')}` : ''}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted dir="ltr">
                                            {cx.tested_at ?? '—'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </IcuPanel>
                )}

                {bloodRequest.issued_units.length > 0 && (
                    <IcuPanel variant="table" title={t('global.issued_blood_units')} icon="bx-package">
                        <Table embedded>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.bag_number')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {bloodRequest.issued_units.map((unit) => (
                                    <TableRow key={unit.id}>
                                        <TableCell>{unit.bag_number ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {unit.issued_at ?? '—'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </IcuPanel>
                )}

                <div className="flex flex-wrap gap-3">
                    {permissions.approve && (
                        <Button color="success" onClick={handleApprove} disabled={processing}>
                            <i className="bx bx-check me-2" />
                            {t('global.approve')}
                        </Button>
                    )}
                    {permissions.reject && (
                        <Button color="failure" onClick={() => setRejectOpen(true)} disabled={processing}>
                            <i className="bx bx-x me-2" />
                            {t('global.reject')}
                        </Button>
                    )}
                    {permissions.deliver && (
                        <Button color="purple" onClick={() => setDeliverOpen(true)} disabled={processing}>
                            <i className="bx bx-package me-2" />
                            {t('global.deliver')}
                        </Button>
                    )}
                </div>
            </div>

            <Modal show={rejectOpen} onClose={() => !processing && setRejectOpen(false)} size="md">
                <form onSubmit={handleReject}>
                    <ModalHeader>{t('global.reject_request')}</ModalHeader>
                    <ModalBody>
                        <Label htmlFor="reject-reason" className="mb-2 block">
                            {t('global.reject_reason')}
                        </Label>
                        <Textarea
                            id="reject-reason"
                            rows={3}
                            value={rejectReason}
                            onChange={(e) => setRejectReason(e.target.value)}
                        />
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={() => setRejectOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="failure" disabled={processing}>
                            {processing ? <Spinner size="sm" /> : null}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={deliverOpen} onClose={() => !processing && setDeliverOpen(false)} size="md">
                <form onSubmit={handleDeliver}>
                    <ModalHeader>{t('global.deliver')}</ModalHeader>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label className="mb-2 block">{t('global.blood_bank_receiver_department')}</Label>
                            <SearchableSelect
                                value={receiverDepartmentId}
                                onChange={setReceiverDepartmentId}
                                options={receiverDepartments.map((d) => ({
                                    value: String(d.id),
                                    label: d.name,
                                }))}
                                placeholder={t('global.select')}
                                required
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">{t('global.blood_bank_receiver_nurse')}</Label>
                            <SearchableSelect
                                value={receiverNurseId}
                                onChange={setReceiverNurseId}
                                options={nurseOptions}
                                placeholder={nursesLoading ? t('global.loading') : t('global.select')}
                                required
                                disabled={!receiverDepartmentId || nursesLoading}
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={() => setDeliverOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="purple" disabled={processing || !receiverDepartmentId || !receiverNurseId}>
                            {processing ? <Spinner size="sm" /> : null}
                            {t('global.deliver')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </DashboardLayout>
    );
}
