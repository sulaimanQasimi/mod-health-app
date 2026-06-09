import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import ProstheticReferralFilters, {
    EMPTY_PROSTHETIC_REFERRAL_FILTERS,
} from '../../../Components/ProstheticsReferrals/ProstheticReferralFilters';
import ProstheticReferralTable from '../../../Components/ProstheticsReferrals/ProstheticReferralTable';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../../hooks/useTranslation';
import { PaginatedProstheticReferrals, ProstheticReferralFilters as Filters } from '../../../types/prosthetics';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface IndexProps {
    referrals: PaginatedProstheticReferrals;
    filters: Filters;
    statusOptions: string[];
    urls: { current: string; create: string; show: string };
}

function cleanFilters(filters: Filters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function ProstheticsReferralsIndex({ referrals, filters: serverFilters, statusOptions, urls }: IndexProps) {
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
            <Head title={t('global.prosthetics_referrals')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_referrals')}
                    icon="bx-transfer"
                    accent="from-indigo-500 to-blue-600"
                    action={
                        <Button as={Link} href={urls.create} color="blue" size="sm">
                            <i className="bx bx-plus me-2" />
                            {t('global.prosthetics_new_referral')}
                        </Button>
                    }
                />

                <Card>
                    <ProstheticReferralFilters
                        filters={filters}
                        statusOptions={statusOptions}
                        processing={processing}
                        onChange={setFilters}
                        onApply={applyFilters}
                        onReset={() => applyFilters(EMPTY_PROSTHETIC_REFERRAL_FILTERS)}
                    />
                </Card>

                <Card>
                    <div className="mb-3 text-sm text-gray-500">
                        {buildPaginationSummary(referrals.meta, t)}
                    </div>
                    <ProstheticReferralTable items={referrals.data} showUrlBase={urls.show} />
                    <SettingsPagination links={referrals.links} className="mt-4" />
                </Card>
            </div>
        </DashboardLayout>
    );
}
