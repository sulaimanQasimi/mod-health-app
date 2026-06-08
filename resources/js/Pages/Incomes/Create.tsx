import { Head } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import IncomeForm from '../../Components/Incomes/IncomeForm';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem } from '../../types/settings';
export default function CreateIncome({
    formData,
    urls,
}: {
    formData: { medicines: OptionItem[]; incomeTypes: string[] };
    urls: { index: string; store: string; back: string };
}) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.create_income_record')} />
            <div className="mx-auto max-w-5xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.create_income_record')}
                        subtitle={t('global.income_records')}
                        icon="bx-log-in"
                        accent="from-green-500 to-emerald-600"
                        backHref={urls.back}
                        backLabel={t('global.back')}
                    />
                    <IncomeForm formData={formData} urls={urls} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
