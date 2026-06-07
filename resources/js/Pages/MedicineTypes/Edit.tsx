import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import MedicineTypeForm from '../../Components/MedicineTypes/MedicineTypeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function EditMedicineType({
    medicineType,
    urls,
}: {
    medicineType: { id: number; type: string };
    urls: SettingsFormUrls;
}) {
    const { t } = useTranslation();
    return (
        <DashboardLayout>
            <Head title={t('global.edit')} />
            <div className="mx-auto max-w-2xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.edit')}
                        subtitle={medicineType.type}
                        icon="bx-edit"
                        accent="from-green-500 to-emerald-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <MedicineTypeForm mode="edit" urls={urls} medicineType={medicineType} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
