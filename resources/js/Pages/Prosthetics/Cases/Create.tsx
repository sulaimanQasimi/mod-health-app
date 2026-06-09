import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../../utils/settingsUi';

interface CreateProps {
    prefill: {
        referral_id?: number;
        patient_id?: number;
        patient_name?: string | null;
        primary_diagnosis?: string | null;
    } | null;
    formOptions: {
        sides: string[];
        categories: string[];
        priorities: string[];
    };
    urls: { index: string; store: string };
}

export default function ProstheticsCasesCreate({ prefill, formOptions, urls }: CreateProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState({
        referral_id: prefill?.referral_id ? String(prefill.referral_id) : '',
        patient_id: prefill?.patient_id ? String(prefill.patient_id) : '',
        side: 'left',
        case_category: 'prosthetic',
        priority: 'normal',
        body_region: '',
        device_type: '',
        primary_diagnosis: prefill?.primary_diagnosis ?? '',
        cause_of_loss_notes: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        const payload = { ...form };
        if (!payload.referral_id) {
            delete (payload as { referral_id?: string }).referral_id;
        }
        router.post(urls.store, payload, { onFinish: () => setProcessing(false) });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.prosthetics_new_case')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_new_case')}
                    icon="bx-plus"
                    accent="from-emerald-500 to-teal-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <Card>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        {prefill?.referral_id && (
                            <input type="hidden" name="referral_id" value={form.referral_id} />
                        )}
                        {prefill?.patient_name && (
                            <p className="text-sm text-gray-500">
                                {t('global.patient_name')}: {prefill.patient_name}
                            </p>
                        )}
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
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="side" value={`${t('global.prosthetics_side')} *`} />
                                <select
                                    id="side"
                                    required
                                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700"
                                    value={form.side}
                                    onChange={(e) => setForm((prev) => ({ ...prev, side: e.target.value }))}
                                >
                                    {formOptions.sides.map((side) => (
                                        <option key={side} value={side}>
                                            {t(`global.prosthetics_side_${side}`)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <Label htmlFor="case_category" value={`${t('global.category')} *`} />
                                <select
                                    id="case_category"
                                    required
                                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700"
                                    value={form.case_category}
                                    onChange={(e) => setForm((prev) => ({ ...prev, case_category: e.target.value }))}
                                >
                                    {formOptions.categories.map((category) => (
                                        <option key={category} value={category}>
                                            {t(`global.prosthetics_category_${category}`)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                        <div>
                            <Label htmlFor="priority" value={t('global.priority')} />
                            <select
                                id="priority"
                                className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700"
                                value={form.priority}
                                onChange={(e) => setForm((prev) => ({ ...prev, priority: e.target.value }))}
                            >
                                {formOptions.priorities.map((priority) => (
                                    <option key={priority} value={priority}>
                                        {priority}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <Label htmlFor="body_region" value={t('global.prosthetics_body_region')} />
                            <TextInput
                                id="body_region"
                                value={form.body_region}
                                onChange={(e) => setForm((prev) => ({ ...prev, body_region: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="device_type" value={t('global.prosthetics_device_type')} />
                            <TextInput
                                id="device_type"
                                value={form.device_type}
                                onChange={(e) => setForm((prev) => ({ ...prev, device_type: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="primary_diagnosis" value={t('global.diagnose')} />
                            <Textarea
                                id="primary_diagnosis"
                                rows={2}
                                value={form.primary_diagnosis}
                                onChange={(e) => setForm((prev) => ({ ...prev, primary_diagnosis: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="cause_of_loss_notes" value={t('global.prosthetics_cause_of_loss')} />
                            <Textarea
                                id="cause_of_loss_notes"
                                rows={2}
                                value={form.cause_of_loss_notes}
                                onChange={(e) => setForm((prev) => ({ ...prev, cause_of_loss_notes: e.target.value }))}
                            />
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
