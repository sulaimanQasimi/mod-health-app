import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult, SettingsPermissions } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface MedicineItem {
    id: number;
    name: string;
}

interface MedicineFilters {
    search: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

const defaultMedicineFilters: MedicineFilters = {
    search: '',
    sort_by: 'id',
    sort_order: 'desc',
    per_page: '15',
};

export default function IndexMedicines({
    medicines,
    filters: serverFilters,
    permissions,
    urls,
}: {
    medicines: PaginatedResult<MedicineItem>;
    filters: MedicineFilters;
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: MedicineFilters) => {
            setProcessing(true);
            router.get(
                urls.index,
                Object.fromEntries(Object.entries(next).filter(([, value]) => value !== '')),
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onFinish: () => setProcessing(false),
                },
            );
        },
        [urls.index],
    );

    const clearFilters = useCallback(() => {
        setFilters(defaultMedicineFilters);
        applyFilters(defaultMedicineFilters);
    }, [applyFilters]);

    const summaryLabel = buildPaginationSummary(medicines.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.medicines')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.medicines')}
                        subtitle={summaryLabel}
                        icon="bx-plus-medical"
                        accent="from-teal-500 to-cyan-600"
                        backLabel={t('global.back')}
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.create')}
                                </Button>
                            ) : undefined
                        }
                    />
                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) =>
                                    setFilters({ ...filters, search: event.target.value })
                                }
                                placeholder={t('global.search_by_name')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.per_page')}</Label>
                            <SearchableSelect
                                value={filters.per_page || '15'}
                                onChange={(value) => {
                                    const next = { ...filters, per_page: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                            >
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </SearchableSelect>
                        </div>
                        <div>
                            <Label>{t('global.sort_by')}</Label>
                            <SearchableSelect
                                value={filters.sort_by || 'id'}
                                onChange={(value) => {
                                    const next = { ...filters, sort_by: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                            >
                                <option value="id">{t('global.id')}</option>
                                <option value="name">{t('global.name')}</option>
                                <option value="created_at">{t('global.created_at')}</option>
                            </SearchableSelect>
                        </div>
                        <div>
                            <Label>{t('global.sort_order') || t('global.order')}</Label>
                            <SearchableSelect
                                value={filters.sort_order || 'desc'}
                                onChange={(value) => {
                                    const next = { ...filters, sort_order: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                            >
                                <option value="desc">{t('global.descending')}</option>
                                <option value="asc">{t('global.ascending')}</option>
                            </SearchableSelect>
                        </div>
                        <SettingsFilterActions
                            processing={processing}
                            showClear
                            onClear={clearFilters}
                        />
                    </form>
                    {medicines.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {medicines.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(medicines.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>{item.name}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${item.id}/edit`}
                                                permission={permissions.edit}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                disabled={deletingId === item.id}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() => {
                                                    setDeletingId(item.id);
                                                    router.delete(`${urls.destroy}/${item.id}`, {
                                                        preserveScroll: true,
                                                        onFinish: () => setDeletingId(null),
                                                    });
                                                }}
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState
                            message={t('global.no_medicines_found') || t('global.no_results_found')}
                        />
                    )}
                    <SettingsPagination links={medicines.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
