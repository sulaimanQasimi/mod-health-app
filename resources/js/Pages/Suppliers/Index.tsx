import { router, useForm } from '@inertiajs/react';
import { Badge, Button, Label, TextInput, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import SettingsPagination from '../../Components/Settings/SettingsPagination';

type SupplierRow = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    is_active: boolean;
    purchases_count: number;
};

export default function SuppliersIndex({
    suppliers,
    filters: serverFilters,
    inventoryNav,
    urls,
}: {
    suppliers: { data: SupplierRow[]; links: any[] };
    filters: { search: string; is_active: string };
    inventoryNav: InventoryNavUrls;
    urls: { index: string; store: string; update: string; destroy: string };
}) {
    const [filters, setFilters] = useState(serverFilters);
    const [editingId, setEditingId] = useState<number | null>(null);
    const form = useForm({
        name: '',
        phone: '',
        email: '',
        address: '',
        notes: '',
        is_active: true,
    });

    const apply = (e?: FormEvent) => {
        e?.preventDefault();
        router.get(urls.index, Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')), {
            preserveScroll: true,
        });
    };

    const startEdit = (row: SupplierRow) => {
        setEditingId(row.id);
        form.setData({
            name: row.name,
            phone: row.phone ?? '',
            email: row.email ?? '',
            address: row.address ?? '',
            notes: '',
            is_active: row.is_active,
        });
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
        form.post(urls.store, {
            onSuccess: () => form.reset(),
        });
    };

    return (
        <InventoryShell title="Suppliers" active="suppliers" urls={inventoryNav}>
            <div className="grid gap-3 lg:grid-cols-[280px_1fr]">
                <form onSubmit={submit} className="space-y-2 rounded-md border border-gray-100 p-2 dark:border-gray-700">
                    <div className="mb-1 flex items-center gap-1.5 text-sm font-semibold">
                        <i className={`bx ${editingId ? 'bx-edit' : 'bx-plus-circle'} text-teal-600`} />
                        {editingId ? 'Edit supplier' : 'Add supplier'}
                    </div>
                    <div>
                        <Label className="text-xs">Name *</Label>
                        <TextInput sizing="sm" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                    </div>
                    <div>
                        <Label className="text-xs">Phone</Label>
                        <TextInput sizing="sm" value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} />
                    </div>
                    <div>
                        <Label className="text-xs">Email</Label>
                        <TextInput sizing="sm" type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} />
                    </div>
                    <div>
                        <Label className="text-xs">Address</Label>
                        <Textarea rows={2} value={form.data.address} onChange={(e) => form.setData('address', e.target.value)} />
                    </div>
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
                            onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                            placeholder="Search supplier"
                        />
                        <Button type="submit" size="xs" color="light">
                            <i className="bx bx-search" />
                        </Button>
                    </form>

                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeader>Name</TableHeader>
                                <TableHeader>Phone</TableHeader>
                                <TableHeader>Purchases</TableHeader>
                                <TableHeader>Status</TableHeader>
                                <TableHeader />
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {suppliers.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-6 text-center text-sm text-gray-500">
                                        <i className="bx bx-buildings text-2xl text-gray-300" />
                                        <div>No suppliers yet</div>
                                    </TableCell>
                                </TableRow>
                            ) : (
                                suppliers.data.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="py-1.5 text-sm font-medium">{row.name}</TableCell>
                                        <TableCell className="py-1.5 text-sm">{row.phone ?? '—'}</TableCell>
                                        <TableCell className="py-1.5 text-sm">{row.purchases_count}</TableCell>
                                        <TableCell className="py-1.5">
                                            <Badge color={row.is_active ? 'success' : 'gray'} size="xs">
                                                {row.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="py-1.5">
                                            <div className="flex gap-1">
                                                <button
                                                    type="button"
                                                    className="inline-flex h-7 w-7 items-center justify-center rounded-md text-amber-600 hover:bg-amber-50"
                                                    onClick={() => startEdit(row)}
                                                    title="Edit"
                                                >
                                                    <i className="bx bx-edit" />
                                                </button>
                                                <button
                                                    type="button"
                                                    className="inline-flex h-7 w-7 items-center justify-center rounded-md text-red-600 hover:bg-red-50"
                                                    onClick={() => {
                                                        if (confirm('Delete this supplier?')) {
                                                            router.delete(urls.destroy.replace('__ID__', String(row.id)));
                                                        }
                                                    }}
                                                    title="Delete"
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
                        <SettingsPagination links={suppliers.links ?? []} />
                    </div>
                </div>
            </div>
        </InventoryShell>
    );
}
