import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import HospitalizationFilters, {
    EMPTY_HOSPITALIZATION_FILTERS,
} from '../../Components/Hospitalizations/HospitalizationFilters';
import HospitalizationTable from '../../Components/Hospitalizations/HospitalizationTable';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HospitalizationActiveFilters,
    HospitalizationOption,
    PaginatedHospitalizations,
} from '../../types/hospitalization';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface IndexProps {
    hospitalizations: PaginatedHospitalizations;
    filters: HospitalizationActiveFilters;
    filterOptions: { rooms: HospitalizationOption[] };
    urls: { current: string; discharged: string };
}

function cleanFilters(filters: HospitalizationActiveFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function HospitalizationsIndex({
    hospitalizations,
    filters: serverFilters,
    filterOptions,
    urls,
}: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: HospitalizationActiveFilters) => {
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
            <Head title={t('global.hospitalized_patients')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.hospitalized_patients')}
                    subtitle={t('global.patients_list')}
                    icon="bx-bed"
                    accent="from-emerald-600 to-emerald-700"
                    action={
                        <Button as={Link} href={urls.discharged} color="light" size="sm">
                            <i className="bx bx-exit me-2" />
                            {t('global.discharged_hospitalizations')}
                        </Button>
                    }
                />

                <Card>
                    <HospitalizationFilters
                        filters={filters}
                        rooms={filterOptions.rooms}
                        processing={processing}
                        onChange={setFilters}
                        onApply={applyFilters}
                        onReset={() => applyFilters(EMPTY_HOSPITALIZATION_FILTERS)}
                    />
                </Card>

                <Card>
                    <div className="mb-3 text-sm text-gray-500">
                        {buildPaginationSummary(hospitalizations.meta, t)}
                    </div>
                    <HospitalizationTable items={hospitalizations.data} />
                    <SettingsPagination links={hospitalizations.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
