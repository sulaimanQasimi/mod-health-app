import { Head, router } from '@inertiajs/react';
import { Spinner } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../Layout/DashboardLayout';
import IcuPanel from '../Icus/IcuPanel';
import SettingsPageHeader from '../Settings/SettingsPageHeader';
import SettingsPagination from '../Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    AnesthesiaListFilters,
    AnesthesiaListUrls,
    AnesthesiaListVariant,
    PaginatedAnesthesias,
    SelectOption,
} from '../../types/anesthesia';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import AnesthesiaFilters, { EMPTY_ANESTHESIA_FILTERS } from './AnesthesiaFilters';
import AnesthesiaListStats from './AnesthesiaListStats';
import AnesthesiaNavTabs from './AnesthesiaNavTabs';
import AnesthesiaTable from './AnesthesiaTable';
import { ANESTHESIA_LIST_VARIANT_CONFIG, ANESTHESIA_PANEL_ICON_CLASS } from './anesthesiaUi';

interface AnesthesiaListPageProps {
    titleKey: string;
    variant: AnesthesiaListVariant;
    anesthesias: PaginatedAnesthesias;
    filters: AnesthesiaListFilters;
    urls: AnesthesiaListUrls;
    filterOptions: {
        operationTypes: SelectOption[];
        departments: SelectOption[];
    };
}

function serializeFilters(filters: AnesthesiaListFilters): Record<string, string> {
    return Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (value === '') return false;
            if (key === 'per_page' && value === '15') return false;
            return true;
        })
    );
}

export default function AnesthesiaListPage({
    titleKey,
    variant,
    anesthesias,
    filters: serverFilters,
    urls,
    filterOptions,
}: AnesthesiaListPageProps) {
    const { t } = useTranslation();
    const variantConfig = ANESTHESIA_LIST_VARIANT_CONFIG[variant];
    const [filters, setFilters] = useState<AnesthesiaListFilters>(() => ({
        ...EMPTY_ANESTHESIA_FILTERS,
        ...serverFilters,
    }));
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters({ ...EMPTY_ANESTHESIA_FILTERS, ...serverFilters });
    }, [serverFilters]);

    const applyFilters = useCallback(
        (next: AnesthesiaListFilters) => {
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
        setFilters(EMPTY_ANESTHESIA_FILTERS);
        applyFilters(EMPTY_ANESTHESIA_FILTERS);
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

                <AnesthesiaNavTabs active={variant} urls={urls} />

                <AnesthesiaListStats variant={variant} meta={anesthesias.meta} />

                <IcuPanel
                    variant="filter"
                    title={t('global.advanced_filters')}
                    icon="bx-filter-alt"
                    iconClassName={ANESTHESIA_PANEL_ICON_CLASS}
                    description={t('global.search_by_patient_operation')}
                >
                    <AnesthesiaFilters
                        filters={filters}
                        processing={processing}
                        operationTypes={filterOptions.operationTypes}
                        departments={filterOptions.departments}
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
                    iconClassName={ANESTHESIA_PANEL_ICON_CLASS}
                    action={
                        <span className="inline-flex items-center gap-2 rounded-full bg-violet-50 px-3 py-1 text-xs font-medium text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">
                            {processing && <Spinner size="xs" />}
                            {buildPaginationSummary(anesthesias.meta, t)}
                        </span>
                    }
                    footer={<SettingsPagination links={anesthesias.links} />}
                >
                    <div className={`relative ${processing ? 'pointer-events-none opacity-60' : ''}`}>
                        {processing && (
                            <div className="absolute inset-0 z-10 flex items-center justify-center bg-white/40 dark:bg-gray-900/40">
                                <Spinner size="lg" color="purple" />
                            </div>
                        )}
                        <AnesthesiaTable items={anesthesias.data} variant={variant} embedded />
                    </div>
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
