import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../../utils/settingsUi';

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

            <div className={`mx-auto space-y-6 ${SETTINGS_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={referral.referral_number}
                    icon="bx-edit"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.show}
                    backLabel={t('global.back')}
                />

                <Card>
                    <form onSubmit={handleSubmit} className="space-y-4">
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
                                className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700"
                                value={form.status}
                                onChange={(e) => setForm((prev) => ({ ...prev, status: e.target.value }))}
                            >
                                {statusOptions.map((status) => (
                                    <option key={status} value={status}>
                                        {status}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <Label htmlFor="reason" value={t('global.reason')} />
                            <Textarea id="reason" rows={2} value={form.reason} onChange={(e) => setForm((prev) => ({ ...prev, reason: e.target.value }))} />
                        </div>
                        <div>
                            <Label htmlFor="diagnosis_summary" value={t('global.diagnose')} />
                            <Textarea id="diagnosis_summary" rows={2} value={form.diagnosis_summary} onChange={(e) => setForm((prev) => ({ ...prev, diagnosis_summary: e.target.value }))} />
                        </div>
                        <div>
                            <Label htmlFor="notes" value={t('global.notes')} />
                            <Textarea id="notes" rows={2} value={form.notes} onChange={(e) => setForm((prev) => ({ ...prev, notes: e.target.value }))} />
                        </div>
                        <div className="flex gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {t('global.save')}
                            </Button>
                            <Button as={Link} href={urls.show} color="light">
                                {t('global.back')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
