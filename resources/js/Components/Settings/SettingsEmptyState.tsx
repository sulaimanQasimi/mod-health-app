import { useTranslation } from '../../hooks/useTranslation';

interface SettingsEmptyStateProps {
    message?: string;
}

export default function SettingsEmptyState({ message }: SettingsEmptyStateProps) {
    const { t } = useTranslation();

    return (
        <div className="rounded-lg border border-dashed border-gray-200 py-12 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            {message ?? t('global.no_results_found')}
        </div>
    );
}
