import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
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

export default function ProstheticsReferralsShow({ referral, urls }: ShowProps) {
    const { t } = useTranslation();
    const [rejectNotes, setRejectNotes] = useState('');
    const [processing, setProcessing] = useState(false);

    const postAction = (url: string, data: Record<string, string> = {}) => {
        setProcessing(true);
        router.post(url, data, { onFinish: () => setProcessing(false) });
    };

    const canAcceptReject = !['rejected', 'cancelled', 'converted_to_case'].includes(referral.status);

    return (
        <DashboardLayout>
            <Head title={referral.referral_number} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <SettingsPageHeader
                    title={referral.referral_number}
                    subtitle={`${referral.patient?.name ?? '—'} — ${t('global.status')}: ${referral.status}`}
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
                                        if (window.confirm(t('global.prosthetics_convert_confirm') || 'Create case from this referral?')) {
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

                <Card>
                    <div className="mb-4 flex flex-wrap gap-2">
                        <Badge color="info">{referral.status}</Badge>
                        {referral.urgency && <Badge color="gray">{referral.urgency}</Badge>}
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {[
                            [t('global.date'), referral.referral_date],
                            [t('global.patient_name'), referral.patient?.name],
                            [t('global.nid'), referral.patient?.nid],
                            [t('global.phone'), referral.patient?.phone],
                            [t('global.requested_department'), referral.referring_facility],
                            [t('global.doctor'), referral.referring_doctor],
                            [t('global.prosthetics_service_type'), referral.requested_service_type],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-800/40"
                            >
                                <p className="text-xs text-gray-500">{label}</p>
                                <p className="font-medium">{value || '—'}</p>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card className="space-y-3 text-sm">
                    <p><strong>{t('global.reason')}:</strong> {referral.reason || '—'}</p>
                    <p><strong>{t('global.diagnose')}:</strong> {referral.diagnosis_summary || '—'}</p>
                    <p><strong>{t('global.notes')}:</strong> {referral.notes || '—'}</p>
                    {referral.converted_case && (
                        <p>
                            <strong>{t('global.prosthetics_case')}:</strong>{' '}
                            <code>{referral.converted_case.case_number}</code>
                        </p>
                    )}
                </Card>

                {canAcceptReject && (
                    <Card>
                        <h3 className="mb-3 text-sm font-semibold">{t('global.actions')}</h3>
                        <div className="flex flex-wrap items-start gap-2">
                            <Button color="green" size="sm" disabled={processing} onClick={() => postAction(urls.accept)}>
                                {t('global.yes')}
                            </Button>
                            <input
                                type="text"
                                className="rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700"
                                placeholder={t('global.reject_reason')}
                                value={rejectNotes}
                                onChange={(e) => setRejectNotes(e.target.value)}
                            />
                            <Button
                                color="red"
                                outline
                                size="sm"
                                disabled={processing}
                                onClick={() => postAction(urls.reject, { notes: rejectNotes })}
                            >
                                {t('global.reject_request')}
                            </Button>
                        </div>
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}
