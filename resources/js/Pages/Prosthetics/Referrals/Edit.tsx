import { Head, Link, router } from '@inertiajs/react';
import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import IcuPanel from '../../../Components/Icus/IcuPanel';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import { prostheticReferralStatusLabel } from '../../../Components/ProstheticsReferrals/prostheticsReferralUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../../utils/settingsUi';

interface EditProps {
    referral: {
        id: number;
        referral_number: string;
        referral_date: string | null;
        status: string;
        reason: string | null;
        diagnosis_summary: string | null;
        notes: string | null;
    };
    statusOptions: string[];
    urls: { show: string; update: string };
}

const SELECT_CLASS =
    'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700';

export default function ProstheticsReferralsEdit({ referral, statusOptions, urls }: EditProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState({
        referral_date: referral.referral_date ?? '',
        status: referral.status,
        reason: referral.reason ?? '',
        diagnosis_summary: referral.diagnosis_summary ?? '',
        notes: referral.notes ?? '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        router.put(urls.update, form, { onFinish: () => setProcessing(false) });
    };

    return (
        <DashboardLayout>
            <Head title={referral.referral_number} />

            <div className={`mx-auto space-y-6 ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={referral.referral_number}
                    icon="bx-edit"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.show}
                    backLabel={t('global.back')}
                />

                <form onSubmit={handleSubmit}>
                    <IcuPanel
                        title={referral.referral_number}
                        icon="bx-file"
                        variant="filter"
                        contentClassName="space-y-4"
                        footer={
                            <div className="flex flex-wrap gap-2">
                                <Button type="submit" color="blue" disabled={processing}>
                                    {t('global.save')}
                                </Button>
                                <Button as={Link} href={urls.show} color="light">
                                    {t('global.back')}
                                </Button>
                            </div>
                        }
                    >
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="referral_date" value={`${t('global.date')} *`} />
                                <TextInput
                                    id="referral_date"
                                    type="date"
                                    required
                                    value={form.referral_date}
                                    onChange={(e) => setForm((prev) => ({ ...prev, referral_date: e.target.value }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="status" value={t('global.status')} />
                                <select
                                    id="status"
                                    className={SELECT_CLASS}
                                    value={form.status}
                                    onChange={(e) => setForm((prev) => ({ ...prev, status: e.target.value }))}
                                >
                                    {statusOptions.map((status) => (
                                        <option key={status} value={status}>
                                            {prostheticReferralStatusLabel(status, t)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                        <div>
                            <Label htmlFor="reason" value={t('global.reason')} />
                            <Textarea
                                id="reason"
                                rows={2}
                                value={form.reason}
                                onChange={(e) => setForm((prev) => ({ ...prev, reason: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="diagnosis_summary" value={t('global.diagnose')} />
                            <Textarea
                                id="diagnosis_summary"
                                rows={2}
                                value={form.diagnosis_summary}
                                onChange={(e) => setForm((prev) => ({ ...prev, diagnosis_summary: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="notes" value={t('global.notes')} />
                            <Textarea
                                id="notes"
                                rows={2}
                                value={form.notes}
                                onChange={(e) => setForm((prev) => ({ ...prev, notes: e.target.value }))}
                            />
                        </div>
                    </IcuPanel>
                </form>
            </div>
        </DashboardLayout>
    );
}
