import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import ProstheticCaseTable from '../../../Components/ProstheticsCases/ProstheticCaseTable';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../../hooks/useTranslation';
import { PaginatedProstheticCases } from '../../../types/prosthetics';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface CaseFilters {
    q: string;
    status: string;
}

interface IndexProps {
    cases: PaginatedProstheticCases;
    filters: CaseFilters;
    statusOptions: string[];
    urls: { current: string; create: string; show: string };
}

export default function ProstheticsCasesIndex({ cases, filters: serverFilters, statusOptions, urls }: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: CaseFilters) => {
            setProcessing(true);
            const payload = Object.fromEntries(Object.entries(next).filter(([, v]) => v !== ''));
            router.get(urls.current, payload, {
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
            <Head title={t('global.prosthetics_cases')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_cases')}
                    icon="bx-briefcase"
                    accent="from-emerald-500 to-teal-600"
                    action={
                        <Button as={Link} href={urls.create} color="blue" size="sm">
                            {t('global.prosthetics_new_case')}
                        </Button>
                    }
                />

                <Card>
                    <form
                        className="flex flex-wrap items-end gap-3"
                        onSubmit={(e) => {
                            e.preventDefault();
                            applyFilters(filters);
                        }}
                    >
                        <div className="min-w-[220px] flex-1">
                            <Label htmlFor="q" className="mb-1 text-xs text-gray-700 dark:text-gray-300">
                                {t('global.search')}
                            </Label>
                            <TextInput
                                id="q"
                                sizing="sm"
                                value={filters.q}
                                onChange={(e) => setFilters((prev) => ({ ...prev, q: e.target.value }))}
                            />
                        </div>
                        <div className="min-w-[180px]">
                            <Label htmlFor="status" className="mb-1 text-xs text-gray-700 dark:text-gray-300">
                                {t('global.status')}
                            </Label>
                            <select
                                id="status"
                                className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                value={filters.status}
                                onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
                            >
                                <option value="">{t('global.all')}</option>
                                {statusOptions.map((status) => (
                                    <option key={status} value={status}>
                                        {t(`global.prosthetics_case_status_${status}`)}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <Button type="submit" color="blue" size="sm" disabled={processing}>
                            {t('global.filter')}
                        </Button>
                        <Button
                            type="button"
                            color="light"
                            size="sm"
                            onClick={() => applyFilters({ q: '', status: '' })}
                        >
                            {t('global.reset')}
                        </Button>
                    </form>
                </Card>

                <Card>
                    <div className="mb-3 text-sm text-gray-500 dark:text-gray-400">
                        {buildPaginationSummary(cases.meta, t)}
                    </div>
                    <ProstheticCaseTable items={cases.data} showUrlBase={urls.show} />
                    <SettingsPagination links={cases.links} className="mt-4" />
                </Card>
            </div>
        </DashboardLayout>
    );
}
