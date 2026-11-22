import { router, useForm } from '@inertiajs/react';
import { Button, Label, TextInput } from 'flowbite-react';
import { FormEvent, useMemo } from 'react';
import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';

type MedicineOption = { id: number; name: string; units_per_pack: number };

type Line = {
    medicine_id: string;
    batch_number: string;
    packs: string;
    units: string;
    units_per_pack: string;
};

const emptyLine = (): Line => ({
    medicine_id: '',
    batch_number: '',
    packs: '1',
    units: '0',
    units_per_pack: '1',
});

export default function CreateDepotTransfer({
    formData,
    defaults,
    inventoryNav,
    urls,
}: {
    formData: {
        depots: Array<{ id: number; name: string }>;
        pharmacies: Array<{ id: number; name: string }>;
        medicines: MedicineOption[];
    };
    defaults: { source_depot_id?: string | number | null; pharmacy_id?: string | number | null };
    inventoryNav: InventoryNavUrls;
    urls: { store: string; index: string; back: string; availableStock?: string };
}) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        source_depot_id: String(defaults.source_depot_id ?? ''),
        pharmacy_id: String(defaults.pharmacy_id ?? ''),
        destination_depot_id: '',
        notes: '',
        items: [emptyLine()] as Line[],
    });

    const medicineMap = useMemo(() => {
        const map = new Map<number, MedicineOption>();
        formData.medicines.forEach((m) => map.set(m.id, m));
        return map;
    }, [formData.medicines]);

    const updateLine = (index: number, patch: Partial<Line>) => {
        setData(
            'items',
            data.items.map((line, i) => (i === index ? { ...line, ...patch } : line)),
        );
    };

    const totalUnits = (line: Line) => {
        const upp = Math.max(1, Number(line.units_per_pack) || 1);
        return Math.max(0, Number(line.packs) || 0) * upp + Math.max(0, Number(line.units) || 0);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(urls.store);
    };

    return (
        <InventoryShell title={t('global.new_transfer')} active="transfers" urls={inventoryNav}>
            <form onSubmit={submit} className="space-y-3">
                <div className="grid gap-2 md:grid-cols-2">
                    <div>
                        <Label className="text-xs">{t('global.from_depot')} *</Label>
                        <SearchableSelect
                            value={data.source_depot_id}
                            onChange={(v) => setData('source_depot_id', v)}
                            options={formData.depots.map((d) => ({ value: String(d.id), label: d.name }))}
                        />
                        {errors.source_depot_id && (
                            <p className="mt-1 text-sm text-red-600">{errors.source_depot_id}</p>
                        )}
                    </div>
                    <div>
                        <Label className="text-xs">{t('global.to_pharmacy')} *</Label>
                        <SearchableSelect
                            value={data.pharmacy_id}
                            onChange={(v) => {
                                setData('pharmacy_id', v);
                                setData('destination_depot_id', '');
                            }}
                            options={formData.pharmacies.map((p) => ({
                                value: String(p.id),
                                label: p.name,
                            }))}
                        />
                        {errors.pharmacy_id && (
                            <p className="mt-1 text-sm text-red-600">{errors.pharmacy_id}</p>
                        )}
                    </div>
                </div>

                <Table>
                    <TableHead>
                        <TableRow>
                            <TableHeader>{t('global.medicine')}</TableHeader>
                            <TableHeader>{t('global.batch_optional')}</TableHeader>
                            <TableHeader>{t('global.packs')}</TableHeader>
                            <TableHeader>{t('global.units')}</TableHeader>
                            <TableHeader>{t('global.total_units')}</TableHeader>
                            <TableHeader />
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {data.items.map((line, index) => (
                            <TableRow key={index}>
                                <TableCell className="min-w-[180px]">
                                    <SearchableSelect
                                        value={line.medicine_id}
                                        onChange={(v) => {
                                            const medicine = medicineMap.get(Number(v));
                                            updateLine(index, {
                                                medicine_id: v,
                                                units_per_pack: String(medicine?.units_per_pack ?? 1),
                                            });
                                        }}
                                        options={formData.medicines.map((m) => ({
                                            value: String(m.id),
                                            label: m.name,
                                        }))}
                                    />
                                </TableCell>
                                <TableCell>
                                    <TextInput
                                        value={line.batch_number}
                                        onChange={(e) => updateLine(index, { batch_number: e.target.value })}
                                        placeholder={t('global.fefo_if_empty')}
                                    />
                                </TableCell>
                                <TableCell>
                                    <TextInput
                                        type="number"
                                        min={0}
                                        value={line.packs}
                                        onChange={(e) => updateLine(index, { packs: e.target.value })}
                                    />
                                </TableCell>
                                <TableCell>
                                    <TextInput
                                        type="number"
                                        min={0}
                                        value={line.units}
                                        onChange={(e) => updateLine(index, { units: e.target.value })}
                                    />
                                </TableCell>
                                <TableCell className="font-semibold">{totalUnits(line)}</TableCell>
                                <TableCell>
                                    <Button
                                        type="button"
                                        size="xs"
                                        color="light"
                                        disabled={data.items.length <= 1}
                                        onClick={() =>
                                            setData(
                                                'items',
                                                data.items.filter((_, i) => i !== index),
                                            )
                                        }
                                    >
                                        {t('global.remove')}
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>

                <div className="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        color="light"
                        onClick={() => setData('items', [...data.items, emptyLine()])}
                    >
                        {t('global.add_line')}
                    </Button>
                    <Button type="submit" color="purple" disabled={processing}>
                        {t('global.save_transfer')}
                    </Button>
                    <Button type="button" color="light" onClick={() => router.visit(urls.index)}>
                        {t('global.cancel')}
                    </Button>
                </div>
            </form>
        </InventoryShell>
    );
}
