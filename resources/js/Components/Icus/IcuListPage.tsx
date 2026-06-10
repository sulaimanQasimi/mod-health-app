import { Head, router } from '@inertiajs/react';
import { Card } from 'flowbite-react';
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
import IcuNavTabs from './IcuNavTabs';
import IcuTable from './IcuTable';

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
        if (key === 'per_page' && value === '15') return false;
        if (showDischargeTabs && key === 'discharge_filter' && value === 'in_icu') return false;
        return true;
    });

    return Object.fromEntries(entries);
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
    const [filters, setFilters] = useState<IcuListFilters>({
        ...EMPTY_ICU_FILTERS,
        ...serverFilters,
        discharge_filter: serverFilters.discharge_filter ?? 'in_icu',
    });
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters({
            ...EMPTY_ICU_FILTERS,
            ...serverFilters,
            discharge_filter: serverFilters.discharge_filter ?? 'in_icu',
        });
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

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t(titleKey)}
                    subtitle={`${icus.meta.total} ${t('global.results')}`}
                    icon="bx-plus-medical"
                    accent="from-rose-600 to-red-700"
                    backLabel={t('global.back')}
                />

                <Card>
                    <IcuNavTabs active={variant} urls={urls} />
                </Card>

                <Card>
                    <IcuFilters
                        filters={filters}
                        processing={processing}
                        showDischargeTabs={showDischargeTabs}
                        onChange={setFilters}
                        onApply={applyFilters}
                        onReset={resetFilters}
                    />
                </Card>

                <Card>
                    <div className="mb-3 text-sm text-gray-500">
                        {buildPaginationSummary(icus.meta, t)}
                    </div>
                    <IcuTable items={icus.data} variant={variant} />
                    <SettingsPagination links={icus.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
