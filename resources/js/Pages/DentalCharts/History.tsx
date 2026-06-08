import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { selectClassName } from '../../lib/dentalChartOptions';
import { useTranslation } from '../../hooks/useTranslation';
import {
    DentalChartRecord,
    DentalChartRegistrationHeader,
    DentalChartUrls,
} from '../../types/dentalChart';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface HistoryDentalChartsProps {
    registration: DentalChartRegistrationHeader;
    chartDates: string[];
    selectedDate: string | null;
    charts: DentalChartRecord[];
    timeline: Array<{ date: string; teeth_count: number }>;
    urls: DentalChartUrls;
}

export default function HistoryDentalCharts({
    registration,
    chartDates,
    selectedDate,
    charts,
    timeline,
    urls,
}: HistoryDentalChartsProps) {
    const { t } = useTranslation();

    return (
        <DashboardLayout>
            <Head title={t('global.chart_history')} />
            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.chart_history')}
                    subtitle={`${registration.patient_name ?? '—'} · #${registration.ref_no ?? registration.id}`}
                    icon="bx-history"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.registrationShow}
                    backLabel={t('global.back')}
                    action={
                        <Button as={Link} href={urls.compare} size="sm" color="light">
                            {t('global.compare_dates')}
                        </Button>
                    }
                />

                <Card className="shadow-sm">
                    <div className="mb-4 flex flex-wrap items-end gap-3">
                        <div className="min-w-[220px]">
                            <label className="mb-2 block text-sm font-medium">{t('global.select_date')}</label>
                            <select
                                className={selectClassName}
                                value={selectedDate ?? ''}
                                onChange={(event) =>
                                    router.get(urls.history, { date: event.target.value }, { preserveScroll: true })
                                }
                            >
                                {chartDates.map((date) => (
                                    <option key={date} value={date}>
                                        {date}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    <div className="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        {timeline.map((item) => (
                            <button
                                key={item.date}
                                type="button"
                                onClick={() => router.get(urls.history, { date: item.date })}
                                className={`rounded-xl border p-4 text-start transition ${
                                    selectedDate === item.date
                                        ? 'border-blue-300 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20'
                                        : 'border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800/40'
                                }`}
                            >
                                <p className="font-semibold" dir="ltr">
                                    {item.date}
                                </p>
                                <p className="text-sm text-gray-500">
                                    {item.teeth_count} {t('global.teeth_recorded')}
                                </p>
                            </button>
                        ))}
                    </div>

                    {charts.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>FDI</TableHeader>
                                    <TableHeader>{t('global.condition')}</TableHeader>
                                    <TableHeader>{t('global.gum_health')}</TableHeader>
                                    <TableHeader>{t('global.pocket_depth')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {charts.map((chart) => (
                                    <TableRow key={chart.id}>
                                        <TableCell>FDI {chart.tooth_number}</TableCell>
                                        <TableCell>
                                            <Badge color="info">{chart.tooth_condition}</Badge>
                                        </TableCell>
                                        <TableCell muted>{chart.gum_health ?? '—'}</TableCell>
                                        <TableCell muted>{chart.pocket_depth ?? '—'}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="edit"
                                                href={`/react/dental-charts/entry/${chart.id}/edit`}
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <p className="py-8 text-center text-sm text-gray-500">{t('global.no_charts_found')}</p>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
