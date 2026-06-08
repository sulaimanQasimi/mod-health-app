import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
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
import { selectClassName } from '../../lib/dentalChartOptions';
import { useTranslation } from '../../hooks/useTranslation';
import { DentalChartRecord, DentalChartRegistrationHeader, DentalChartUrls } from '../../types/dentalChart';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface CompareDentalChartsProps {
    registration: DentalChartRegistrationHeader;
    chartDates: string[];
    date1: string | null;
    date2: string | null;
    comparison: Array<{
        tooth_number: number;
        date1: DentalChartRecord | null;
        date2: DentalChartRecord | null;
        changed: boolean;
    }>;
    urls: DentalChartUrls;
}

export default function CompareDentalCharts({
    registration,
    chartDates,
    date1,
    date2,
    comparison,
    urls,
}: CompareDentalChartsProps) {
    const { t } = useTranslation();
    const [localDate1, setLocalDate1] = useState(date1 ?? '');
    const [localDate2, setLocalDate2] = useState(date2 ?? '');

    const applyCompare = () => {
        router.get(urls.compare, { date1: localDate1, date2: localDate2 }, { preserveScroll: true });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.compare_dates')} />
            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.compare_dates')}
                    subtitle={`${registration.patient_name ?? '—'} · #${registration.ref_no ?? registration.id}`}
                    icon="bx-git-compare"
                    accent="from-indigo-500 to-blue-600"
                    backHref={urls.history}
                    backLabel={t('global.back')}
                />

                <Card className="shadow-sm">
                    <div className="mb-4 grid gap-4 md:grid-cols-3">
                        <div>
                            <label className="mb-2 block text-sm font-medium">{t('global.date')} 1</label>
                            <select
                                className={selectClassName}
                                value={localDate1}
                                onChange={(event) => setLocalDate1(event.target.value)}
                            >
                                {chartDates.map((date) => (
                                    <option key={`d1-${date}`} value={date}>
                                        {date}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="mb-2 block text-sm font-medium">{t('global.date')} 2</label>
                            <select
                                className={selectClassName}
                                value={localDate2}
                                onChange={(event) => setLocalDate2(event.target.value)}
                            >
                                {chartDates.map((date) => (
                                    <option key={`d2-${date}`} value={date}>
                                        {date}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="flex items-end">
                            <Button color="blue" onClick={applyCompare}>
                                {t('global.compare_dates')}
                            </Button>
                        </div>
                    </div>

                    {comparison.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>FDI</TableHeader>
                                    <TableHeader>{date1 ?? '—'}</TableHeader>
                                    <TableHeader>{date2 ?? '—'}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {comparison.map((row) => (
                                    <TableRow key={row.tooth_number}>
                                        <TableCell>FDI {row.tooth_number}</TableCell>
                                        <TableCell>{row.date1?.tooth_condition ?? '—'}</TableCell>
                                        <TableCell>{row.date2?.tooth_condition ?? '—'}</TableCell>
                                        <TableCell>
                                            {row.changed ? (
                                                <Badge color="warning">{t('global.yes')}</Badge>
                                            ) : (
                                                <Badge color="success">{t('global.no')}</Badge>
                                            )}
                                        </TableCell>
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
