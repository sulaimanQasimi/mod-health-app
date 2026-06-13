import { Badge, Card } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';

interface LaboratoryResultsSummaryProps {
    patientCount: number;
    registrationCount: number;
}

export default function LaboratoryResultsSummary({
    patientCount,
    registrationCount,
}: LaboratoryResultsSummaryProps) {
    const { t } = useTranslation();

    return (
        <Card className="mb-4 shadow-sm">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h2 className="font-semibold text-gray-900 dark:text-white">
                    {t('global.test_results')} — {t('global.patients')}
                </h2>
                <div className="flex flex-wrap gap-2">
                    <Badge color="info">
                        {patientCount} {t('global.patients')}
                    </Badge>
                    <Badge color="purple">
                        {registrationCount} {t('global.registrations') || 'registrations'}
                    </Badge>
                </div>
            </div>
        </Card>
    );
}
