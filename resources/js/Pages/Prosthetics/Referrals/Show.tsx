import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Label, TextInput } from 'flowbite-react';
import { useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import IcuPanel from '../../../Components/Icus/IcuPanel';
import { prostheticReferralStatusLabel } from '../../../Components/ProstheticsReferrals/prostheticsReferralUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { ProstheticReferralDetail } from '../../../types/prosthetics';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface ShowProps {
    referral: ProstheticReferralDetail;
    urls: {
        index: string;
        edit: string;
        accept: string;
        reject: string;
        convert: string;
        caseShow: string;
    };
}

const PANEL_ICON_CLASS = 'text-indigo-600 dark:text-indigo-400';
const PANEL_BODY_CLASS = 'p-5';

function DetailTile({ label, value }: { label: string; value?: string | null }) {
    return (
        <div className="rounded-xl border border-gray-100 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-800/40">
            <p className="text-xs text-gray-500 dark:text-gray-400">{label}</p>
            <p className="mt-0.5 font-medium text-gray-900 dark:text-white">{value || '—'}</p>
        </div>
    );
}

export default function ProstheticsReferralsShow({ referral, urls }: ShowProps) {
    const { t } = useTranslation();
    const [rejectNotes, setRejectNotes] = useState('');
    const [processing, setProcessing] = useState(false);

    const statusLabel = prostheticReferralStatusLabel(referral.status, t);

    const postAction = (url: string, data: Record<string, string> = {}) => {
        setProcessing(true);
        router.post(url, data, { onFinish: () => setProcessing(false) });
    };

    const canAcceptReject = !['rejected', 'cancelled', 'converted_to_case'].includes(referral.status);

    return (
        <DashboardLayout>
            <Head title={referral.referral_number} />

            <div className={`mx-auto w-full min-w-0 space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={referral.referral_number}
                    subtitle={`${referral.patient?.name ?? '—'} · ${t('global.status')}: ${statusLabel}`}
                    icon="bx-transfer"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            <Button as={Link} href={urls.edit} color="light" size="sm">
                                {t('global.edit')}
                            </Button>
                            {!referral.converted_case_id ? (
                                <Button
                                    color="blue"
                                    size="sm"
                                    disabled={processing}
                                    onClick={() => {
                                        if (window.confirm(t('global.are_you_sure'))) {
                                            postAction(urls.convert);
                                        }
                                    }}
                                >
                                    {t('global.prosthetics_new_case')}
                                </Button>
                            ) : (
                                <Button as={Link} href={urls.caseShow} color="blue" size="sm">
                                    {t('global.prosthetics_case_detail')}
                                </Button>
                            )}
                        </div>
                    }
                />

                <IcuPanel
                    variant="table"
                    contentClassName={PANEL_BODY_CLASS}
                    title={t('global.prosthetics_referral')}
                    icon="bx-transfer"
                    iconClassName={PANEL_ICON_CLASS}
                    action={
                        <div className="flex flex-wrap gap-2">
                            <Badge color="info">{statusLabel}</Badge>
                            {referral.urgency && <Badge color="gray">{referral.urgency}</Badge>}
                        </div>
                    }
                >
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <DetailTile label={t('global.date')} value={referral.referral_date} />
                        <DetailTile label={t('global.patient_name')} value={referral.patient?.name} />
                        <DetailTile label={t('global.nid')} value={referral.patient?.nid} />
                        <DetailTile label={t('global.phone')} value={referral.patient?.phone} />
                        <DetailTile label={t('global.requested_department')} value={referral.referring_facility} />
                        <DetailTile label={t('global.doctor')} value={referral.referring_doctor} />
                        <DetailTile
                            label={t('global.prosthetics_service_type')}
                            value={referral.requested_service_type}
                        />
                        {referral.converted_case && (
                            <DetailTile
                                label={t('global.prosthetics_case')}
                                value={referral.converted_case.case_number}
                            />
                        )}
                    </div>
                </IcuPanel>

                <IcuPanel
                    variant="table"
                    contentClassName={PANEL_BODY_CLASS}
                    title={t('global.details')}
                    icon="bx-detail"
                    iconClassName={PANEL_ICON_CLASS}
                >
                    <div className="grid gap-4 lg:grid-cols-3">
                        <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-800/40">
                            <p className="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {t('global.reason')}
                            </p>
                            <p className="text-gray-900 dark:text-white">{referral.reason || '—'}</p>
                        </div>
                        <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-800/40">
                            <p className="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {t('global.diagnose')}
                            </p>
                            <p className="text-gray-900 dark:text-white">{referral.diagnosis_summary || '—'}</p>
                        </div>
                        <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-800/40">
                            <p className="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {t('global.notes')}
                            </p>
                            <p className="text-gray-900 dark:text-white">{referral.notes || '—'}</p>
                        </div>
                    </div>
                </IcuPanel>

                {canAcceptReject && (
                    <IcuPanel
                        variant="table"
                        contentClassName={PANEL_BODY_CLASS}
                        title={t('global.actions')}
                        icon="bx-cog"
                        iconClassName={PANEL_ICON_CLASS}
                    >
                        <div className="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
                            <Button color="success" size="sm" disabled={processing} onClick={() => postAction(urls.accept)}>
                                {t('global.yes')}
                            </Button>
                            <div className="min-w-[220px] flex-1">
                                <Label htmlFor="reject-notes" className="mb-1 text-xs">
                                    {t('global.reject_reason')}
                                </Label>
                                <TextInput
                                    id="reject-notes"
                                    sizing="sm"
                                    placeholder={t('global.reject_reason')}
                                    value={rejectNotes}
                                    onChange={(e) => setRejectNotes(e.target.value)}
                                />
                            </div>
                            <Button
                                color="failure"
                                outline
                                size="sm"
                                disabled={processing}
                                onClick={() => postAction(urls.reject, { notes: rejectNotes })}
                            >
                                {t('global.reject_request')}
                            </Button>
                        </div>
                    </IcuPanel>
                )}
            </div>
        </DashboardLayout>
    );
}
