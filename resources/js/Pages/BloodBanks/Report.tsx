import { Head, router } from '@inertiajs/react';
import { Badge, Button, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import { BLOOD_BANK_PANEL_ICON_CLASS, bloodGroupLabel, bloodRhLabel, bloodStatusBadgeColor } from '../../Components/BloodBanks/bloodBankUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    BloodBankListUrls,
    BloodReportFilterOptions,
    BloodReportFilters,
    BloodReportItem,
} from '../../types/bloodBank';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportProps {
    items: BloodReportItem[];
    filters: BloodReportFilters;
    filterOptions: BloodReportFilterOptions;
    urls: BloodBankListUrls & { current: string; export: string };
}

export default function BloodBanksReport({ items, filters, filterOptions, urls }: ReportProps) {
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
            },
        );
    };

    const handleReset = () => {
        const empty: BloodReportFilters = {
            patient_name: '',
            status: '',
            group: '',
            rh: '',
            department_id: '',
            from: '',
            to: '',
        };
        setForm(empty);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const exportUrl = () => {
        const params = new URLSearchParams(
            Object.fromEntries(
                Object.entries(form).filter(([, value]) => value !== ''),
            ),
        );
        return `${urls.export}?${params.toString()}`;
    };

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.reports')}
                    subtitle={t('global.blood_bank')}
                    icon="bx-bar-chart-alt-2"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.dashboard}
                    backLabel={t('global.back')}
                    action={
                        <SettingsPageActions>
                            <a
                                href={exportUrl()}
                                className="inline-flex items-center gap-2 rounded-xl border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50 dark:border-rose-900/40 dark:text-rose-300"
                            >
                                <i className="bx bx-download" />
                                {t('global.export')}
                            </a>
                        </SettingsPageActions>
                    }
                />

                <BloodBankNavTabs active="report" urls={urls} />

                <IcuPanel
                    variant="filter"
                    title={t('global.advanced_filters')}
                    icon="bx-filter-alt"
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                >
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
                                <Label className="mb-2 block">{t('global.status')}</Label>
                                <SearchableSelect
                                    value={form.status}
                                    onChange={(value) => setForm({ ...form, status: value })}
                                    options={filterOptions.statuses.map((status) => ({
                                        value: status,
                                        label: status,
                                    }))}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">{t('global.blood_group')}</Label>
                                <SearchableSelect
                                    value={form.group}
                                    onChange={(value) => setForm({ ...form, group: value })}
                                    options={filterOptions.bloodGroups.map((group) => ({
                                        value: group,
                                        label: group,
                                    }))}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">{t('global.rh')}</Label>
                                <SearchableSelect
                                    value={form.rh}
                                    onChange={(value) => setForm({ ...form, rh: value })}
                                    options={[
                                        { value: '+', label: 'Rh+' },
                                        { value: '-', label: 'Rh−' },
                                    ]}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">{t('global.requested_department')}</Label>
                                <SearchableSelect
                                    value={form.department_id}
                                    onChange={(value) => setForm({ ...form, department_id: value })}
                                    options={filterOptions.departments.map((dept) => ({
                                        value: String(dept.id),
                                        label: dept.name,
                                    }))}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.date_from')}</Label>
                                <PersianDateInput
                                    value={form.from}
                                    onChange={(value) => setForm({ ...form, from: value })}
                                />
                            </div>
                            <div>
                                <Label>{t('global.date_to')}</Label>
                                <PersianDateInput
                                    value={form.to}
                                    onChange={(value) => setForm({ ...form, to: value })}
                                />
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" color="failure" disabled={processing}>
                                {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-search me-2" />}
                                {t('global.search')}
                            </Button>
                            <Button type="button" color="light" onClick={handleReset} disabled={processing}>
                                {t('global.reset')}
                            </Button>
                        </div>
                    </form>
                </IcuPanel>

                <IcuPanel
                    variant="table"
                    title={t('global.reports')}
                    icon="bx-bar-chart-alt-2"
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                >
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.requested_department')}</TableHeader>
                                <TableHeader>{t('global.branch')}</TableHeader>
                                <TableHeader>{t('global.blood_group')}</TableHeader>
                                <TableHeader>{t('global.rh')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {items.length === 0 ? (
                                <TableEmpty colSpan={7} message={t('global.no_records_found')} />
                            ) : (
                                items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell className="font-medium">{item.patient_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.branch_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color="failure" className="w-fit font-normal">
                                                {bloodGroupLabel(item.group)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{bloodRhLabel(item.rh)}</TableCell>
                                        <TableCell>
                                            <Badge color={bloodStatusBadgeColor(item.status)} className="w-fit font-normal">
                                                {item.status}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
