import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
import LaboratoryPageHeader from '../../../Components/Laboratory/LaboratoryPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../../Components/ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { PaginationLink } from '../../../types/appointment';
import { LaboratoryReportRow, SelectOption } from '../../../types/laboratory';

interface ReportProps {
    items: {
        data: LaboratoryReportRow[];
        links?: PaginationLink[];
        meta?: {
            from: number | null;
            to: number | null;
            total: number;
        };
    } | null;
    filters: {
        from: string;
        to: string;
        test_type: string;
        patient_id: string;
        per_page: string;
    };
    filterOptions: { labTypes: SelectOption[] };
    urls: {
        report: string;
        export: string;
    };
}

export default function Report({ items, filters: serverFilters, filterOptions, urls }: ReportProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.get(
            urls.report,
            Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')),
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    const totalRegistrations = items?.data?.reduce((sum, row) => sum + row.total_count, 0) ?? 0;

    return (
        <DashboardLayout>
            <Head title={t('global.test_registration_report')} />

            <LaboratoryPageHeader
                title={t('global.test_registration_report')}
                subtitle={
                    t('global.view_and_export_test_registration_statistics') ||
                    t('global.reports')
                }
                icon="bx-bar-chart-alt-2"
                accent="from-indigo-500 to-blue-600"
            />

            <Card className="mb-6 shadow-sm">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label>{t('global.from')}</Label>
                            <TextInput
                                value={filters.from}
                                onChange={(e) => setFilters({ ...filters, from: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.to')}</Label>
                            <TextInput
                                value={filters.to}
                                onChange={(e) => setFilters({ ...filters, to: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.test_type')}</Label>
                            <Select
                                value={filters.test_type}
                                onChange={(e) => setFilters({ ...filters, test_type: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {filterOptions.labTypes.map((type) => (
                                    <option key={type.id} value={type.id}>
                                        {type.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.patient_id')}</Label>
                            <TextInput
                                value={filters.patient_id}
                                onChange={(e) => setFilters({ ...filters, patient_id: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.per_page')}</Label>
                            <Select
                                value={filters.per_page}
                                onChange={(e) => setFilters({ ...filters, per_page: e.target.value })}
                            >
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">{t('global.all')}</option>
                            </Select>
                        </div>
                    </div>
                    <Button type="submit" color="blue" disabled={processing}>
                        {t('global.search')}
                    </Button>
                </form>
            </Card>

            {items && (
                <>
                    <Card className="mb-4 shadow-sm">
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {items.data.length} {t('global.test_type')} · {totalRegistrations}{' '}
                                {t('global.registrations') || 'registrations'}
                            </p>
                            <form method="POST" action={urls.export}>
                                <input type="hidden" name="_token" value="" />
                                <Button type="button" color="light" size="sm" disabled>
                                    <i className="bx bx-export me-1" />
                                    {t('global.export')}
                                </Button>
                            </form>
                        </div>
                    </Card>

                    <Card className="shadow-sm">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>#</TableHead>
                                        <TableHead>{t('global.test_type')}</TableHead>
                                        <TableHead className="text-end">{t('global.total')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {items.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={3} className="text-center text-gray-500">
                                                {t('global.no_item_is_found')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        items.data.map((row, index) => (
                                            <TableRow key={row.lab_type_id}>
                                                <TableCell>{index + 1}</TableCell>
                                                <TableCell className="font-medium">
                                                    {row.lab_type_name}
                                                </TableCell>
                                                <TableCell className="text-end">
                                                    <span className="inline-flex min-w-[2.5rem] justify-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-bold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                                        {row.total_count}
                                                    </span>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </Card>

                    {items.links && items.meta && (
                        <AppointmentPagination links={items.links} meta={items.meta} t={t} />
                    )}
                </>
            )}
        </DashboardLayout>
    );
}
