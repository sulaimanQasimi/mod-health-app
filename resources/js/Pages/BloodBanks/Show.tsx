import { Head, Link, router } from '@inertiajs/react';
import { Alert, Badge, Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import BloodRequestWorkflow from '../../Components/BloodBanks/BloodRequestWorkflow';
import BloodUnitDetailTile from '../../Components/BloodBanks/BloodUnitDetailTile';
import {
    BLOOD_BANK_PANEL_ICON_CLASS,
    BLOOD_BANK_PRIMARY_BTN_CLASS,
    bloodGroupLabel,
    bloodRhLabel,
    bloodStatusBadgeColor,
} from '../../Components/BloodBanks/bloodBankUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import StatCard from '../../Components/ui/StatCard';
import { useTranslation } from '../../hooks/useTranslation';
import {
    BloodRequestDetail,
    BloodRequestListVariant,
    BloodRequestShowPermissions,
    BloodRequestShowUrls,
    BloodRequestWorkflowData,
} from '../../types/bloodBank';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowProps {
    bloodRequest: BloodRequestDetail;
    workflowData: BloodRequestWorkflowData;
    receiverDepartments: { id: number; name: string }[];
    permissions: BloodRequestShowPermissions;
    flash?: {
        success?: string | null;
        error?: string | null;
    };
    urls: BloodRequestShowUrls;
}

const PANEL_BODY_CLASS = 'p-5';

function formatOrderQuantity(bloodRequest: BloodRequestDetail): string {
    const display = bloodRequest.order_quantity_display;
    if (display.mode === 'volume_ml' && display.ml != null) {
        return `${display.ml} ml`;
    }
    if (display.mode === 'units' && display.units != null) {
        return String(display.units);
    }
    return String(bloodRequest.requested_qty);
}

function navTabForStatus(status: string): BloodRequestListVariant {
    if (status === 'approved' || status === 'delivered') {
        return status === 'delivered' ? 'delivered' : 'approved';
    }
    if (status === 'rejected') {
        return 'rejected';
    }
    return 'new';
}

export default function BloodBanksShow({
    bloodRequest,
    workflowData,
    receiverDepartments,
    permissions,
    flash,
    urls,
}: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [rejectOpen, setRejectOpen] = useState(false);
    const [rejectReason, setRejectReason] = useState('');

    const hasPendingActions = permissions.approve || permissions.reject;
    const isApproved = bloodRequest.status === 'approved';
    const isDelivered = bloodRequest.status === 'delivered';
    const navTab = navTabForStatus(bloodRequest.status);

    const post = (url: string, onSuccess?: () => void) => {
        setProcessing(true);
        router.post(url, {}, {
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

    const pendingActionsFooter =
        hasPendingActions && !isApproved ? (
            <div className="flex flex-col gap-3 sm:flex-row">
                {permissions.approve && (
                    <Button
                        type="button"
                        className={`${BLOOD_BANK_PRIMARY_BTN_CLASS} sm:flex-1`}
                        disabled={processing}
                        onClick={handleApprove}
                    >
                        {processing ? <Spinner size="sm" /> : <i className="bx bx-check" />}
                        {t('global.approve')}
                    </Button>
                )}
                {permissions.reject && (
                    <Button
                        type="button"
                        color="failure"
                        className="sm:flex-1"
                        disabled={processing}
                        onClick={() => setRejectOpen(true)}
                    >
                        <i className="bx bx-x" />
                        {t('global.reject')}
                    </Button>
                )}
            </div>
        ) : undefined;

    const statCards = [
        {
            title: t('global.requested_quantity'),
            value: formatOrderQuantity(bloodRequest),
            subtitle: bloodRequest.uses_volume_ml_tracking
                ? `${bloodRequest.ordered_volume_ml} ml`
                : t('global.blood_request_details'),
            iconClass: 'bx bx-droplet',
            iconBgClass: 'bg-rose-600',
            borderClass: 'border-rose-200 dark:border-rose-800',
            valueClass: 'text-rose-700 dark:text-rose-300',
        },
        {
            title: t('global.crossmatch_reserved_compatible_summary'),
            value: bloodRequest.uses_volume_ml_tracking
                ? `${bloodRequest.reserved_compatible_volume_ml} ml`
                : bloodRequest.reserved_compatible_qty,
            subtitle: t('global.crossmatch_workflow'),
            iconClass: 'bx bx-test-tube',
            iconBgClass: 'bg-sky-600',
            borderClass: 'border-sky-200 dark:border-sky-800',
            valueClass: 'text-sky-700 dark:text-sky-300',
        },
        {
            title: t('global.issued_blood_units'),
            value: bloodRequest.uses_volume_ml_tracking
                ? `${bloodRequest.issued_volume_ml} ml`
                : bloodRequest.issued_qty,
            subtitle: t('global.delivered'),
            iconClass: 'bx bx-package',
            iconBgClass: 'bg-emerald-600',
            borderClass: 'border-emerald-200 dark:border-emerald-800',
            valueClass: 'text-emerald-700 dark:text-emerald-300',
        },
        {
            title: t('global.remaining_quantity'),
            value: bloodRequest.uses_volume_ml_tracking
                ? `${bloodRequest.remaining_volume_ml} ml`
                : bloodRequest.remaining_qty,
            subtitle: bloodRequest.uses_volume_ml_tracking ? t('global.remaining_volume_ml_summary') : t('global.quantity'),
            iconClass: 'bx bx-time-five',
            iconBgClass: 'bg-amber-500',
            borderClass: 'border-amber-200 dark:border-amber-800',
            valueClass: 'text-amber-700 dark:text-amber-300',
        },
    ];

    return (
        <DashboardLayout>
            <Head title={`${t('global.blood_request_details')} — #${bloodRequest.id}`} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.blood_request_details')}
                    subtitle={`#${bloodRequest.id} · ${bloodRequest.patient.name ?? '—'}`}
                    icon="bx-donate-blood"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                    action={
                        <SettingsPageActions>
                            <Link
                                href={urls.inventory}
                                className="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-medium text-rose-700 shadow-sm transition hover:bg-rose-50 dark:border-rose-900/40 dark:bg-gray-900 dark:text-rose-300 dark:hover:bg-rose-950/30"
                            >
                                <i className="bx bx-box" />
                                {t('global.open_full_inventory')}
                            </Link>
                        </SettingsPageActions>
                    }
                />

                <BloodBankNavTabs active={navTab} urls={urls} />

                {flash?.success && (
                    <Alert color="success" className="rounded-xl">
                        <span className="text-sm font-medium">{flash.success}</span>
                    </Alert>
                )}
                {flash?.error && (
                    <Alert color="failure" className="rounded-xl">
                        <span className="text-sm font-medium">{flash.error}</span>
                    </Alert>
                )}

                {bloodRequest.reject_reason && (
                    <Alert color="failure" className="rounded-xl">
                        <div className="flex items-start gap-2">
                            <i className="bx bx-x-circle mt-0.5 text-lg" />
                            <div>
                                <p className="font-semibold">{t('global.reject_reason')}</p>
                                <p className="mt-0.5 text-sm opacity-90">{bloodRequest.reject_reason}</p>
                            </div>
                        </div>
                    </Alert>
                )}

                {bloodRequest.quantity_inferred_from_volume_ml && (
                    <Alert color="warning" className="rounded-xl">
                        <div className="flex items-start gap-2">
                            <i className="bx bx-info-circle mt-0.5 text-lg" />
                            <span className="text-sm font-medium">
                                {t('global.quantity_inferred_from_volume_hint').replace(
                                    ':raw',
                                    String(bloodRequest.quantity ?? ''),
                                )}
                            </span>
                        </div>
                    </Alert>
                )}

                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {statCards.map((card) => (
                        <StatCard key={card.title} {...card} />
                    ))}
                </div>

                <div className="space-y-5">
                    {isApproved ? (
                        <BloodRequestWorkflow
                            bloodRequest={bloodRequest}
                            workflowData={workflowData}
                            permissions={permissions}
                            urls={urls}
                            receiverDepartments={receiverDepartments}
                        />
                    ) : (
                        <IcuPanel
                            variant="table"
                            contentClassName={PANEL_BODY_CLASS}
                            title={t('global.blood_request_details')}
                            icon="bx-info-circle"
                            iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                            action={
                                <Badge color={bloodStatusBadgeColor(bloodRequest.status)} className="font-normal capitalize">
                                    {bloodRequest.status}
                                </Badge>
                            }
                            footer={pendingActionsFooter}
                        >
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <BloodUnitDetailTile icon="bx-user" label={t('global.patient_name')} value={bloodRequest.patient.name ?? '—'} />
                                <BloodUnitDetailTile icon="bx-id-card" label={t('global.card_number')} value={bloodRequest.patient.id_card ?? '—'} />
                                <BloodUnitDetailTile icon="bx-phone" label={t('global.phone')} value={bloodRequest.patient.phone ?? '—'} />
                                <BloodUnitDetailTile icon="bx-buildings" label={t('global.requested_department')} value={bloodRequest.department_name ?? '—'} />
                                <BloodUnitDetailTile icon="bx-droplet" label={t('global.blood_group')} value={bloodGroupLabel(bloodRequest.group)} />
                                <BloodUnitDetailTile icon="bx-plus-medical" label={t('global.rh')} value={bloodRhLabel(bloodRequest.rh)} />
                                <BloodUnitDetailTile icon="bx-cylinder" label={t('global.blood_type')} value={bloodRequest.type ?? '—'} />
                                <BloodUnitDetailTile icon="bx-hash" label={t('global.quantity')} value={formatOrderQuantity(bloodRequest)} />
                                <BloodUnitDetailTile icon="bx-test-tube" label={t('global.hemoglobin')} value={bloodRequest.hemoglobin != null ? String(bloodRequest.hemoglobin) : '—'} />
                                <BloodUnitDetailTile icon="bx-test-tube" label={t('global.hematocrit')} value={bloodRequest.hematocrit != null ? String(bloodRequest.hematocrit) : '—'} />
                                <BloodUnitDetailTile icon="bx-injection" label={t('global.clotting_factor')} value={bloodRequest.factor ?? '—'} />
                                <BloodUnitDetailTile icon="bx-calendar" label={t('global.date')}>
                                    <span dir="ltr">{bloodRequest.created_at ?? '—'}</span>
                                </BloodUnitDetailTile>
                                {bloodRequest.created_by_name && (
                                    <BloodUnitDetailTile icon="bx-user-check" label={t('global.created_by')} value={bloodRequest.created_by_name} />
                                )}
                            </div>
                        </IcuPanel>
                    )}

                    {isDelivered && (bloodRequest.receiver_department_name || bloodRequest.receiver_nurse_name) && (
                            <IcuPanel
                                variant="table"
                                contentClassName={PANEL_BODY_CLASS}
                                title={t('global.blood_bank_receiver_summary')}
                                icon="bx-user-pin"
                                iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                            >
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <BloodUnitDetailTile
                                        icon="bx-buildings"
                                        label={t('global.blood_bank_receiver_department')}
                                        value={bloodRequest.receiver_department_name ?? '—'}
                                    />
                                    <BloodUnitDetailTile
                                        icon="bx-user-pin"
                                        label={t('global.blood_bank_receiver_nurse')}
                                        value={bloodRequest.receiver_nurse_name ?? '—'}
                                    />
                                </div>
                            </IcuPanel>
                        )}

                        {isDelivered && bloodRequest.issued_units.length > 0 && (
                            <IcuPanel
                                variant="table"
                                contentClassName={PANEL_BODY_CLASS}
                                title={t('global.issued_blood_units')}
                                icon="bx-package"
                                iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                            >
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>{t('global.bag_number')}</TableHeader>
                                            <TableHeader>{t('global.expires_at')}</TableHeader>
                                            <TableHeader>{t('global.date')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {bloodRequest.issued_units.map((unit) => (
                                            <TableRow key={unit.id}>
                                                <TableCell>
                                                    {unit.urls?.show ? (
                                                        <Link href={unit.urls.show} className="font-medium text-rose-700 hover:underline dark:text-rose-300">
                                                            {unit.bag_number ?? '—'}
                                                        </Link>
                                                    ) : (
                                                        (unit.bag_number ?? '—')
                                                    )}
                                                </TableCell>
                                                <TableCell muted dir="ltr">
                                                    {unit.expires_at ?? '—'}
                                                </TableCell>
                                                <TableCell muted dir="ltr">
                                                    {unit.issued_at ?? '—'}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </IcuPanel>
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
                            className="rounded-xl"
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
        </DashboardLayout>
    );
}
