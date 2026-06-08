import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { TOOTH_CONDITIONS, selectClassName } from '../../lib/dentalChartOptions';
import { useTranslation } from '../../hooks/useTranslation';
import {
    DentalChartRegistrationHeader,
    DentalChartUrls,
    PaginatedDentalCharts,
} from '../../types/dentalChart';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface IndexDentalChartsProps {
    registration: DentalChartRegistrationHeader;
    charts: PaginatedDentalCharts;
    filters: { tooth_number: string; tooth_condition: string; per_page: string };
    urls: DentalChartUrls;
}

export default function IndexDentalCharts({ registration, charts, filters, urls }: IndexDentalChartsProps) {
    const { t } = useTranslation();
    const [localFilters, setLocalFilters] = useState(filters);

    const applyFilters = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.index, Object.fromEntries(Object.entries(localFilters).filter(([, v]) => v !== '')), {
            preserveScroll: true,
        });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.dental_chart')} />
            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.dental_chart')}
                    subtitle={`${registration.patient_name ?? '—'} · #${registration.ref_no ?? registration.id}`}
                    icon="bx-grid-alt"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.registrationShow}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            <Button as={Link} href={urls.create} size="sm" color="blue">
                                <i className="bx bx-plus me-2" />
                                {t('global.add_tooth_record')}
                            </Button>
                            <Button as={Link} href={urls.history} size="sm" color="light">
                                {t('global.history')}
                            </Button>
                            <Button as={Link} href={urls.compare} size="sm" color="light">
                                {t('global.compare_dates')}
                            </Button>
                        </div>
                    }
                />

                <Card className="shadow-sm">
                    <form onSubmit={applyFilters} className="mb-4 grid gap-4 md:grid-cols-3">
                        <div>
                            <label className="mb-2 block text-sm font-medium">{t('global.tooth_number')}</label>
                            <input
                                className={selectClassName}
                                value={localFilters.tooth_number}
                                onChange={(event) =>
                                    setLocalFilters((current) => ({ ...current, tooth_number: event.target.value }))
                                }
                                placeholder="FDI"
                            />
                        </div>
                        <div>
                            <label className="mb-2 block text-sm font-medium">{t('global.tooth_condition')}</label>
                            <select
                                className={selectClassName}
                                value={localFilters.tooth_condition}
                                onChange={(event) =>
                                    setLocalFilters((current) => ({
                                        ...current,
                                        tooth_condition: event.target.value,
                                    }))
                                }
                            >
                                <option value="">{t('global.all')}</option>
                                {TOOTH_CONDITIONS.map((condition) => (
                                    <option key={condition} value={condition}>
                                        {t(`global.${condition}`)}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="flex items-end">
                            <Button type="submit" color="blue">
                                {t('global.search')}
                            </Button>
                        </div>
                    </form>

                    {charts.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>FDI</TableHeader>
                                    <TableHeader>{t('global.condition')}</TableHeader>
                                    <TableHeader>{t('global.chart_date')}</TableHeader>
                                    <TableHeader>{t('global.gum_health')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {charts.data.map((chart) => (
                                    <TableRow key={chart.id}>
                                        <TableCell>{chart.tooth_number}</TableCell>
                                        <TableCell>
                                            <Badge color="info">{chart.tooth_condition}</Badge>
                                        </TableCell>
                                        <TableCell muted dir="ltr">
                                            {chart.chart_date ?? '—'}
                                        </TableCell>
                                        <TableCell muted>{chart.gum_health ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <Button
                                                as={Link}
                                                href={`/react/dental-charts/entry/${chart.id}/edit`}
                                                size="xs"
                                                color="warning"
                                            >
                                                <i className="bx bx-edit" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <p className="py-8 text-center text-sm text-gray-500">{t('global.no_charts_found')}</p>
                    )}

                    <div className="mt-4">
                        <SettingsPagination links={charts.links} />
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
