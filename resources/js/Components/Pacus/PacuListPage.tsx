import CareUnitListPage from '../CareUnits/CareUnitListPage';
import {
    PacuListFilters,
    PacuListUrls,
    PacuListVariant,
    PaginatedPacus,
} from '../../types/pacu';
import PacuFilters, { EMPTY_PACU_FILTERS } from './PacuFilters';
import PacuListStats from './PacuListStats';
import PacuNavTabs from './PacuNavTabs';
import PacuTable from './PacuTable';
import { PACU_LIST_VARIANT_CONFIG, PACU_THEME } from './pacuUi';

interface PacuListPageProps {
    titleKey: string;
    variant: PacuListVariant;
    pacus: PaginatedPacus;
    filters: PacuListFilters;
    urls: PacuListUrls;
}

function serializeFilters(filters: PacuListFilters): Record<string, string> {
    const entries = Object.entries(filters).filter(([key, value]) => {
        if (value === '') return false;
        if (key === 'per_page' && value === '15') return false;
        return true;
    });

    return Object.fromEntries(entries);
}

function normalizeFilters(serverFilters: PacuListFilters): PacuListFilters {
    return {
        ...EMPTY_PACU_FILTERS,
        ...serverFilters,
    };
}

export default function PacuListPage({
    titleKey,
    variant,
    pacus,
    filters: serverFilters,
    urls,
}: PacuListPageProps) {
    const variantConfig = PACU_LIST_VARIANT_CONFIG[variant];

    return (
        <CareUnitListPage
            titleKey={titleKey}
            theme={{
                ...PACU_THEME,
                accent: variantConfig.accent,
                icon: variantConfig.icon,
            }}
            listTitleKey={titleKey}
            listIcon={variantConfig.icon}
            paginated={pacus}
            filters={serverFilters}
            emptyFilters={EMPTY_PACU_FILTERS}
            urls={urls}
            normalizeFilters={normalizeFilters}
            serializeFilters={serializeFilters}
            navTabs={<PacuNavTabs active={variant} urls={urls} />}
            stats={<PacuListStats variant={variant} meta={pacus.meta} />}
            filtersPanel={(props) => (
                <PacuFilters {...props} embedded />
            )}
            table={<PacuTable items={pacus.data} variant={variant} embedded />}
        />
    );
}
