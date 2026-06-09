import { Head, router } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import UnderReviewFilters, {
    EMPTY_UNDER_REVIEW_FILTERS,
} from '../../Components/UnderReviews/UnderReviewFilters';
import UnderReviewTable from '../../Components/UnderReviews/UnderReviewTable';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedUnderReviews, UnderReviewFilters as Filters } from '../../types/underReview';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface IndexProps {
    underReviews: PaginatedUnderReviews;
    filters: Filters;
    urls: { current: string; show: string };
}

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function UnderReviewsIndex({ underReviews, filters: serverFilters, urls }: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: Filters) => {
            setProcessing(true);
            router.get(urls.current, cleanFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current]
    );

    return (
        <DashboardLayout>
            <Head title={t('global.under_review_patients')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.under_review_patients')}
                    subtitle={t('global.patients_list')}
                    icon="bx-revision"
                    accent="from-slate-600 to-slate-700"
                    backLabel={t('global.back')}
                />

                <Card>
                    <UnderReviewFilters
                        filters={filters}
                        processing={processing}
                        onChange={setFilters}
                        onApply={applyFilters}
                        onReset={() => applyFilters(EMPTY_UNDER_REVIEW_FILTERS)}
                    />
                </Card>

                <Card>
                    <div className="mb-3 text-sm text-gray-500">
                        {buildPaginationSummary(underReviews.meta, t)}
                    </div>
                    <UnderReviewTable items={underReviews.data} />
                    <SettingsPagination links={underReviews.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
