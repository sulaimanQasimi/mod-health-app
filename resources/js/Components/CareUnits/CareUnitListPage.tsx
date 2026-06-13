import { Head, router } from '@inertiajs/react';
import { Spinner } from 'flowbite-react';
import { ReactNode, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../Layout/DashboardLayout';
import SettingsPageHeader from '../Settings/SettingsPageHeader';
import SettingsPagination from '../Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginationLink, PaginationMeta } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import CareUnitPanel from './CareUnitPanel';
import { CareUnitTheme } from './careUnitUi';

interface CareUnitListPageProps<TFilters> {
    titleKey: string;
    subtitleKey?: string;
    theme: CareUnitTheme;
    listTitleKey: string;
    listIcon: string;
    paginated: {
        data: unknown[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    filters: TFilters;
    emptyFilters: TFilters;
    urls: { current: string };
    normalizeFilters: (filters: TFilters) => TFilters;
    serializeFilters: (filters: TFilters) => Record<string, string>;
    navTabs: ReactNode;
    stats: ReactNode;
    filtersPanel: (props: {
        filters: TFilters;
        processing: boolean;
        onChange: (filters: TFilters) => void;
        onApply: (filters: TFilters) => void;
        onReset: () => void;
    }) => ReactNode;
    table: ReactNode;
}

export default function CareUnitListPage<TFilters>({
    titleKey,
    subtitleKey = 'global.patients_list',
    theme,
    listTitleKey,
    listIcon,
    paginated,
    filters: serverFilters,
    emptyFilters,
    urls,
    normalizeFilters,
    serializeFilters,
    navTabs,
    stats,
    filtersPanel,
    table,
}: CareUnitListPageProps<TFilters>) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<TFilters>(() => normalizeFilters(serverFilters));
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(normalizeFilters(serverFilters));
    }, [serverFilters]);

    const applyFilters = useCallback(
        (next: TFilters) => {
            setProcessing(true);
            router.get(urls.current, serializeFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current, serializeFilters]
    );

    const resetFilters = () => {
        setFilters(emptyFilters);
        applyFilters(emptyFilters);
    };

    return (
        <DashboardLayout>
            <Head title={t(titleKey)} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t(titleKey)}
                    subtitle={t(subtitleKey)}
                    icon={theme.icon}
                    accent={theme.accent}
                    backLabel={t('global.back')}
                />

                {navTabs}

                {stats}

                <CareUnitPanel
                    variant="filter"
                    title={t('global.advanced_filters')}
                    icon="bx-filter-alt"
                    iconClassName={theme.panelIconClass}
                    iconBgClassName={theme.panelIconBgClass}
                    description={t('global.search_patient_placeholder')}
                >
                    {filtersPanel({
                        filters,
                        processing,
                        onChange: setFilters,
                        onApply: applyFilters,
                        onReset: resetFilters,
                    })}
                </CareUnitPanel>

                <CareUnitPanel
                    variant="table"
                    title={t(listTitleKey)}
                    icon={listIcon}
                    iconClassName={theme.panelIconClass}
                    iconBgClassName={theme.panelIconBgClass}
                    action={
                        <span
                            className={`inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium ${theme.listSummaryBadge}`}
                        >
                            {processing && <Spinner size="xs" />}
                            {buildPaginationSummary(paginated.meta, t)}
                        </span>
                    }
                    footer={<SettingsPagination links={paginated.links} />}
                >
                    <div className={`relative ${processing ? 'pointer-events-none opacity-60' : ''}`}>
                        {processing && (
                            <div className="absolute inset-0 z-10 flex items-center justify-center bg-white/40 dark:bg-gray-900/40">
                                <Spinner size="lg" color="failure" />
                            </div>
                        )}
                        {table}
                    </div>
                </CareUnitPanel>
            </div>
        </DashboardLayout>
    );
}
