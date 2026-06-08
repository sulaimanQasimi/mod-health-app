import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface FulfillmentListItem {
    id: number;
    medicine_name: string | null;
    unit_type: string | null;
    amount: string | null;
    form_no: string | null;
    date: string | null;
    pharmacy_name: string | null;
    user_name: string | null;
    created_by_name: string | null;
    created_at: string | null;
}

interface FulfillmentFilters {
    search: string;
    medicine_id: string;
    pharmacy_id: string;
    date_from: string;
    date_to: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

interface FulfillmentPermissions {
    view: boolean;
    create: boolean;
    edit: boolean;
    delete: boolean;
}

const EMPTY_FILTERS: FulfillmentFilters = {
    search: '',
    medicine_id: '',
    pharmacy_id: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    per_page: '15',
};

export default function IndexPharmacyFulfillments({
    fulfillments,
    filters: serverFilters,
    filterOptions,
    userPharmacies,
    permissions,
    urls,
}: {
    fulfillments: PaginatedResult<FulfillmentListItem>;
    filters: FulfillmentFilters;
    filterOptions: { pharmacies: OptionItem[]; medicines: OptionItem[] };
    userPharmacies?: OptionItem[];
    permissions: FulfillmentPermissions;
    urls: {
        index: string;
        create: string;
        show: string;
        edit: string;
        destroy: string;
        stock: string;
    };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: FulfillmentFilters) => {
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

    const summaryLabel = buildPaginationSummary(fulfillments.meta, t);
    const pharmacySubtitle = useMemo(() => {
        if (!userPharmacies?.length) return undefined;
        return userPharmacies.map((pharmacy) => pharmacy.name).join(', ');
    }, [userPharmacies]);

    const showPharmacyFilter = (filterOptions?.pharmacies ?? []).length > 0;

    return (
        <DashboardLayout>
            <Head title={t('global.pharmacy_fulfillments')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.pharmacy_fulfillments')}
                        subtitle={pharmacySubtitle ?? summaryLabel}
                        icon="bx-list-check"
                        accent="from-teal-500 to-cyan-600"
                        backLabel={t('global.back')}
                        action={
                            <div className="flex flex-wrap gap-2">
                                <Button color="light" as={Link} href={urls.stock}>
                                    <i className="bx bx-box me-2 text-lg" />
                                    {t('global.stock')}
                                </Button>
                                {permissions.create && (
                                    <Button color="blue" as={Link} href={urls.create}>
                                        <i className="bx bx-plus me-2 text-lg" />
                                        {t('global.create')}
                                    </Button>
                                )}
                            </div>
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
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                placeholder={t('global.search_by_medicine_form_no')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.medicine')}</Label>
                            <SearchableSelect
                                value={filters.medicine_id}
                                onChange={(value) => setFilters({ ...filters, medicine_id: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    ...(filterOptions?.medicines ?? []).map((medicine) => ({
                                        value: String(medicine.id),
                                        label: medicine.name,
                                    })),
                                ]}
                                placeholder={t('global.all')}
                            />
                        </div>
                        {showPharmacyFilter && (
                            <div>
                                <Label>{t('global.pharmacy')}</Label>
                                <SearchableSelect
                                    value={filters.pharmacy_id}
                                    onChange={(value) => setFilters({ ...filters, pharmacy_id: value })}
                                    options={[
                                        { value: '', label: t('global.all') },
                                        ...(filterOptions?.pharmacies ?? []).map((pharmacy) => ({
                                            value: String(pharmacy.id),
                                            label: pharmacy.name,
                                        })),
                                    ]}
                                    placeholder={t('global.all')}
                                />
                            </div>
                        )}
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <TextInput
                                value={filters.date_from}
                                onChange={(event) => setFilters({ ...filters, date_from: event.target.value })}
                                placeholder={t('global.date_from')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <TextInput
                                value={filters.date_to}
                                onChange={(event) => setFilters({ ...filters, date_to: event.target.value })}
                                placeholder={t('global.date_to')}
                            />
                        </div>
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

                    {fulfillments.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.medicine')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy')}</TableHeader>
                                    <TableHeader>{t('global.unit_type')}</TableHeader>
                                    <TableHeader>{t('global.amount')}</TableHeader>
                                    <TableHeader>{t('global.form_no')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {fulfillments.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(fulfillments.meta.from ?? 1) + index}</TableCell>
                                        <TableCell className="font-medium">{item.medicine_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.pharmacy_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.unit_type ?? '—'}</TableCell>
                                        <TableCell>{item.amount ?? '—'}</TableCell>
                                        <TableCell muted>{item.form_no ?? '—'}</TableCell>
                                        <TableCell muted>{item.date ?? '—'}</TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${item.id}`}
                                                permission={permissions.view}
                                            />
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
                        <SettingsEmptyState message={t('global.no_pharmacy_fulfillments_found')} />
                    )}
                    <SettingsPagination links={fulfillments.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
