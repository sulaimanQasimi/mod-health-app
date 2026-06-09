import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../../hooks/useTranslation';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface ReportCase {
    id: number;
    case_number: string;
    status: string;
    created_at: string | null;
    patient?: { name: string; last_name?: string };
    deliveries?: Array<{ delivered_at: string | null }>;
}

interface IndexProps {
    cases: {
        data: ReportCase[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
        };
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    statusCounts: Record<string, number>;
    summary: {
        avg_days: number | null;
        delivered_count: number;
        total_cases: number;
    };
    filters: { status: string; from: string; to: string };
    statusOptions: string[];
    urls: { current: string; dashboard: string; caseShow: string };
}

export default function ProstheticsReportsIndex({
    cases,
    statusCounts,
    summary,
    filters: serverFilters,
    statusOptions,
    urls,
}: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
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
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    icon="bx-bar-chart-alt-2"
                    accent="from-violet-500 to-purple-600"
                    backHref={urls.dashboard}
                    backLabel={t('global.back')}
                />

                <div className="grid gap-4 sm:grid-cols-3">
                    <Card>
                        <p className="text-sm text-gray-500">{t('global.prosthetics_avg_turnaround')}</p>
                        <p className="text-2xl font-semibold">{summary.avg_days ?? '—'}</p>
                    </Card>
                    <Card>
                        <p className="text-sm text-gray-500">{t('global.prosthetics_delivered_cases')}</p>
                        <p className="text-2xl font-semibold">{summary.delivered_count}</p>
                    </Card>
                    <Card>
                        <p className="text-sm text-gray-500">{t('global.total')}</p>
                        <p className="text-2xl font-semibold">{summary.total_cases}</p>
                    </Card>
                </div>

                <Card>
                    <form
                        className="mb-4 flex flex-wrap items-end gap-3"
                        onSubmit={(e) => {
                            e.preventDefault();
                            applyFilters(filters);
                        }}
                    >
                        <div className="min-w-[180px]">
                            <Label htmlFor="status" value={t('global.status')} className="mb-1 text-xs" />
                            <select
                                id="status"
                                className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
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
                        <div>
                            <Label htmlFor="from" value={t('global.from')} className="mb-1 text-xs" />
                            <TextInput
                                id="from"
                                type="date"
                                sizing="sm"
                                value={filters.from}
                                onChange={(e) => setFilters((prev) => ({ ...prev, from: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="to" value={t('global.to')} className="mb-1 text-xs" />
                            <TextInput
                                id="to"
                                type="date"
                                sizing="sm"
                                value={filters.to}
                                onChange={(e) => setFilters((prev) => ({ ...prev, to: e.target.value }))}
                            />
                        </div>
                        <Button type="submit" color="blue" size="sm" disabled={processing}>
                            {t('global.filter')}
                        </Button>
                    </form>

                    <div className="mb-6 overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th className="px-3 py-2">{t('global.status')}</th>
                                    <th className="px-3 py-2">{t('global.count')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {Object.entries(statusCounts).map(([status, count]) => (
                                    <tr key={status} className="border-b dark:border-gray-700">
                                        <td className="px-3 py-2">{t(`global.prosthetics_case_status_${status}`)}</td>
                                        <td className="px-3 py-2">{count}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="mb-3 text-sm text-gray-500">{buildPaginationSummary(cases.meta, t)}</div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th className="px-3 py-2">{t('global.prosthetics_case_number')}</th>
                                    <th className="px-3 py-2">{t('global.patient_name')}</th>
                                    <th className="px-3 py-2">{t('global.status')}</th>
                                    <th className="px-3 py-2">{t('global.created_at')}</th>
                                    <th className="px-3 py-2">{t('global.prosthetics_delivered')}</th>
                                    <th className="px-3 py-2" />
                                </tr>
                            </thead>
                            <tbody>
                                {cases.data.map((item) => (
                                    <tr key={item.id} className="border-b dark:border-gray-700">
                                        <td className="px-3 py-2 font-mono">{item.case_number}</td>
                                        <td className="px-3 py-2">
                                            {item.patient
                                                ? `${item.patient.name} ${item.patient.last_name ?? ''}`.trim()
                                                : '—'}
                                        </td>
                                        <td className="px-3 py-2">
                                            {t(`global.prosthetics_case_status_${item.status}`)}
                                        </td>
                                        <td className="px-3 py-2">{item.created_at ?? '—'}</td>
                                        <td className="px-3 py-2">
                                            {item.deliveries?.[0]?.delivered_at ?? '—'}
                                        </td>
                                        <td className="px-3 py-2 text-right">
                                            <Link
                                                href={`${urls.caseShow}/${item.id}`}
                                                className="text-blue-600 hover:underline"
                                            >
                                                {t('global.show')}
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <SettingsPagination links={cases.links} className="mt-4" />
                </Card>
            </div>
        </DashboardLayout>
    );
}
