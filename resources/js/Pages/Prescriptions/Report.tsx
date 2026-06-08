import { Head, Link } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    urls: { index: string; legacyReport: string };
}

export default function Report({ urls }: ReportProps) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.reports')}
                        subtitle={t('global.prescriptions')}
                        icon="bx-bar-chart-alt-2"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />
                    <p className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        {t('global.reports')}
                    </p>
                    <Button color="blue" as="a" href={urls.legacyReport}>
                        {t('global.reports')}
                    </Button>
                    <Button color="light" as={Link} href={urls.index} className="ms-2">
                        {t('global.back')}
                    </Button>
                </Card>
            </div>
        </DashboardLayout>
    );
}
