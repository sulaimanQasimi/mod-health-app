import { Alert } from 'flowbite-react';
import { LaboratoryDepartmentScope } from '../../types/laboratory';
import { useTranslation } from '../../hooks/useTranslation';

interface LaboratoryDepartmentScopeBannerProps {
    scope: LaboratoryDepartmentScope;
}

export default function LaboratoryDepartmentScopeBanner({
    scope,
}: LaboratoryDepartmentScopeBannerProps) {
    const { t } = useTranslation();

    if (!scope.is_restricted) {
        return null;
    }

    if (!scope.department_id) {
        return (
            <Alert color="warning" className="mb-4">
                <span className="font-medium">{t('global.department')}</span>
                {' — '}
                {t('global.no_item_is_found')}
            </Alert>
        );
    }

    return (
        <Alert color="info" className="mb-4">
            <div className="flex flex-wrap items-center gap-2">
                <i className="bx bx-buildings text-lg" />
                <span>
                    {t('global.showing_results_for_department') || 'Showing tests for department'}:{' '}
                    <span className="font-semibold">{scope.department_name}</span>
                </span>
            </div>
        </Alert>
    );
}
