import { Head, router } from '@inertiajs/react';
import { Spinner } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../Layout/DashboardLayout';
import IcuPanel from '../Icus/IcuPanel';
import SettingsPageHeader from '../Settings/SettingsPageHeader';
import SettingsPagination from '../Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    OperationListFilters,
    OperationListUrls,
    OperationListVariant,
    PaginatedOperations,
    SelectOption,
} from '../../types/operation';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import OperationFilters, { EMPTY_OPERATION_FILTERS } from './OperationFilters';
import OperationListStats from './OperationListStats';
import OperationNavTabs from './OperationNavTabs';
import OperationTable from './OperationTable';
import { OPERATION_LIST_VARIANT_CONFIG, OPERATION_PANEL_ICON_CLASS } from './operationUi';

interface OperationListPageProps {
    titleKey: string;
    variant: OperationListVariant;
    operations: PaginatedOperations;
    filters: OperationListFilters;
    urls: OperationListUrls & { current: string };
    filterOptions: {
        branches: SelectOption[];
        departments: SelectOption[];
        operationTypes: SelectOption[];
        surgeons: SelectOption[];
    };
}

function serializeFilters(filters: OperationListFilters): Record<string, string> {
    return Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (value === '') return false;
            if (key === 'per_page' && value === '15') return false;
            if (key === 'sort_by' && value === 'date') return false;
            if (key === 'sort_order' && value === 'desc') return false;
            return true;
        })
    );
}

export default function OperationListPage({
    titleKey,
    variant,
    operations,
    filters: serverFilters,
    urls,
    filterOptions,
}: OperationListPageProps) {
    const { t } = useTranslation();
    const variantConfig = OPERATION_LIST_VARIANT_CONFIG[variant];
    const [filters, setFilters] = useState<OperationListFilters>(() => ({
        ...EMPTY_OPERATION_FILTERS,
        ...serverFilters,
    }));
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters({ ...EMPTY_OPERATION_FILTERS, ...serverFilters });
    }, [serverFilters]);

    const applyFilters = useCallback(
        (next: OperationListFilters) => {
            setProcessing(true);
            router.get(urls.current, serializeFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current]
    );

    const resetFilters = () => {
        setFilters(EMPTY_OPERATION_FILTERS);
        applyFilters(EMPTY_OPERATION_FILTERS);
    };

    return (
        <DashboardLayout>
            <Head title={t(titleKey)} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t(titleKey)}
                    subtitle={t(variantConfig.subtitleKey)}
                    icon={variantConfig.icon}
                    accent={variantConfig.accent}
                    backLabel={t('global.back')}
                />

                <OperationNavTabs active={variant} urls={urls} />

                <OperationListStats variant={variant} meta={operations.meta} />

                <IcuPanel
                    variant="filter"
                    title={t('global.advanced_filters')}
                    icon="bx-filter-alt"
                    iconClassName={OPERATION_PANEL_ICON_CLASS}
                    description={t('global.search_by_patient_operation')}
                >
                    <OperationFilters
                        filters={filters}
                        processing={processing}
                        branches={filterOptions.branches}
                        departments={filterOptions.departments}
                        operationTypes={filterOptions.operationTypes}
                        surgeons={filterOptions.surgeons}
                        embedded
                        onChange={setFilters}
                        onApply={applyFilters}
                        onReset={resetFilters}
                    />
                </IcuPanel>

                <IcuPanel
                    variant="table"
                    title={t(titleKey)}
                    icon={variantConfig.icon}
                    iconClassName={OPERATION_PANEL_ICON_CLASS}
                    action={
                        <span className="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                            {processing && <Spinner size="xs" />}
                            {buildPaginationSummary(operations.meta, t)}
                        </span>
                    }
                    footer={<SettingsPagination links={operations.links} />}
                >
                    <div className={`relative ${processing ? 'pointer-events-none opacity-60' : ''}`}>
                        {processing && (
                            <div className="absolute inset-0 z-10 flex items-center justify-center bg-white/40 dark:bg-gray-900/40">
                                <Spinner size="lg" color="warning" />
                            </div>
                        )}
                        <OperationTable items={operations.data} variant={variant} embedded />
                    </div>
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
