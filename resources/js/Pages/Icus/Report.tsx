import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import IcuNavTabs from '../../Components/Icus/IcuNavTabs';
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
import { IcuListUrls, IcuReportFilters, IcuReportItem } from '../../types/icu';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    items: IcuReportItem[];
    filters: IcuReportFilters;
    urls: IcuListUrls & { current: string; export: string };
}

function statusBadge(status: string, t: (key: string) => string) {
    if (status === 'new') {
        return <Badge color="info">{t('global.new_icus')}</Badge>;
    }
    if (status === 'approved') {
        return <Badge color="success">{t('global.approved_icus')}</Badge>;
    }
    return <Badge color="failure">{t('global.rejected_icus')}</Badge>;
}

export default function IcusReport({ items, filters, urls }: ReportProps) {
    const { t } = useTranslation();
    const [form, setForm] = useState(filters);
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.get(
            urls.current,
            {
                patient_name: form.patient_name,
                status: form.status,
                date_from: form.date_from,
                date_to: form.date_to,
                search: '1',
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }
        );
    };

    const handleReset = () => {
        const empty = { patient_name: '', status: '', date_from: '', date_to: '' };
        setForm(empty);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const handleExport = (type: 'excel' | 'pdf') => {
        if (items.length === 0) return;

        const formEl = document.createElement('form');
        formEl.method = 'POST';
        formEl.action = urls.export;
        formEl.target = '_blank';

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrf) {
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            formEl.appendChild(tokenInput);
        }

        const dataInput = document.createElement('input');
        dataInput.type = 'hidden';
        dataInput.name = 'data';
        dataInput.value = JSON.stringify(items.map((item) => item.id));
        formEl.appendChild(dataInput);

        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'type';
        typeInput.value = type;
        formEl.appendChild(typeInput);

        document.body.appendChild(formEl);
        formEl.submit();
        document.body.removeChild(formEl);
    };

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    subtitle={t('global.new_icus')}
                    icon="bx-bar-chart-alt-2"
                    accent="from-violet-600 to-purple-700"
                    backLabel={t('global.back')}
                />

                <Card>
                    <IcuNavTabs active="report" urls={urls} />
                </Card>

                <Card>
                    <h2 className="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                        <i className="bx bx-search text-violet-500" />
                        {t('global.documents.search')}
                    </h2>
                    <form onSubmit={handleSubmit} className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label htmlFor="report-patient-name">{t('global.patient_name')}</Label>
                            <TextInput
                                id="report-patient-name"
                                sizing="sm"
                                value={form.patient_name}
                                onChange={(e) => setForm((prev) => ({ ...prev, patient_name: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="report-status">{t('global.status')}</Label>
                            <Select
                                id="report-status"
                                sizing="sm"
                                value={form.status}
                                onChange={(e) => setForm((prev) => ({ ...prev, status: e.target.value }))}
                            >
                                <option value="">{t('global.select')}</option>
                                <option value="new">{t('global.new_icus')}</option>
                                <option value="approved">{t('global.approved_icus')}</option>
                                <option value="rejected">{t('global.rejected_icus')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.from')}</Label>
                            <PersianDateInput
                                value={form.date_from}
                                onChange={(date_from) => setForm((prev) => ({ ...prev, date_from }))}
                            />
                        </div>
                        <div>
                            <Label>{t('global.to')}</Label>
                            <PersianDateInput
                                value={form.date_to}
                                onChange={(date_to) => setForm((prev) => ({ ...prev, date_to }))}
                            />
                        </div>
                        <div className="flex flex-wrap gap-2 md:col-span-2 lg:col-span-4">
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                <i className="bx bx-search me-1" />
                                {t('global.documents.search')}
                            </Button>
                            <Button type="button" color="light" size="sm" disabled={processing} onClick={handleReset}>
                                {t('global.reset')}
                            </Button>
                        </div>
                    </form>
                </Card>

                <Card>
                    <div className="mb-4 flex flex-wrap gap-2">
                        <Button
                            type="button"
                            color="light"
                            size="sm"
                            disabled={items.length === 0}
                            onClick={() => handleExport('excel')}
                        >
                            <i className="bx bx-spreadsheet me-1" />
                            Excel
                        </Button>
                        <Button
                            type="button"
                            color="failure"
                            size="sm"
                            disabled={items.length === 0}
                            onClick={() => handleExport('pdf')}
                        >
                            <i className="bx bx-file me-1" />
                            PDF
                        </Button>
                    </div>

                    <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('global.number')}</TableHead>
                                    <TableHead>{t('global.patient_name')}</TableHead>
                                    <TableHead>{t('global.status')}</TableHead>
                                    <TableHead>{t('global.doctor_name')}</TableHead>
                                    <TableHead>{t('global.branch')}</TableHead>
                                    <TableHead>{t('global.date')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell>{statusBadge(item.status, t)}</TableCell>
                                        <TableCell>{item.doctor_name ?? '—'}</TableCell>
                                        <TableCell>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell dir="ltr">{item.created_at ?? '—'}</TableCell>
                                    </TableRow>
                                ))}
                                {items.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={6} className="py-10 text-center text-gray-500">
                                            {t('global.no_item_is_found')}
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
