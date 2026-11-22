import { router, useForm } from '@inertiajs/react';
import { Badge, Button, Label, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import InventoryShell from '../../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../../Components/Inventory/InventoryModuleNav';
import MedicineSubNav, { MedicineNavKey, MedicineNavUrls } from '../../../Components/Medicines/MedicineSubNav';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../Components/ui/Table';

type Row = Record<string, any> & { id: number };

export default function MedicineLookupsIndex({
    title,
    navKey,
    labelField,
    hasActive,
    extraFields,
    rows,
    filters: serverFilters,
    inventoryNav,
    medicineNav,
    urls,
}: {
    title: string;
    navKey: MedicineNavKey;
    labelField: string;
    hasActive: boolean;
    extraFields: string[];
    rows: { data: Row[]; links: any[] };
    filters: { search: string };
    inventoryNav: InventoryNavUrls;
    medicineNav: MedicineNavUrls;
    urls: { index: string; store: string; update: string; destroy: string };
}) {
    const [filters, setFilters] = useState(serverFilters);
    const [editingId, setEditingId] = useState<number | null>(null);
    const form = useForm<Record<string, any>>({
        [labelField]: '',
        gram: '',
        ml: '',
        is_active: true,
    });

    const apply = (e?: FormEvent) => {
        e?.preventDefault();
        router.get(urls.index, Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')), {
            preserveScroll: true,
        });
    };

    const startEdit = (row: Row) => {
        setEditingId(row.id);
        const next: Record<string, any> = {
            [labelField]: row[labelField] ?? '',
            is_active: row.is_active ?? true,
        };
        extraFields.forEach((field) => {
            next[field] = row[field] ?? '';
        });
        form.setData(next);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (editingId) {
            form.put(urls.update.replace('__ID__', String(editingId)), {
                onSuccess: () => {
                    setEditingId(null);
                    form.reset();
                },
            });
            return;
        }
        form.post(urls.store, { onSuccess: () => form.reset() });
    };

    return (
        <InventoryShell title={title} active="medicines" urls={inventoryNav}>
            <MedicineSubNav active={navKey} urls={medicineNav} />

            <div className="grid gap-3 lg:grid-cols-[260px_1fr]">
                <form onSubmit={submit} className="space-y-2 rounded-md border border-gray-100 p-2 dark:border-gray-700">
                    <div className="flex items-center gap-1 text-sm font-semibold">
                        <i className={`bx ${editingId ? 'bx-edit' : 'bx-plus-circle'} text-teal-600`} />
                        {editingId ? 'Edit' : 'Add'}
                    </div>
                    <div>
                        <Label className="text-xs capitalize">{labelField.replace(/_/g, ' ')} *</Label>
                        <TextInput
                            sizing="sm"
                            value={form.data[labelField] ?? ''}
                            onChange={(e) => form.setData(labelField, e.target.value)}
                            required
                        />
                    </div>
                    {extraFields.includes('gram') && (
                        <div>
                            <Label className="text-xs">Gram</Label>
                            <TextInput
                                sizing="sm"
                                type="number"
                                step="0.0001"
                                value={form.data.gram ?? ''}
                                onChange={(e) => form.setData('gram', e.target.value)}
                            />
                        </div>
                    )}
                    {extraFields.includes('ml') && (
                        <div>
                            <Label className="text-xs">ml</Label>
                            <TextInput
                                sizing="sm"
                                type="number"
                                step="0.0001"
                                value={form.data.ml ?? ''}
                                onChange={(e) => form.setData('ml', e.target.value)}
                            />
                        </div>
                    )}
                    <div className="flex gap-1.5">
                        <Button type="submit" size="xs" color="success" disabled={form.processing}>
                            <i className="bx bx-save me-1" />
                            Save
                        </Button>
                        {editingId && (
                            <Button
                                type="button"
                                size="xs"
                                color="light"
                                onClick={() => {
                                    setEditingId(null);
                                    form.reset();
                                }}
                            >
                                Cancel
                            </Button>
                        )}
                    </div>
                </form>

                <div>
                    <form onSubmit={apply} className="mb-2 flex gap-2">
                        <TextInput
                            sizing="sm"
                            className="flex-1"
                            value={filters.search}
                            onChange={(e) => setFilters({ search: e.target.value })}
                            placeholder={`Search ${title.toLowerCase()}`}
                        />
                        <Button type="submit" size="xs" color="light">
                            <i className="bx bx-search" />
                        </Button>
                    </form>

                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeader className="capitalize">{labelField.replace(/_/g, ' ')}</TableHeader>
                                {extraFields.map((field) => (
                                    <TableHeader key={field} className="capitalize">
                                        {field}
                                    </TableHeader>
                                ))}
                                {hasActive && <TableHeader>Status</TableHeader>}
                                <TableHeader />
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {rows.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={3 + extraFields.length} className="py-6 text-center text-sm text-gray-500">
                                        No records yet
                                    </TableCell>
                                </TableRow>
                            ) : (
                                rows.data.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="py-1.5 text-sm font-medium">{row[labelField]}</TableCell>
                                        {extraFields.map((field) => (
                                            <TableCell key={field} className="py-1.5 text-sm">
                                                {row[field] ?? '—'}
                                            </TableCell>
                                        ))}
                                        {hasActive && (
                                            <TableCell className="py-1.5">
                                                <Badge color={row.is_active ? 'success' : 'gray'} size="xs">
                                                    {row.is_active ? 'Active' : 'Inactive'}
                                                </Badge>
                                            </TableCell>
                                        )}
                                        <TableCell className="py-1.5">
                                            <div className="flex gap-1">
                                                <button
                                                    type="button"
                                                    className="inline-flex h-7 w-7 items-center justify-center rounded-md text-amber-600 hover:bg-amber-50"
                                                    onClick={() => startEdit(row)}
                                                >
                                                    <i className="bx bx-edit" />
                                                </button>
                                                <button
                                                    type="button"
                                                    className="inline-flex h-7 w-7 items-center justify-center rounded-md text-red-600 hover:bg-red-50"
                                                    onClick={() => {
                                                        if (confirm('Delete this record?')) {
                                                            router.delete(urls.destroy.replace('__ID__', String(row.id)));
                                                        }
                                                    }}
                                                >
                                                    <i className="bx bx-trash" />
                                                </button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                    <div className="mt-2">
                        <SettingsPagination links={rows.links ?? []} />
                    </div>
                </div>
            </div>
        </InventoryShell>
    );
}
