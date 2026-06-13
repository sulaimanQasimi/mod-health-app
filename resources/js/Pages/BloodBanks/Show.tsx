import { Head, Link, router } from '@inertiajs/react';
import { Alert, Badge, Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, ReactNode, useState } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import BloodRequestWorkflow from '../../Components/BloodBanks/BloodRequestWorkflow';
import BloodUnitDetailTile from '../../Components/BloodBanks/BloodUnitDetailTile';
import {
    BLOOD_BANK_PANEL_ICON_CLASS,
    BLOOD_REQUEST_STAT_GRADIENTS,
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

const ACTION_BTN_BASE =
    'inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-60';

const ACTION_BTN_VARIANTS = {
    success: `${ACTION_BTN_BASE} bg-gradient-to-b from-emerald-500 to-emerald-600 text-white hover:from-emerald-600 hover:to-emerald-700 focus:ring-emerald-400/40`,
    danger: `${ACTION_BTN_BASE} bg-gradient-to-b from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700 focus:ring-red-400/40`,
} as const;

function ActionButton({
    variant,
    disabled,
    onClick,
    icon,
    children,
}: {
    variant: keyof typeof ACTION_BTN_VARIANTS;
    disabled?: boolean;
    onClick?: () => void;
    icon: string;
    children: ReactNode;
}) {
    return (
        <button type="button" className={ACTION_BTN_VARIANTS[variant]} disabled={disabled} onClick={onClick}>
            {disabled ? <Spinner size="sm" /> : <i className={`bx ${icon}`} />}
            {children}
        </button>
    );
}

function StatCard({
    label,
    value,
    gradient,
    icon,
}: {
    label: string;
    value: string | number;
    gradient: string;
    icon: string;
}) {
    return (
        <div className={`overflow-hidden rounded-2xl bg-gradient-to-br ${gradient} p-4 text-white shadow-sm`}>
            <div className="flex items-start justify-between gap-2">
                <p className="text-xs font-semibold uppercase tracking-wide text-white/85">{label}</p>
                <i className={`bx ${icon} text-2xl text-white/35`} />
            </div>
            <p className="mt-2 text-3xl font-bold">{value}</p>
        </div>
    );
}

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
                    <StatCard
                        label={t('global.requested_quantity')}
                        value={formatOrderQuantity(bloodRequest)}
                        gradient={BLOOD_REQUEST_STAT_GRADIENTS.requested}
                        icon="bx-droplet"
                    />
                    <StatCard
                        label={t('global.crossmatch_reserved_compatible_summary')}
                        value={bloodRequest.reserved_compatible_qty}
                        gradient={BLOOD_REQUEST_STAT_GRADIENTS.reserved}
                        icon="bx-test-tube"
                    />
                    <StatCard
                        label={t('global.issued_blood_units')}
                        value={bloodRequest.issued_qty}
                        gradient={BLOOD_REQUEST_STAT_GRADIENTS.issued}
                        icon="bx-package"
                    />
                    <StatCard
                        label={t('global.remaining_quantity')}
                        value={bloodRequest.remaining_qty}
                        gradient={BLOOD_REQUEST_STAT_GRADIENTS.remaining}
                        icon="bx-time-five"
                    />
                </div>

                <div className={`grid gap-5 ${hasPendingActions && !isApproved ? 'xl:grid-cols-3' : ''}`}>
                    <div className={`space-y-5 ${hasPendingActions && !isApproved ? 'xl:col-span-2' : ''}`}>
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

                    {hasPendingActions && !isApproved && (
                        <div className="space-y-5 xl:sticky xl:top-4 xl:self-start">
                            <IcuPanel
                                variant="table"
                                contentClassName={PANEL_BODY_CLASS}
                                title={t('global.actions')}
                                icon="bx-cog"
                                iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                            >
                                <div className="space-y-3">
                                    {permissions.approve && (
                                        <ActionButton variant="success" icon="bx-check" disabled={processing} onClick={handleApprove}>
                                            {t('global.approve')}
                                        </ActionButton>
                                    )}
                                    {permissions.reject && (
                                        <ActionButton variant="danger" icon="bx-x" disabled={processing} onClick={() => setRejectOpen(true)}>
                                            {t('global.reject')}
                                        </ActionButton>
                                    )}
                                </div>
                            </IcuPanel>
                        </div>
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
