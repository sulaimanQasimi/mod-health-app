import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface ShowProps {
    referral: {
        id: number;
        referral_number: string;
        status: string;
        referral_date: string | null;
        reason: string | null;
        diagnosis_summary: string | null;
        notes: string | null;
        converted_case_id: number | null;
        patient: { id: number; name: string } | null;
        converted_case: { id: number; case_number: string } | null;
    };
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

                <Card className="space-y-2 text-sm">
                    <p><strong>{t('global.reason')}:</strong> {referral.reason || '—'}</p>
                    <p><strong>{t('global.diagnose')}:</strong> {referral.diagnosis_summary || '—'}</p>
                    <p><strong>{t('global.notes')}:</strong> {referral.notes || '—'}</p>
                </Card>

                {referral.status !== 'rejected' && (
                    <Card>
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
