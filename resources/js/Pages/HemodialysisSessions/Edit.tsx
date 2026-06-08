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
    HemodialysisSessionFormData,
    HemodialysisSessionFormOptions,
} from '../../types/hemodialysisSession';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface EditHemodialysisSessionProps {
    session: HemodialysisSessionFormData;
    formOptions: HemodialysisSessionFormOptions;
    urls: {
        show: string;
        update: string;
    };
}

function buildInitialValues(session: HemodialysisSessionFormData): HemodialysisSessionFormValues {
    return {
        patient_id: String(session.patient_id),
        nephrology_registration_id: session.nephrology_registration_id
            ? String(session.nephrology_registration_id)
            : '',
        doctor_id: session.doctor_id ? String(session.doctor_id) : '',
        diagnosis: session.diagnosis ?? session.default_diagnosis ?? '',
        dialysis_schedule: session.dialysis_schedule ?? '',
        session_date: session.session_date ?? '',
        session_time: session.session_time ?? '',
        duration_minutes: session.duration_minutes != null ? String(session.duration_minutes) : '',
        vascular_access_type: session.vascular_access_type ?? '',
        pre_blood_pressure: session.pre_blood_pressure ?? '',
        pre_weight: session.pre_weight != null ? String(session.pre_weight) : '',
        pre_pulse: session.pre_pulse != null ? String(session.pre_pulse) : '',
        pre_temperature: session.pre_temperature != null ? String(session.pre_temperature) : '',
        post_blood_pressure: session.post_blood_pressure ?? '',
        post_weight: session.post_weight != null ? String(session.post_weight) : '',
        post_pulse: session.post_pulse != null ? String(session.post_pulse) : '',
        post_temperature: session.post_temperature != null ? String(session.post_temperature) : '',
        fluid_removed_ml: session.fluid_removed_ml != null ? String(session.fluid_removed_ml) : '',
        dialyzer_type: session.dialyzer_type ?? '',
        blood_type: session.blood_type ?? '',
        complications_notes: session.complications_notes ?? '',
        status: session.status,
    };
}

export default function EditHemodialysisSession({ session, formOptions, urls }: EditHemodialysisSessionProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (values: HemodialysisSessionFormValues) => {
        setProcessing(true);
        router.put(urls.update, buildHemodialysisPayload(values), {
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.hemodialysis_session')}
                    subtitle={`${t('global.ref_no')}: ${session.ref_no ?? '—'}`}
                    icon="bx-water"
                    accent="from-sky-500 to-blue-600"
                    backHref={urls.show}
                    backLabel={t('global.back')}
                />

                <Card className="shadow-sm">
                    <HemodialysisSessionForm
                        formId="hemodialysis-edit-form"
                        initialValues={buildInitialValues(session)}
                        formOptions={formOptions}
                        patientLocked
                        patientLabel={session.patient_label}
                        disabled={processing}
                        onSubmit={handleSubmit}
                    />

                    <div className="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <Button as={Link} href={urls.show} color="gray" size="sm" disabled={processing}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" form="hemodialysis-edit-form" color="blue" size="sm" disabled={processing}>
                            <i className="bx bx-save me-2" />
                            {t('global.save')}
                        </Button>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
