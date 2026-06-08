import { Head, router } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DentalChartForm, {
    dentalChartPayload,
    emptyDentalChartForm,
} from '../../Components/DentalCharts/DentalChartForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { DentalChartRegistrationHeader, DentalChartUrls } from '../../types/dentalChart';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface CreateDentalChartProps {
    registration: DentalChartRegistrationHeader;
    urls: DentalChartUrls;
}

export default function CreateDentalChart({ registration, urls }: CreateDentalChartProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState(emptyDentalChartForm());

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.post(urls.store, dentalChartPayload(form), {
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.create_dental_chart')} />
            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <SettingsPageHeader
                    title={t('global.create_dental_chart')}
                    subtitle={`${registration.patient_name ?? '—'} · #${registration.ref_no ?? registration.id}`}
                    icon="bx-grid-alt"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.registrationShow}
                    backLabel={t('global.back')}
                />
                <Card className="shadow-sm">
                    <h2 className="mb-4 text-sm font-semibold">{t('global.chart_information')}</h2>
                    <DentalChartForm
                        form={form}
                        processing={processing}
                        submitLabel={t('global.save')}
                        onChange={setForm}
                        onSubmit={handleSubmit}
                        onCancel={() => router.visit(urls.registrationShow)}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
