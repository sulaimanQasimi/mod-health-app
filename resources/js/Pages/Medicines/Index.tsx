import { Link, router } from '@inertiajs/react';
import { Button, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import MedicineSubNav, { MedicineNavUrls } from '../../Components/Medicines/MedicineSubNav';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult, SettingsPermissions } from '../../types/settings';

interface MedicineItem {
    id: number;
    name: string;
    type?: string | null;
    country?: string | null;
    formula?: string | null;
    gram_ml?: string | null;
}

interface MedicineFilters {
    search: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
    medicine_type_id: string;
    medicine_country_id: string;
}

const defaultMedicineFilters: MedicineFilters = {
    search: '',
    sort_by: 'id',
    sort_order: 'desc',
    per_page: '15',
    medicine_type_id: '',
    medicine_country_id: '',
};

export default function IndexMedicines({
    medicines,
    filters: serverFilters,
    filterOptions,
    permissions,
    inventoryNav,
    medicineNav,
    urls,
}: {
    medicines: PaginatedResult<MedicineItem>;
    filters: MedicineFilters;
    filterOptions: {
        types: Array<{ id: number; type: string }>;
        countries: Array<{ id: number; name: string }>;
    };
    permissions: SettingsPermissions;
    inventoryNav: InventoryNavUrls;
    medicineNav: MedicineNavUrls;
    urls: { index: string; create: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState({ ...defaultMedicineFilters, ...serverFilters });
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => setFilters({ ...defaultMedicineFilters, ...serverFilters }), [serverFilters]);

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

    return (
        <InventoryShell
            title={t('global.medicines')}
            active="medicines"
            urls={inventoryNav}
            action={
                permissions.create ? (
                    <Link
                        href={urls.create}
                        className="inline-flex items-center gap-1 rounded-md bg-teal-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-teal-700"
                    >
                        <i className="bx bx-plus text-sm" />
                        {t('global.create')}
                    </Link>
                ) : undefined
            }
        >
            <MedicineSubNav active="medicines" urls={medicineNav} />

            <form
                onSubmit={(event: FormEvent) => {
                    event.preventDefault();
                    applyFilters(filters);
                }}
                className="mb-2 grid grid-cols-2 gap-2 md:grid-cols-6"
            >
                <TextInput
                    sizing="sm"
                    value={filters.search}
                    onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                    placeholder={t('global.search_by_name')}
                />
                <SearchableSelect
                    value={filters.medicine_type_id || ''}
                    onChange={(value) => setFilters({ ...filters, medicine_type_id: value })}
                    options={[
                        { value: '', label: 'All types' },
                        ...filterOptions.types.map((item) => ({ value: String(item.id), label: item.type })),
                    ]}
                />
                <SearchableSelect
                    value={filters.medicine_country_id || ''}
                    onChange={(value) => setFilters({ ...filters, medicine_country_id: value })}
                    options={[
                        { value: '', label: 'All countries' },
                        ...filterOptions.countries.map((item) => ({ value: String(item.id), label: item.name })),
                    ]}
                />
                <SearchableSelect
                    value={filters.sort_by || 'id'}
                    onChange={(value) => {
                        const next = { ...filters, sort_by: value };
                        setFilters(next);
                        applyFilters(next);
                    }}
                    options={[
                        { value: 'id', label: t('global.id') },
                        { value: 'name', label: t('global.name') },
                        { value: 'created_at', label: t('global.created_at') },
                    ]}
                />
                <Button type="submit" size="xs" color="light" className="h-9" disabled={processing}>
                    <i className="bx bx-search me-1" />
                    {t('global.search')}
                </Button>
                <Button
                    type="button"
                    size="xs"
                    color="light"
                    className="h-9"
                    onClick={() => {
                        setFilters(defaultMedicineFilters);
                        applyFilters(defaultMedicineFilters);
                    }}
                >
                    <i className="bx bx-x" />
                </Button>
            </form>

            {medicines.data.length > 0 ? (
                <Table>
                    <TableHead>
                        <TableRow variant="header">
                            <TableHeader>#</TableHeader>
                            <TableHeader>{t('global.name')}</TableHeader>
                            <TableHeader>Type</TableHeader>
                            <TableHeader>Country</TableHeader>
                            <TableHeader>Formula</TableHeader>
                            <TableHeader>Gram/ml</TableHeader>
                            <TableHeader align="center">{t('global.actions')}</TableHeader>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {medicines.data.map((item, index) => (
                            <TableRow key={item.id}>
                                <TableCell className="py-1.5 text-sm">{(medicines.meta.from ?? 1) + index}</TableCell>
                                <TableCell className="py-1.5 text-sm font-medium">
                                    <span className="inline-flex items-center gap-1.5">
                                        <i className="bx bx-capsule text-teal-600" />
                                        {item.name}
                                    </span>
                                </TableCell>
                                <TableCell className="py-1.5 text-sm">{item.type ?? '—'}</TableCell>
                                <TableCell className="py-1.5 text-sm">{item.country ?? '—'}</TableCell>
                                <TableCell className="py-1.5 text-sm">{item.formula ?? '—'}</TableCell>
                                <TableCell className="py-1.5 text-sm">{item.gram_ml ?? '—'}</TableCell>
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
                <SettingsEmptyState message={t('global.no_medicines_found') || t('global.no_results_found')} />
            )}
            <div className="mt-2">
                <SettingsPagination links={medicines.links} />
            </div>
        </InventoryShell>
    );
}
