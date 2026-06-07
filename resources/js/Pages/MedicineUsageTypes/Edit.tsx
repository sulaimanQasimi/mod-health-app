import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import MedicineUsageTypeForm from '../../Components/MedicineUsageTypes/MedicineUsageTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function EditMedicineUsageType({
    medicineUsageType,
    urls,
}: {
    medicineUsageType: { id: number; name: string; description: string };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className="mx-auto max-w-3xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={medicineUsageType.name}
                        icon="bx-edit"
                        accent="from-purple-500 to-violet-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <MedicineUsageTypeForm mode="edit" urls={urls} medicineUsageType={medicineUsageType} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
