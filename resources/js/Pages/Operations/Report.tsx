import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import OperationNavTabs from '../../Components/Operations/OperationNavTabs';
import {
    operationApprovalLabel,
    operationReservedLabel,
    OPERATION_APPROVE_BTN_CLASS,
} from '../../Components/Operations/operationUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    OperationListUrls,
    OperationReportFilters,
    OperationReportItem,
    SelectOption,
} from '../../types/operation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    items: OperationReportItem[];
    filters: OperationReportFilters;
    filterOptions: {
        operationTypes: SelectOption[];
        surgeons: SelectOption[];
    };
    urls: OperationListUrls & { current: string; export: string };
}

export default function OperationsReport({ items, filters, filterOptions, urls }: ReportProps) {
    const { t } = useTranslation();
    const [form, setForm] = useState(filters);
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.get(
            urls.current,
            { ...form, search: '1' },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }
        );
    };

    const handleReset = () => {
        const empty: OperationReportFilters = {
            patient_name: '',
            surgeon_id: '',
            operation_status: '',
            operation_approval: '',
            reserve_status: '',
            operation_type_id: '',
            date_from: '',
            date_to: '',
        };
        setForm(empty);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    subtitle={t('global.operations')}
                    icon="bx-bar-chart-alt-2"
                    accent="from-amber-600 to-orange-700"
                    backLabel={t('global.back')}
                />

                <OperationNavTabs active="report" urls={urls} />

                <Card>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <Label>{t('global.patient_name')}</Label>
                                <TextInput
                                    value={form.patient_name}
                                    onChange={(e) => setForm({ ...form, patient_name: e.target.value })}
                                />
                            </div>
                            <div>
                                <Label>{t('global.operation_surgion')}</Label>
                                <Select
                                    value={form.surgeon_id}
                                    onChange={(e) => setForm({ ...form, surgeon_id: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    {filterOptions.surgeons.map((surgeon) => (
                                        <option key={surgeon.id} value={surgeon.id}>
                                            {surgeon.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.operation_type')}</Label>
                                <Select
                                    value={form.operation_type_id}
                                    onChange={(e) => setForm({ ...form, operation_type_id: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    {filterOptions.operationTypes.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.status')}</Label>
                                <Select
                                    value={form.operation_status}
                                    onChange={(e) => setForm({ ...form, operation_status: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="0">{t('global.pending')}</option>
                                    <option value="1">{t('global.completed')}</option>
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.operation_approval')}</Label>
                                <Select
                                    value={form.operation_approval}
                                    onChange={(e) => setForm({ ...form, operation_approval: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="0">{t('global.operation_not_approved')}</option>
                                    <option value="1">{t('global.approved')}</option>
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.reserve_operation')}</Label>
                                <Select
                                    value={form.reserve_status}
                                    onChange={(e) => setForm({ ...form, reserve_status: e.target.value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="0">{t('global.unreserved')}</option>
                                    <option value="1">{t('global.reserved')}</option>
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.date_from')}</Label>
                                <PersianDateInput
                                    value={form.date_from}
                                    onChange={(value) => setForm({ ...form, date_from: value })}
                                />
                            </div>
                            <div>
                                <Label>{t('global.date_to')}</Label>
                                <PersianDateInput
                                    value={form.date_to}
                                    onChange={(value) => setForm({ ...form, date_to: value })}
                                />
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <button type="submit" className={OPERATION_APPROVE_BTN_CLASS} disabled={processing}>
                                <i className="bx bx-search" />
                                {t('global.search')}
                            </button>
                            <Button type="button" color="light" onClick={handleReset} disabled={processing}>
                                {t('global.reset')}
                            </Button>
                        </div>
                    </form>
                </Card>

                <Card>
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.operation_surgion')}</TableHeader>
                                <TableHeader>{t('global.operation_type')}</TableHeader>
                                <TableHeader>{t('global.date')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {items.map((item, index) => (
                                <TableRow key={item.id}>
                                    <TableCell>{index + 1}</TableCell>
                                    <TableCell>{item.patient_name ?? '—'}</TableCell>
                                    <TableCell muted>{item.surgion_name ?? '—'}</TableCell>
                                    <TableCell muted>{item.operation_type_name ?? '—'}</TableCell>
                                    <TableCell muted dir="ltr">
                                        {[item.date, item.time].filter(Boolean).join(' ') || '—'}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex flex-wrap gap-1">
                                            <Badge color={item.is_operation_done ? 'info' : 'warning'}>
                                                {item.is_operation_done ? t('global.completed') : t('global.pending')}
                                            </Badge>
                                            <Badge color={item.is_operation_approved ? 'success' : 'failure'}>
                                                {operationApprovalLabel(item.is_operation_approved, t)}
                                            </Badge>
                                            {item.is_reserved && (
                                                <Badge color="purple">
                                                    {operationReservedLabel(item.is_reserved, t)}
                                                </Badge>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {items.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="py-8 text-center text-gray-500">
                                        {t('global.no_records_found')}
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </Card>
            </div>
        </DashboardLayout>
    );
}
