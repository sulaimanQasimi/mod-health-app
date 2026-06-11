import { Head, router } from '@inertiajs/react';
import { Spinner } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../Layout/DashboardLayout';
import IcuPanel from '../Icus/IcuPanel';
import SettingsPageHeader from '../Settings/SettingsPageHeader';
import SettingsPagination from '../Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    BloodBankListUrls,
    BloodRequestFilterOptions,
    BloodRequestListFilters,
    BloodRequestListVariant,
    PaginatedBloodRequests,
} from '../../types/bloodBank';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import BloodBankFilters, { EMPTY_BLOOD_REQUEST_FILTERS } from './BloodBankFilters';
import BloodBankNavTabs from './BloodBankNavTabs';
import BloodBankTable from './BloodBankTable';
import { BLOOD_BANK_PANEL_ICON_CLASS, BLOOD_REQUEST_LIST_VARIANT_CONFIG } from './bloodBankUi';

interface BloodBankListPageProps {
    titleKey: string;
    variant: BloodRequestListVariant;
    bloodRequests: PaginatedBloodRequests;
    filters: BloodRequestListFilters;
    urls: BloodBankListUrls & { current: string };
    filterOptions: BloodRequestFilterOptions;
}

function serializeFilters(filters: BloodRequestListFilters): Record<string, string> {
    return Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (value === '') return false;
            if (key === 'per_page' && value === '15') return false;
            return true;
        }),
    );
}

export default function BloodBankListPage({
    titleKey,
    variant,
    bloodRequests,
    filters: serverFilters,
    urls,
    filterOptions,
}: BloodBankListPageProps) {
    const { t } = useTranslation();
    const variantConfig = BLOOD_REQUEST_LIST_VARIANT_CONFIG[variant];
    const [filters, setFilters] = useState<BloodRequestListFilters>(() => ({
        ...EMPTY_BLOOD_REQUEST_FILTERS,
        ...serverFilters,
    }));
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters({ ...EMPTY_BLOOD_REQUEST_FILTERS, ...serverFilters });
    }, [serverFilters]);

    const applyFilters = useCallback(
        (next: BloodRequestListFilters) => {
            setProcessing(true);
            router.get(urls.current, serializeFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current],
    );

    const resetFilters = () => {
        setFilters(EMPTY_BLOOD_REQUEST_FILTERS);
        applyFilters(EMPTY_BLOOD_REQUEST_FILTERS);
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
                    backHref={urls.dashboard}
                    backLabel={t('global.back')}
                />

                <BloodBankNavTabs active={variant} urls={urls} />

                <IcuPanel
                    variant="filter"
                    title={t('global.advanced_filters')}
                    icon="bx-filter-alt"
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                    description={t('global.search')}
                >
                    <BloodBankFilters
                        filters={filters}
                        processing={processing}
                        filterOptions={filterOptions}
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
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                    description={buildPaginationSummary(bloodRequests.meta, t)}
                    action={
                        processing ? (
                            <Spinner size="sm" color="failure" />
                        ) : (
                            <span className="text-sm text-gray-500 dark:text-gray-400">
                                {bloodRequests.meta.total} {t('global.records')}
                            </span>
                        )
                    }
                >
                    <BloodBankTable items={bloodRequests.data} />
                    <SettingsPagination links={bloodRequests.links} className="mt-4" />
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
