import { Head, Link, router, usePage } from '@inertiajs/react';
import { Badge, Button, Card, Checkbox, Dropdown, DropdownItem, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    PaginatedPrescriptions,
    PrescriptionIndexFilters,
    PrescriptionIndexPermissions,
    PrescriptionIndexUrls,
} from '../../types/prescription';
import { buildPaginationSummary } from '../../utils/pagination';
import { settingsActionClasses, SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import { OptionItem } from '../../types/settings';

interface IndexPrescriptionsProps {
    mode: 'undelivered' | 'delivered';
    prescriptions: PaginatedPrescriptions;
    filters: PrescriptionIndexFilters;
    filterOptions: { doctors: OptionItem[] };
    permissions: PrescriptionIndexPermissions;
    urls: PrescriptionIndexUrls;
}

const EMPTY_FILTERS: PrescriptionIndexFilters = {
    patient_name: '',
    card_number: '',
    father_name: '',
    patient_id: '',
    token_filter: '',
    doctor_id: '',
    status: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    per_page: '10',
};

function cleanFilters(filters: PrescriptionIndexFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function IndexPrescriptions({
    mode,
    prescriptions,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: IndexPrescriptionsProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [selectedIds, setSelectedIds] = useState<number[]>([]);
    const [bulkProcessing, setBulkProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);
    useEffect(() => setSelectedIds([]), [prescriptions.meta.current_page, mode]);

    const isDelivered = mode === 'delivered';
    const summaryLabel = buildPaginationSummary(prescriptions.meta, t);

    const applyFilters = useCallback(
        (next: PrescriptionIndexFilters) => {
            setProcessing(true);
            router.get(urls.current, cleanFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current],
    );

    const toggleSort = (field: string) => {
        const next: PrescriptionIndexFilters = {
            ...filters,
            sort_by: field,
            sort_order: filters.sort_by === field && filters.sort_order === 'asc' ? 'desc' : 'asc',
        };
        setFilters(next);
        applyFilters(next);
    };

    const allSelected = useMemo(
        () => prescriptions.data.length > 0 && selectedIds.length === prescriptions.data.length,
        [prescriptions.data.length, selectedIds.length],
    );

    const toggleSelectAll = () => {
        setSelectedIds(allSelected ? [] : prescriptions.data.map((item) => item.id));
    };

    const handleBulkStatus = (isCompleted: boolean) => {
        if (selectedIds.length === 0 || !permissions.edit) return;
        if (!window.confirm(t('global.confirm_bulk_action'))) return;

        setBulkProcessing(true);
        router.post(
            urls.bulkUpdateStatus,
            { prescription_ids: selectedIds, is_completed: isCompleted },
            {
                preserveScroll: true,
                onFinish: () => {
                    setBulkProcessing(false);
                    setSelectedIds([]);
                },
            },
        );
    };

    const handleBulkDelete = () => {
        if (selectedIds.length === 0 || !permissions.delete) return;
        if (!window.confirm(t('global.confirm_bulk_delete'))) return;

        setBulkProcessing(true);
        router.post(
            urls.bulkDelete,
            { prescription_ids: selectedIds },
            {
                preserveScroll: true,
                onFinish: () => {
                    setBulkProcessing(false);
                    setSelectedIds([]);
                },
            },
        );
    };

    const handleBulkPrint = () => {
        selectedIds.forEach((id) => {
            window.open(`${urls.thermalReceipt}/${id}`, '_blank');
        });
    };

    const handleExport = async (format: 'excel' | 'pdf') => {
        if (!permissions.export) return;

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('format', format);
        Object.entries(cleanFilters(filters)).forEach(([key, value]) => formData.append(key, value));
        selectedIds.forEach((id) => formData.append('selected[]', String(id)));

        const response = await fetch(urls.export, { method: 'POST', body: formData, credentials: 'same-origin' });
        if (!response.ok) return;

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `prescriptions.${format === 'pdf' ? 'pdf' : 'xlsx'}`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    };

    const sortIcon = (field: string) => {
        if (filters.sort_by !== field) return 'bx-sort';
        return filters.sort_order === 'asc' ? 'bx-sort-up' : 'bx-sort-down';
    };

    return (
        <DashboardLayout>
            <Head title={isDelivered ? t('global.delivered_prescriptions') : t('global.undelivered_prescriptions')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={isDelivered ? t('global.delivered_prescriptions') : t('global.new_prescriptions')}
                        subtitle={summaryLabel}
                        icon="bx-receipt"
                        accent="from-emerald-500 to-teal-600"
                        backLabel={t('global.back')}
                        action={
                            <div className="flex flex-wrap gap-2">
                                <Button color="light" as={Link} href={urls.scanCode}>
                                    <i className="bx bx-qr-scan me-2 text-lg" />
                                    {t('global.scan_prescription')}
                                </Button>
                                {!isDelivered && (
                                    <Button color="light" as={Link} href={urls.delivered}>
                                        {t('global.delivered_prescriptions')}
                                    </Button>
                                )}
                                {isDelivered && (
                                    <Button color="light" as={Link} href={urls.index}>
                                        {t('global.undelivered_prescriptions')}
                                    </Button>
                                )}
                            </div>
                        }
                    />

                    <form
                        onSubmit={(e: FormEvent) => {
                            e.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <Label>{t('global.patient_name')}</Label>
                            <TextInput
                                value={filters.patient_name}
                                onChange={(e) => setFilters({ ...filters, patient_name: e.target.value })}
                                placeholder={t('global.search_by_patient_name')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.card_number')}</Label>
                            <TextInput
                                value={filters.card_number}
                                onChange={(e) => setFilters({ ...filters, card_number: e.target.value })}
                                placeholder={t('global.search_by_card_number')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.father_name')}</Label>
                            <TextInput
                                value={filters.father_name}
                                onChange={(e) => setFilters({ ...filters, father_name: e.target.value })}
                                placeholder={t('global.search_by_father_name')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.patient_id')}</Label>
                            <TextInput
                                value={filters.patient_id}
                                onChange={(e) => setFilters({ ...filters, patient_id: e.target.value })}
                                placeholder={t('global.search_by_patient_id')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.token_id')}</Label>
                            <TextInput
                                value={filters.token_filter}
                                onChange={(e) => setFilters({ ...filters, token_filter: e.target.value })}
                                placeholder={t('global.search_by_token_id')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.doctor_name')}</Label>
                            <SearchableSelect
                                value={filters.doctor_id}
                                onChange={(value) => setFilters({ ...filters, doctor_id: value })}
                                options={(filterOptions?.doctors ?? []).map((doctor) => ({
                                    value: String(doctor.id),
                                    label: doctor.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        {!isDelivered && (
                            <div>
                                <Label>{t('global.status')}</Label>
                                <SearchableSelect
                                    value={filters.status}
                                    onChange={(value) => setFilters({ ...filters, status: value })}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="0">{t('global.not_delivered')}</option>
                                    <option value="1">{t('global.delivered')}</option>
                                </SearchableSelect>
                            </div>
                        )}
                        <div className="xl:col-span-4">
                            <SettingsFilterActions
                                processing={processing}
                                showClear
                                onClear={() => {
                                    const next = { ...EMPTY_FILTERS, per_page: filters.per_page };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                            />
                        </div>
                    </form>

                    <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div className="flex flex-wrap gap-2">
                            {permissions.export && (
                                <Dropdown
                                    label={
                                        <span className="inline-flex items-center gap-2">
                                            <i className="bx bx-download text-lg" />
                                            {t('global.export')}
                                        </span>
                                    }
                                    dismissOnClick
                                >
                                    <DropdownItem onClick={() => handleExport('excel')}>
                                        {t('global.export_excel')}
                                    </DropdownItem>
                                    <DropdownItem onClick={() => handleExport('pdf')}>
                                        {t('global.export_pdf')}
                                    </DropdownItem>
                                </Dropdown>
                            )}
                            {selectedIds.length > 0 && permissions.edit && (
                                <Dropdown
                                    label={`${t('global.bulk_actions')} (${selectedIds.length})`}
                                    dismissOnClick
                                >
                                    <DropdownItem onClick={() => handleBulkStatus(true)}>
                                        {t('global.mark_as_delivered')}
                                    </DropdownItem>
                                    <DropdownItem onClick={() => handleBulkStatus(false)}>
                                        {t('global.mark_as_not_delivered')}
                                    </DropdownItem>
                                    <DropdownItem onClick={handleBulkPrint}>{t('global.bulk_print')}</DropdownItem>
                                    {permissions.delete && (
                                        <DropdownItem onClick={handleBulkDelete}>
                                            <span className="text-red-600">{t('global.bulk_delete')}</span>
                                        </DropdownItem>
                                    )}
                                </Dropdown>
                            )}
                        </div>
                        {bulkProcessing && <Spinner size="sm" />}
                    </div>

                    {prescriptions.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>
                                        <Checkbox checked={allSelected} onChange={toggleSelectAll} />
                                    </TableHeader>
                                    <TableHeader>
                                        <button type="button" className="inline-flex items-center gap-1" onClick={() => toggleSort('created_at')}>
                                            {t('global.number')}
                                            <i className={`bx ${sortIcon('created_at')}`} />
                                        </button>
                                    </TableHeader>
                                    <TableHeader>{t('global.patient_id')}</TableHeader>
                                    <TableHeader>{t('global.card_number')}</TableHeader>
                                    <TableHeader>
                                        <button type="button" className="inline-flex items-center gap-1 font-semibold text-emerald-700" onClick={() => toggleSort('patient_name')}>
                                            {t('global.patient_name')}
                                            <i className={`bx ${sortIcon('patient_name')}`} />
                                        </button>
                                    </TableHeader>
                                    <TableHeader>{t('global.father_name')}</TableHeader>
                                    <TableHeader>{t('global.token_id')}</TableHeader>
                                    <TableHeader>
                                        <button type="button" className="inline-flex items-center gap-1" onClick={() => toggleSort('doctor_name')}>
                                            {t('global.doctor_name')}
                                            <i className={`bx ${sortIcon('doctor_name')}`} />
                                        </button>
                                    </TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader>
                                        <button type="button" className="inline-flex items-center gap-1" onClick={() => toggleSort('is_completed')}>
                                            {t('global.status')}
                                            <i className={`bx ${sortIcon('is_completed')}`} />
                                        </button>
                                    </TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {prescriptions.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>
                                            <Checkbox
                                                checked={selectedIds.includes(item.id)}
                                                onChange={() =>
                                                    setSelectedIds((current) =>
                                                        current.includes(item.id)
                                                            ? current.filter((id) => id !== item.id)
                                                            : [...current, item.id],
                                                    )
                                                }
                                            />
                                        </TableCell>
                                        <TableCell>{(prescriptions.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>{item.patient_id}</TableCell>
                                        <TableCell>
                                            <Badge color="gray">{item.card_number ?? '—'}</Badge>
                                        </TableCell>
                                        <TableCell className="font-semibold text-emerald-700">{item.patient_name}</TableCell>
                                        <TableCell muted>{item.father_name ?? '—'}</TableCell>
                                        <TableCell muted>
                                            {item.token_number ? (
                                                <Badge color="info">{item.token_number}</Badge>
                                            ) : (
                                                '—'
                                            )}
                                        </TableCell>
                                        <TableCell muted>{item.doctor_name}</TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={item.is_completed ? 'success' : 'warning'}>
                                                {item.is_completed ? t('global.delivered') : t('global.not_delivered')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                <Link
                                                    href={`${urls.show}/${item.id}`}
                                                    className={settingsActionClasses.view}
                                                >
                                                    <i className="bx bx-show-alt text-lg" />
                                                </Link>
                                                <a
                                                    href={`${urls.thermalReceipt}/${item.id}`}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className={settingsActionClasses.edit}
                                                    title={t('global.thermal_print')}
                                                >
                                                    <i className="bx bx-printer text-lg" />
                                                </a>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_prescriptions_found')} />
                    )}
                    <SettingsPagination links={prescriptions.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
