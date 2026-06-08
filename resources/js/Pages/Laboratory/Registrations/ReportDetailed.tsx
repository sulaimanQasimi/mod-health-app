import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import AppointmentPagination from '../../../Components/Appointments/AppointmentPagination';
import LaboratoryPageHeader from '../../../Components/Laboratory/LaboratoryPageHeader';
import LaboratoryPriorityBadge from '../../../Components/Laboratory/LaboratoryPriorityBadge';
import LaboratoryStatusBadge from '../../../Components/Laboratory/LaboratoryStatusBadge';
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
import {
    LaboratoryDetailedReportRow,
    SectionOption,
    SelectOption,
} from '../../../types/laboratory';

interface ReportDetailedProps {
    items: {
        data: LaboratoryDetailedReportRow[];
        links?: PaginationLink[];
        meta?: {
            from: number | null;
            to: number | null;
            total: number;
        };
    } | null;
    filters: Record<string, string>;
    filterOptions: {
        labTypes: SelectOption[];
        branches: SelectOption[];
        departments: SelectOption[];
        doctors: SelectOption[];
        users: SelectOption[];
        sections: SectionOption[];
    };
    urls: {
        report: string;
        export: string;
    };
}

export default function ReportDetailed({
    items,
    filters: serverFilters,
    filterOptions,
    urls,
}: ReportDetailedProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [showAdvanced, setShowAdvanced] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const updateFilter = (field: string, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

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

    return (
        <DashboardLayout>
            <Head title={t('global.test_registration_report_detailed')} />

            <LaboratoryPageHeader
                title={t('global.test_registration_report_detailed')}
                subtitle={t('global.reports')}
                icon="bx-spreadsheet"
                accent="from-slate-600 to-gray-800"
            />

            <Card className="mb-6 shadow-sm">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label>{t('global.from')}</Label>
                            <TextInput
                                value={filters.from ?? ''}
                                onChange={(e) => updateFilter('from', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label>{t('global.to')}</Label>
                            <TextInput
                                value={filters.to ?? ''}
                                onChange={(e) => updateFilter('to', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label>{t('global.test_type')}</Label>
                            <Select
                                value={filters.test_type ?? ''}
                                onChange={(e) => updateFilter('test_type', e.target.value)}
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
                            <Label>{t('global.status')}</Label>
                            <Select
                                value={filters.status ?? ''}
                                onChange={(e) => updateFilter('status', e.target.value)}
                            >
                                <option value="">{t('global.all')}</option>
                                <option value="pending">{t('global.pending')}</option>
                                <option value="in_progress">{t('global.in_progress')}</option>
                                <option value="completed">{t('global.completed')}</option>
                                <option value="cancelled">{t('global.cancelled')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.patient_id')}</Label>
                            <TextInput
                                value={filters.patient_id ?? ''}
                                onChange={(e) => updateFilter('patient_id', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label>{t('global.doctor')}</Label>
                            <Select
                                value={filters.doctor_id ?? ''}
                                onChange={(e) => updateFilter('doctor_id', e.target.value)}
                            >
                                <option value="">{t('global.all')}</option>
                                {filterOptions.doctors.map((doctor) => (
                                    <option key={doctor.id} value={doctor.id}>
                                        {doctor.name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.per_page')}</Label>
                            <Select
                                value={filters.per_page ?? '15'}
                                onChange={(e) => updateFilter('per_page', e.target.value)}
                            >
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">{t('global.all')}</option>
                            </Select>
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={() => setShowAdvanced((v) => !v)}
                        className="text-sm font-medium text-blue-600 hover:underline"
                    >
                        {showAdvanced ? t('global.hide') || 'Hide' : t('global.advanced_filters')}
                    </button>

                    {showAdvanced && (
                        <div className="grid grid-cols-1 gap-4 rounded-lg border border-gray-200 p-4 md:grid-cols-2 lg:grid-cols-4 dark:border-gray-700">
                            <div>
                                <Label>{t('global.branch')}</Label>
                                <Select
                                    value={filters.branch_id ?? ''}
                                    onChange={(e) => updateFilter('branch_id', e.target.value)}
                                >
                                    <option value="">{t('global.all')}</option>
                                    {filterOptions.branches.map((branch) => (
                                        <option key={branch.id} value={branch.id}>
                                            {branch.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.department')}</Label>
                                <Select
                                    value={filters.department_id ?? ''}
                                    onChange={(e) => updateFilter('department_id', e.target.value)}
                                >
                                    <option value="">{t('global.all')}</option>
                                    {filterOptions.departments.map((dept) => (
                                        <option key={dept.id} value={dept.id}>
                                            {dept.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.assigned_to')}</Label>
                                <Select
                                    value={filters.assigned_to ?? ''}
                                    onChange={(e) => updateFilter('assigned_to', e.target.value)}
                                >
                                    <option value="">{t('global.all')}</option>
                                    {filterOptions.users.map((user) => (
                                        <option key={user.id} value={user.id}>
                                            {user.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.notes')}</Label>
                                <TextInput
                                    value={filters.notes ?? ''}
                                    onChange={(e) => updateFilter('notes', e.target.value)}
                                />
                            </div>
                        </div>
                    )}

                    <Button type="submit" color="blue" disabled={processing}>
                        {t('global.search')}
                    </Button>
                </form>
            </Card>

            {items && (
                <Card className="shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('global.reference_number')}</TableHead>
                                    <TableHead>{t('global.date')}</TableHead>
                                    <TableHead>{t('global.patient')}</TableHead>
                                    <TableHead>{t('global.test_type')}</TableHead>
                                    <TableHead>{t('global.status')}</TableHead>
                                    <TableHead>{t('global.priority')}</TableHead>
                                    <TableHead>{t('global.doctor')}</TableHead>
                                    <TableHead>{t('global.completed_by')}</TableHead>
                                    <TableHead>{t('global.assigned_to')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={9} className="text-center text-gray-500">
                                            {t('global.no_item_is_found')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    items.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell className="font-mono text-sm">{row.ref_no}</TableCell>
                                            <TableCell>{row.registration_date ?? '—'}</TableCell>
                                            <TableCell>{row.patient_name ?? '—'}</TableCell>
                                            <TableCell>{row.lab_type_name ?? '—'}</TableCell>
                                            <TableCell>
                                                <LaboratoryStatusBadge status={row.status} />
                                            </TableCell>
                                            <TableCell>
                                                <LaboratoryPriorityBadge priority={row.priority} />
                                            </TableCell>
                                            <TableCell>{row.doctor_name ?? '—'}</TableCell>
                                            <TableCell>{row.completed_by_name ?? '—'}</TableCell>
                                            <TableCell>{row.assigned_to_name ?? '—'}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    {items.links && items.meta && (
                        <AppointmentPagination links={items.links} meta={items.meta} t={t} />
                    )}
                </Card>
            )}
        </DashboardLayout>
    );
}
