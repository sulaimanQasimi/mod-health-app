import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useState } from 'react';
import HemodialysisSessionForm, {
    buildHemodialysisPayload,
    HemodialysisSessionFormValues,
} from '../../Components/HemodialysisSessions/HemodialysisSessionForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HemodialysisSessionFormOptions,
    HemodialysisSessionPrefill,
} from '../../types/hemodialysisSession';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface CreateHemodialysisSessionProps {
    formOptions: HemodialysisSessionFormOptions;
    prefill: HemodialysisSessionPrefill;
    urls: {
        index: string;
        store: string;
    };
}

function buildInitialValues(prefill: HemodialysisSessionPrefill): HemodialysisSessionFormValues {
    return {
        patient_id: prefill.patient ? String(prefill.patient.id) : '',
        nephrology_registration_id: prefill.registration ? String(prefill.registration.id) : '',
        doctor_id: '',
        diagnosis: prefill.registration?.diagnosis ?? '',
        dialysis_schedule: '',
        session_date: '',
        session_time: '',
        duration_minutes: '',
        vascular_access_type: '',
        pre_blood_pressure: '',
        pre_weight: '',
        pre_pulse: '',
        pre_temperature: '',
        post_blood_pressure: '',
        post_weight: '',
        post_pulse: '',
        post_temperature: '',
        fluid_removed_ml: '',
        dialyzer_type: '',
        blood_type: '',
        complications_notes: '',
        status: 'pending',
    };
}

export default function CreateHemodialysisSession({ formOptions, prefill, urls }: CreateHemodialysisSessionProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (values: HemodialysisSessionFormValues) => {
        setProcessing(true);
        router.post(urls.store, buildHemodialysisPayload(values), {
            onFinish: () => setProcessing(false),
        });
    };

    const patientLabel = prefill.patient
        ? `${prefill.patient.name} (${prefill.patient.identifier})`
        : null;
    const registrationLabel = prefill.registration
        ? `${t('global.ref_no')}: ${prefill.registration.ref_no}`
        : null;

    return (
        <DashboardLayout>
            <Head title={t('global.add_hemodialysis_session')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.add_hemodialysis_session')}
                    icon="bx-water"
                    accent="from-sky-500 to-blue-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <Card className="shadow-sm">
                    <HemodialysisSessionForm
                        formId="hemodialysis-create-form"
                        initialValues={buildInitialValues(prefill)}
                        formOptions={formOptions}
                        patientLocked={Boolean(prefill.patient)}
                        patientLabel={patientLabel}
                        registrationLocked={Boolean(prefill.registration)}
                        registrationLabel={registrationLabel}
                        disabled={processing}
                        onSubmit={handleSubmit}
                    />

                    <div className="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <Button as={Link} href={urls.index} color="gray" size="sm" disabled={processing}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" form="hemodialysis-create-form" color="blue" size="sm" disabled={processing}>
                            <i className="bx bx-save me-2" />
                            {t('global.save')}
                        </Button>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
