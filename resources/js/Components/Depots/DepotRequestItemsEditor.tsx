import { Alert, Button, Label, Spinner, TextInput } from 'flowbite-react';
import { useEffect, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotFormData } from '../../types/depot';
import SearchableSelect from '../ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/Table';
import { DepotItemKind } from './DepotItemKindField';
import { useAvailableStock } from './useAvailableStock';

export interface DepotRequestLineItem {
    medicine_id: string;
    tool_id: string;
    quantity: string;
    unit_id: string;
    batch_number: string;
}

export const emptyRequestLine = (): DepotRequestLineItem => ({
    medicine_id: '',
    tool_id: '',
    quantity: '',
    unit_id: '',
    batch_number: '',
});

interface LineStockProps {
    stockUrl: string;
    sourceDepotId: string;
    line: DepotRequestLineItem;
    kind: DepotItemKind;
}

function LineStockHint({ stockUrl, sourceDepotId, line, kind }: LineStockProps) {
    const { t } = useTranslation();
    const itemId = kind === 'medicine' ? line.medicine_id : line.tool_id;
    const { available, loading } = useAvailableStock(stockUrl, sourceDepotId, kind, itemId);

    if (!sourceDepotId || !itemId) {
        return null;
    }

    return (
        <span className="text-xs text-gray-500">
            {loading ? (
                <Spinner size="sm" />
            ) : (
                <>
                    {t('global.available_stock')}: <strong>{available?.toLocaleString() ?? '—'}</strong>
                </>
            )}
        </span>
    );
}

interface DepotRequestItemsEditorProps {
    items: DepotRequestLineItem[];
    onChange: (items: DepotRequestLineItem[]) => void;
    formData: DepotFormData;
    sourceDepotId: string;
    stockUrl?: string;
    errors?: Record<string, string>;
}

export default function DepotRequestItemsEditor({
    items,
    onChange,
    formData,
    sourceDepotId,
    stockUrl,
    errors = {},
}: DepotRequestItemsEditorProps) {
    const { t } = useTranslation();
    const [kinds, setKinds] = useState<DepotItemKind[]>(() => items.map((line) => (line.tool_id ? 'tool' : 'medicine')));

    useEffect(() => {
        setKinds((prev) => {
            if (prev.length === items.length) {
                return prev;
            }

            return items.map((line, index) => prev[index] ?? (line.tool_id ? 'tool' : 'medicine'));
        });
    }, [items.length, items]);

    const updateLine = (index: number, patch: Partial<DepotRequestLineItem>) => {
        onChange(items.map((line, i) => (i === index ? { ...line, ...patch } : line)));
    };

    const switchKind = (index: number, kind: DepotItemKind) => {
        setKinds((prev) => prev.map((value, i) => (i === index ? kind : value)));
        updateLine(index, { medicine_id: '', tool_id: '' });
    };

    const addLine = () => {
        setKinds((prev) => [...prev, 'medicine']);
        onChange([...items, emptyRequestLine()]);
    };

    const removeLine = (index: number) => {
        if (items.length <= 1) {
            return;
        }
        setKinds((prev) => prev.filter((_, i) => i !== index));
        onChange(items.filter((_, i) => i !== index));
    };

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white">
                        {t('global.depot.transfer_lines')}
                    </h3>
                    <p className="text-xs text-gray-500">{t('global.depot.transfer_lines_hint')}</p>
                </div>
                <Button type="button" color="light" size="sm" onClick={addLine}>
                    <i className="bx bx-plus me-1" />
                    {t('global.add')} {t('global.depot.line')}
                </Button>
            </div>

            {errors.items && (
                <Alert color="failure">
                    <span className="text-sm">{errors.items}</span>
                </Alert>
            )}

            <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead className="min-w-[7rem]">{t('global.type')}</TableHead>
                            <TableHead className="min-w-[12rem]">{t('global.name')}</TableHead>
                            <TableHead className="min-w-[6rem]">{t('global.quantity')}</TableHead>
                            <TableHead className="min-w-[8rem]">{t('global.unit')}</TableHead>
                            <TableHead className="min-w-[8rem]">{t('global.batch_number')}</TableHead>
                            <TableHead className="w-12" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {items.map((line, index) => {
                            const kind = kinds[index] ?? 'medicine';
                            const medicineError = errors[`items.${index}.medicine_id`];
                            const toolError = errors[`items.${index}.tool_id`];
                            const quantityError = errors[`items.${index}.quantity`];

                            return (
                                <TableRow key={index}>
                                    <TableCell>
                                        <div className="inline-flex rounded-lg border border-gray-200 p-0.5 dark:border-gray-600">
                                            <button
                                                type="button"
                                                onClick={() => switchKind(index, 'medicine')}
                                                className={`rounded-md px-2 py-1 text-xs font-medium ${
                                                    kind === 'medicine'
                                                        ? 'bg-emerald-600 text-white'
                                                        : 'text-gray-600 dark:text-gray-300'
                                                }`}
                                            >
                                                {t('global.medicine')}
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => switchKind(index, 'tool')}
                                                className={`rounded-md px-2 py-1 text-xs font-medium ${
                                                    kind === 'tool'
                                                        ? 'bg-emerald-600 text-white'
                                                        : 'text-gray-600 dark:text-gray-300'
                                                }`}
                                            >
                                                {t('global.depot.tool')}
                                            </button>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {kind === 'medicine' ? (
                                            <SearchableSelect
                                                value={line.medicine_id}
                                                onChange={(value) => updateLine(index, { medicine_id: value, tool_id: '' })}
                                                options={[
                                                    { value: '', label: t('global.select') },
                                                    ...formData.medicines.map((item) => ({
                                                        value: String(item.id),
                                                        label: item.name,
                                                    })),
                                                ]}
                                            />
                                        ) : (
                                            <SearchableSelect
                                                value={line.tool_id}
                                                onChange={(value) => updateLine(index, { tool_id: value, medicine_id: '' })}
                                                options={[
                                                    { value: '', label: t('global.select') },
                                                    ...formData.tools.map((item) => ({
                                                        value: String(item.id),
                                                        label: item.name,
                                                    })),
                                                ]}
                                            />
                                        )}
                                        {(medicineError || toolError) && (
                                            <p className="mt-1 text-xs text-red-600">{medicineError ?? toolError}</p>
                                        )}
                                        {stockUrl && (
                                            <div className="mt-1">
                                                <LineStockHint
                                                    stockUrl={stockUrl}
                                                    sourceDepotId={sourceDepotId}
                                                    line={line}
                                                    kind={kind}
                                                />
                                            </div>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <TextInput
                                            type="number"
                                            min={1}
                                            value={line.quantity}
                                            onChange={(event) => updateLine(index, { quantity: event.target.value })}
                                        />
                                        {quantityError && (
                                            <p className="mt-1 text-xs text-red-600">{quantityError}</p>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <SearchableSelect
                                            value={line.unit_id}
                                            onChange={(value) => updateLine(index, { unit_id: value })}
                                            options={[
                                                { value: '', label: t('global.select') },
                                                ...formData.units.map((unit) => ({
                                                    value: String(unit.id),
                                                    label: unit.name,
                                                })),
                                            ]}
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <TextInput
                                            value={line.batch_number}
                                            onChange={(event) => updateLine(index, { batch_number: event.target.value })}
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <Button
                                            type="button"
                                            color="light"
                                            size="xs"
                                            disabled={items.length <= 1}
                                            onClick={() => removeLine(index)}
                                            aria-label={t('global.delete')}
                                        >
                                            <i className="bx bx-trash text-red-500" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            );
                        })}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
