import { Head, router } from '@inertiajs/react';
import { Spinner } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../Layout/DashboardLayout';
import SettingsPageHeader from '../Settings/SettingsPageHeader';
import SettingsPagination from '../Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    IcuListFilters,
    IcuListUrls,
    IcuListVariant,
    PaginatedIcus,
} from '../../types/icu';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import IcuFilters, { EMPTY_ICU_FILTERS } from './IcuFilters';
import IcuListStats from './IcuListStats';
import IcuNavTabs from './IcuNavTabs';
import IcuPanel from './IcuPanel';
import IcuTable from './IcuTable';
import { ICU_LIST_VARIANT_CONFIG } from './icuUi';

interface IcuListPageProps {
    titleKey: string;
    variant: IcuListVariant;
    icus: PaginatedIcus;
    filters: IcuListFilters;
    urls: IcuListUrls;
    showDischargeTabs?: boolean;
}

function serializeFilters(filters: IcuListFilters, showDischargeTabs: boolean): Record<string, string> {
    const entries = Object.entries(filters).filter(([key, value]) => {
        if (value === '') return false;
        if (!showDischargeTabs && key === 'discharge_filter') return false;
        if (key === 'per_page' && value === '15') return false;
        if (showDischargeTabs && key === 'discharge_filter' && value === 'in_icu') return false;
        return true;
    });

    return Object.fromEntries(entries);
}

function normalizeFilters(serverFilters: IcuListFilters): IcuListFilters {
    return {
        ...EMPTY_ICU_FILTERS,
        ...serverFilters,
        discharge_filter: serverFilters.discharge_filter ?? 'in_icu',
    };
}

export default function IcuListPage({
    titleKey,
    variant,
    icus,
    filters: serverFilters,
    urls,
    showDischargeTabs = false,
}: IcuListPageProps) {
    const { t } = useTranslation();
    const variantConfig = ICU_LIST_VARIANT_CONFIG[variant];
    const [filters, setFilters] = useState<IcuListFilters>(() => normalizeFilters(serverFilters));
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(normalizeFilters(serverFilters));
    }, [serverFilters]);

    const applyFilters = useCallback(
        (next: IcuListFilters) => {
            setProcessing(true);
            router.get(urls.current, serializeFilters(next, showDischargeTabs), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current, showDischargeTabs]
    );

    const resetFilters = () => {
        const next = showDischargeTabs
            ? { ...EMPTY_ICU_FILTERS, discharge_filter: 'in_icu' }
            : EMPTY_ICU_FILTERS;
        setFilters(next);
        applyFilters(next);
    };

    return (
        <DashboardLayout>
            <Head title={t(titleKey)} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t(titleKey)}
                    subtitle={t('global.patients_list')}
                    icon={variantConfig.icon}
                    accent={variantConfig.accent}
                    backLabel={t('global.back')}
                />

                <IcuNavTabs active={variant} urls={urls} />

                <IcuListStats variant={variant} meta={icus.meta} />

                <IcuPanel
                    variant="filter"
                    title={t('global.advanced_filters')}
                    icon="bx-filter-alt"
                    description={t('global.search_patient_placeholder')}
                >
                    <IcuFilters
                        filters={filters}
                        processing={processing}
                        showDischargeTabs={showDischargeTabs}
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
                    action={
                        <span className="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                            {processing && <Spinner size="xs" />}
                            {buildPaginationSummary(icus.meta, t)}
                        </span>
                    }
                    footer={<SettingsPagination links={icus.links} />}
                >
                    <div className={`relative ${processing ? 'pointer-events-none opacity-60' : ''}`}>
                        {processing && (
                            <div className="absolute inset-0 z-10 flex items-center justify-center bg-white/40 dark:bg-gray-900/40">
                                <Spinner size="lg" color="failure" />
                            </div>
                        )}
                        <IcuTable items={icus.data} variant={variant} embedded />
                    </div>
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
