import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../../utils/settingsUi';

interface CreateProps {
    urls: { index: string; store: string };
}

export default function ProstheticsReferralsCreate({ urls }: CreateProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState({
        patient_id: '',
        referral_date: new Date().toISOString().slice(0, 10),
        referring_facility: '',
        referring_doctor: '',
        reason: '',
        diagnosis_summary: '',
        notes: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        router.post(urls.store, form, { onFinish: () => setProcessing(false) });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.prosthetics_new_referral')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_new_referral')}
                    icon="bx-plus"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <Card>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <Label htmlFor="patient_id" value={`${t('global.prosthetics_patient_id')} *`} />
                            <TextInput
                                id="patient_id"
                                type="number"
                                min={1}
                                required
                                value={form.patient_id}
                                onChange={(e) => setForm((prev) => ({ ...prev, patient_id: e.target.value }))}
                            />
                        </div>
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
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="referring_facility" value={t('global.requested_department')} />
                                <TextInput
                                    id="referring_facility"
                                    value={form.referring_facility}
                                    onChange={(e) => setForm((prev) => ({ ...prev, referring_facility: e.target.value }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="referring_doctor" value={t('global.doctor')} />
                                <TextInput
                                    id="referring_doctor"
                                    value={form.referring_doctor}
                                    onChange={(e) => setForm((prev) => ({ ...prev, referring_doctor: e.target.value }))}
                                />
                            </div>
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
                            <Button as={Link} href={urls.index} color="light">
                                {t('global.back')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
