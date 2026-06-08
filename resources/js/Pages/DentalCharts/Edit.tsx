import { Head, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DentalChartForm, {
    dentalChartFormFromRecord,
    dentalChartPayload,
} from '../../Components/DentalCharts/DentalChartForm';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import { DentalChartRecord, DentalChartRegistrationHeader, DentalChartUrls } from '../../types/dentalChart';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface EditDentalChartProps {
    registration: DentalChartRegistrationHeader;
    chart: DentalChartRecord;
    urls: DentalChartUrls;
}

export default function EditDentalChart({ registration, chart, urls }: EditDentalChartProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState(dentalChartFormFromRecord(chart));

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!urls.update) return;
        setProcessing(true);
        router.put(urls.update, dentalChartPayload(form, false), {
            onFinish: () => setProcessing(false),
        });
    };

    const handleDelete = () => {
        if (!urls.destroy || !window.confirm(t('global.are_you_sure'))) return;
        setProcessing(true);
        router.delete(urls.destroy, { onFinish: () => setProcessing(false) });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.edit_dental_chart')} />
            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <SettingsPageHeader
                    title={`${t('global.edit_dental_chart')} - FDI ${chart.tooth_number}`}
                    subtitle={chart.chart_date ?? '—'}
                    icon="bx-grid-alt"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.registrationShow}
                    backLabel={t('global.back')}
                    action={
                        <Button color="failure" size="sm" onClick={handleDelete} disabled={processing}>
                            <i className="bx bx-trash me-2" />
                            {t('global.delete')}
                        </Button>
                    }
                />
                <Card className="shadow-sm">
                    <DentalChartForm
                        form={form}
                        processing={processing}
                        showToothSelect={false}
                        submitLabel={t('global.update')}
                        onChange={setForm}
                        onSubmit={handleSubmit}
                        onCancel={() => router.visit(urls.registrationShow)}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
