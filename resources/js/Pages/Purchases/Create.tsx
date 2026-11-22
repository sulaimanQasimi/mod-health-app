import { router, useForm } from '@inertiajs/react';
import { Button, Label, TextInput } from 'flowbite-react';
import { FormEvent, useMemo } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';

type MedicineOption = {
    id: number;
    name: string;
    unit_id: number | null;
    units_per_pack: number;
};

type Line = {
    medicine_id: string;
    batch_number: string;
    expiry_date: string;
    packs: string;
    units: string;
    units_per_pack: string;
    purchase_price: string;
    unit_sale_price: string;
    pack_price: string;
};

const emptyLine = (): Line => ({
    medicine_id: '',
    batch_number: '',
    expiry_date: '',
    packs: '1',
    units: '0',
    units_per_pack: '1',
    purchase_price: '0',
    unit_sale_price: '0',
    pack_price: '0',
});

export default function CreatePurchase({
    formData,
    inventoryNav,
    urls,
}: {
    formData: {
        depots: Array<{ id: number; name: string }>;
        suppliers: Array<{ id: number; name: string }>;
        medicines: MedicineOption[];
    };
    inventoryNav: InventoryNavUrls;
    urls: { store: string; index: string; back: string };
}) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        depot_id: '',
        supplier_id: '',
        supplier: '',
        purchase_date: new Date().toISOString().slice(0, 10),
        notes: '',
        items: [emptyLine()] as Line[],
    });

    const medicineMap = useMemo(() => {
        const map = new Map<number, MedicineOption>();
        formData.medicines.forEach((m) => map.set(m.id, m));
        return map;
    }, [formData.medicines]);

    const totalUnits = (line: Line) => {
        const upp = Math.max(1, Number(line.units_per_pack) || 1);
        return Math.max(0, Number(line.packs) || 0) * upp + Math.max(0, Number(line.units) || 0);
    };

    const updateLine = (index: number, patch: Partial<Line>) => {
        setData(
            'items',
            data.items.map((line, i) => (i === index ? { ...line, ...patch } : line)),
        );
    };

    const onMedicineChange = (index: number, medicineId: string) => {
        const medicine = medicineMap.get(Number(medicineId));
        const upp = String(medicine?.units_per_pack ?? 1);
        const unitSale = data.items[index].unit_sale_price;
        updateLine(index, {
            medicine_id: medicineId,
            units_per_pack: upp,
            pack_price: Number(unitSale) > 0 ? String(Number(unitSale) * Number(upp)) : data.items[index].pack_price,
        });
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(urls.store);
    };

    return (
        <InventoryShell
            title={t('global.new_purchase')}
            active="purchases"
            urls={inventoryNav}
            action={
                <button
                    type="button"
                    onClick={() => router.visit(urls.index)}
                    className="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200"
                >
                    <i className="bx bx-arrow-back" />
                    {t('global.back')}
                </button>
            }
        >
            <form onSubmit={submit} className="space-y-3">
                <div className="grid gap-2 md:grid-cols-4">
                    <div>
                        <Label className="text-xs">{t('global.depot.name')} *</Label>
                        <SearchableSelect
                            value={data.depot_id}
                            onChange={(v) => setData('depot_id', v)}
                            options={formData.depots.map((d) => ({ value: String(d.id), label: d.name }))}
                        />
                        {errors.depot_id && <p className="text-xs text-red-600">{errors.depot_id}</p>}
                    </div>
                    <div>
                        <Label className="text-xs">{t('global.supplier')}</Label>
                        <SearchableSelect
                            value={data.supplier_id}
                            onChange={(v) => {
                                setData('supplier_id', v);
                                const s = formData.suppliers.find((x) => String(x.id) === v);
                                if (s) setData('supplier', s.name);
                            }}
                            options={[
                                { value: '', label: t('global.select_supplier') },
                                ...formData.suppliers.map((s) => ({ value: String(s.id), label: s.name })),
                            ]}
                        />
                    </div>
                    <div>
                        <Label className="text-xs">{t('global.date')} *</Label>
                        <TextInput
                            sizing="sm"
                            type="date"
                            value={data.purchase_date}
                            onChange={(e) => setData('purchase_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <Label className="text-xs">{t('global.notes')}</Label>
                        <TextInput sizing="sm" value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                    </div>
                </div>

                <Table>
                    <TableHead>
                        <TableRow>
                            <TableHeader>{t('global.medicine')}</TableHeader>
                            <TableHeader>{t('global.batch_number')}</TableHeader>
                            <TableHeader>{t('global.expiry_date')}</TableHeader>
                            <TableHeader>{t('global.packs')}</TableHeader>
                            <TableHeader>{t('global.units')}</TableHeader>
                            <TableHeader>{t('global.total')}</TableHeader>
                            <TableHeader>{t('global.cost')}</TableHeader>
                            <TableHeader>{t('global.unit_sale')}</TableHeader>
                            <TableHeader>{t('global.pack_price')}</TableHeader>
                            <TableHeader />
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {data.items.map((line, index) => (
                            <TableRow key={index}>
                                <TableCell className="min-w-[160px] py-1">
                                    <SearchableSelect
                                        value={line.medicine_id}
                                        onChange={(v) => onMedicineChange(index, v)}
                                        options={formData.medicines.map((m) => ({
                                            value: String(m.id),
                                            label: m.name,
                                        }))}
                                    />
                                </TableCell>
                                <TableCell className="py-1">
                                    <TextInput
                                        sizing="sm"
                                        value={line.batch_number}
                                        onChange={(e) => updateLine(index, { batch_number: e.target.value })}
                                    />
                                </TableCell>
                                <TableCell className="py-1">
                                    <TextInput
                                        sizing="sm"
                                        type="date"
                                        value={line.expiry_date}
                                        onChange={(e) => updateLine(index, { expiry_date: e.target.value })}
                                    />
                                </TableCell>
                                <TableCell className="py-1 w-20">
                                    <TextInput
                                        sizing="sm"
                                        type="number"
                                        min={0}
                                        value={line.packs}
                                        onChange={(e) => updateLine(index, { packs: e.target.value })}
                                    />
                                </TableCell>
                                <TableCell className="py-1 w-20">
                                    <TextInput
                                        sizing="sm"
                                        type="number"
                                        min={0}
                                        value={line.units}
                                        onChange={(e) => updateLine(index, { units: e.target.value })}
                                    />
                                </TableCell>
                                <TableCell className="py-1 text-sm font-semibold text-teal-700">{totalUnits(line)}</TableCell>
                                <TableCell className="py-1 w-24">
                                    <TextInput
                                        sizing="sm"
                                        type="number"
                                        min={0}
                                        step="0.01"
                                        value={line.purchase_price}
                                        onChange={(e) => updateLine(index, { purchase_price: e.target.value })}
                                    />
                                </TableCell>
                                <TableCell className="py-1 w-24">
                                    <TextInput
                                        sizing="sm"
                                        type="number"
                                        min={0}
                                        step="0.01"
                                        value={line.unit_sale_price}
                                        onChange={(e) => {
                                            const upp = Math.max(1, Number(line.units_per_pack) || 1);
                                            updateLine(index, {
                                                unit_sale_price: e.target.value,
                                                pack_price:
                                                    Number(e.target.value) > 0
                                                        ? String(Number(e.target.value) * upp)
                                                        : line.pack_price,
                                            });
                                        }}
                                    />
                                </TableCell>
                                <TableCell className="py-1 w-24">
                                    <TextInput
                                        sizing="sm"
                                        type="number"
                                        min={0}
                                        step="0.01"
                                        value={line.pack_price}
                                        onChange={(e) => updateLine(index, { pack_price: e.target.value })}
                                    />
                                </TableCell>
                                <TableCell className="py-1">
                                    <button
                                        type="button"
                                        className="inline-flex h-7 w-7 items-center justify-center rounded-md text-red-600 hover:bg-red-50 disabled:opacity-40"
                                        disabled={data.items.length <= 1}
                                        onClick={() => setData('items', data.items.filter((_, i) => i !== index))}
                                        title={t('global.remove')}
                                    >
                                        <i className="bx bx-trash" />
                                    </button>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>

                {(errors as any).batch_number && (
                    <p className="text-xs text-red-600">{(errors as any).batch_number}</p>
                )}

                <div className="flex flex-wrap gap-1.5">
                    <Button
                        type="button"
                        size="xs"
                        color="light"
                        onClick={() => setData('items', [...data.items, emptyLine()])}
                    >
                        <i className="bx bx-plus me-1" />
                        {t('global.line')}
                    </Button>
                    <Button type="submit" size="xs" color="success" disabled={processing}>
                        <i className="bx bx-save me-1" />
                        {t('global.save_draft')}
                    </Button>
                </div>
            </form>
        </InventoryShell>
    );
}
