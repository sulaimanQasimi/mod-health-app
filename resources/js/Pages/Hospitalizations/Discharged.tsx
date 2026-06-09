import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import HospitalizationDischargedFilters, {
    EMPTY_DISCHARGED_FILTERS,
} from '../../Components/Hospitalizations/HospitalizationDischargedFilters';
import HospitalizationTable from '../../Components/Hospitalizations/HospitalizationTable';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HospitalizationDischargedFilters as Filters,
    HospitalizationOption,
    PaginatedHospitalizations,
} from '../../types/hospitalization';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface DischargedProps {
    hospitalizations: PaginatedHospitalizations;
    filters: Filters;
    filterOptions: { rooms: HospitalizationOption[]; doctors: HospitalizationOption[] };
    urls: { current: string; index: string };
}

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function HospitalizationsDischarged({
    hospitalizations,
    filters: serverFilters,
    filterOptions,
    urls,
}: DischargedProps) {
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
            <Head title={t('global.discharged_hospitalizations')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.discharged_hospitalizations')}
                    subtitle={t('global.patients_list')}
                    icon="bx-exit"
                    accent="from-gray-600 to-gray-700"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                    action={
                        <Button as={Link} href={urls.index} color="success" size="sm">
                            <i className="bx bx-bed me-2" />
                            {t('global.hospitalized_patients')}
                        </Button>
                    }
                />

                <Card>
                    <HospitalizationDischargedFilters
                        filters={filters}
                        rooms={filterOptions.rooms}
                        doctors={filterOptions.doctors}
                        processing={processing}
                        onChange={setFilters}
                        onApply={applyFilters}
                        onReset={() => applyFilters(EMPTY_DISCHARGED_FILTERS)}
                    />
                </Card>

                <Card>
                    <div className="mb-3 text-sm text-gray-500">
                        {buildPaginationSummary(hospitalizations.meta, t)}
                    </div>
                    <HospitalizationTable items={hospitalizations.data} variant="discharged" />
                    <SettingsPagination links={hospitalizations.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
