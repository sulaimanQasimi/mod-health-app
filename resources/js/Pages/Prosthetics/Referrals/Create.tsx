import { Head, Link, router } from '@inertiajs/react';
import { Button } from 'flowbite-react';
import { useState } from 'react';
import IcuPanel from '../../../Components/Icus/IcuPanel';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import ProstheticReferralForm, {
    ProstheticReferralFormValues,
} from '../../../Components/ProstheticsReferrals/ProstheticReferralForm';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../../utils/settingsUi';

interface CreateProps {
    prefill?: { patient_id?: number | null };
    urls: {
        index: string;
        store: string;
        patientSearch: string;
    };
}

function buildInitialValues(prefill?: CreateProps['prefill']): ProstheticReferralFormValues {
    return {
        patient_id: prefill?.patient_id ? String(prefill.patient_id) : '',
        referral_date: new Date().toISOString().slice(0, 10),
        referring_facility: '',
        referring_doctor: '',
        reason: '',
        diagnosis_summary: '',
        urgency: 'routine',
        requested_service_type: '',
        notes: '',
    };
}

const REFERRAL_FORM_ID = 'prosthetic-referral-create-form';

export default function ProstheticsReferralsCreate({ prefill, urls }: CreateProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (values: ProstheticReferralFormValues) => {
        setProcessing(true);
        router.post(urls.store, values, { onFinish: () => setProcessing(false) });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.prosthetics_new_referral')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_WIDE_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_new_referral')}
                    icon="bx-plus"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <IcuPanel
                    title={t('global.prosthetics_new_referral')}
                    icon="bx-file"
                    variant="filter"
                    contentClassName="space-y-4"
                    footer={
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" form={REFERRAL_FORM_ID} color="blue" disabled={processing}>
                                {t('global.save')}
                            </Button>
                            <Button as={Link} href={urls.index} color="light">
                                {t('global.back')}
                            </Button>
                        </div>
                    }
                >
                    <ProstheticReferralForm
                        formId={REFERRAL_FORM_ID}
                        initialValues={buildInitialValues(prefill)}
                        patientSearchUrl={urls.patientSearch}
                        disabled={processing}
                        submitLabel={t('global.save')}
                        hideActions
                        onSubmit={handleSubmit}
                    />
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
