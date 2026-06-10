import { Alert, Button, Spinner, TextInput } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotFormData } from '../../types/depot';
import SearchableSelect from '../ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/Table';
import { useAvailableStock } from './useAvailableStock';

export interface DepotPharmacyLineItem {
    medicine_id: string;
    quantity: string;
    unit_id: string;
    batch_number: string;
    expiry_date: string;
}

export const emptyPharmacyLine = (): DepotPharmacyLineItem => ({
    medicine_id: '',
    quantity: '',
    unit_id: '',
    batch_number: '',
    expiry_date: '',
});

interface LineStockProps {
    stockUrl: string;
    depotId: string;
    line: DepotPharmacyLineItem;
    onUseAvailable: (quantity: string) => void;
}

function LineStockHint({ stockUrl, depotId, line, onUseAvailable }: LineStockProps) {
    const { t } = useTranslation();
    const { available, loading } = useAvailableStock(stockUrl, depotId, 'medicine', line.medicine_id);

    if (!depotId || !line.medicine_id) {
        return null;
    }

    const exceedsStock =
        available !== null && line.quantity !== '' && Number(line.quantity) > available;

    return (
        <div className="mt-1.5 space-y-1">
            <div className="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                {loading ? (
                    <Spinner size="sm" />
                ) : (
                    <>
                        <span>
                            {t('global.available_stock')}:{' '}
                            <strong className="text-gray-700 dark:text-gray-200">
                                {available?.toLocaleString() ?? '—'}
                            </strong>
                        </span>
                        {available !== null && available > 0 && (
                            <button
                                type="button"
                                onClick={() => onUseAvailable(String(available))}
                                className="font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400"
                            >
                                {t('global.depot.use_available_stock')}
                            </button>
                        )}
                    </>
                )}
            </div>
            {exceedsStock && (
                <p className="text-xs text-amber-600 dark:text-amber-400">
                    {t('global.depot.quantity_exceeds_stock')}
                </p>
            )}
        </div>
    );
}

interface DepotPharmacyItemsEditorProps {
    items: DepotPharmacyLineItem[];
    onChange: (items: DepotPharmacyLineItem[]) => void;
    formData: DepotFormData;
    depotId: string;
    stockUrl?: string;
    errors?: Record<string, string>;
    disabled?: boolean;
}

export default function DepotPharmacyItemsEditor({
    items,
    onChange,
    formData,
    depotId,
    stockUrl,
    errors = {},
    disabled = false,
}: DepotPharmacyItemsEditorProps) {
    const { t } = useTranslation();

    const updateLine = (index: number, patch: Partial<DepotPharmacyLineItem>) => {
        onChange(items.map((line, i) => (i === index ? { ...line, ...patch } : line)));
    };

    const addLine = () => {
        onChange([...items, emptyPharmacyLine()]);
    };

    const removeLine = (index: number) => {
        if (items.length <= 1) {
            return;
        }
        onChange(items.filter((_, i) => i !== index));
    };

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white">
                        {t('global.depot.pharmacy_lines')}
                    </h3>
                    <p className="text-xs text-gray-500">{t('global.depot.pharmacy_lines_hint')}</p>
                </div>
                <Button type="button" color="light" size="sm" onClick={addLine} disabled={disabled}>
                    <i className="bx bx-plus me-1" />
                    {t('global.add')} {t('global.medicine')}
                </Button>
            </div>

            {!depotId && (
                <Alert color="warning">
                    <span className="text-sm">{t('global.depot.select_source_depot_first')}</span>
                </Alert>
            )}

            {errors.items && (
                <Alert color="failure">
                    <span className="text-sm">{errors.items}</span>
                </Alert>
            )}

            <Table className="min-w-[920px]">
                <TableHead>
                    <TableRow variant="header">
                        <TableHeader className="w-10">#</TableHeader>
                        <TableHeader className="min-w-[14rem]">{t('global.medicine')}</TableHeader>
                        <TableHeader align="center" className="min-w-[6rem]">
                            {t('global.quantity')}
                        </TableHeader>
                        <TableHeader className="min-w-[8rem]">{t('global.unit')}</TableHeader>
                        <TableHeader className="min-w-[8rem]">{t('global.batch_number')}</TableHeader>
                        <TableHeader className="min-w-[9rem]">{t('global.expiry_date')}</TableHeader>
                        <TableHeader align="center" className="w-14">
                            <span className="sr-only">{t('global.actions')}</span>
                        </TableHeader>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {items.map((line, index) => {
                        const medicineError = errors[`items.${index}.medicine_id`];
                        const quantityError = errors[`items.${index}.quantity`];

                        return (
                            <TableRow key={index}>
                                <TableCell muted className="align-top">
                                    {index + 1}
                                </TableCell>
                                <TableCell className="min-w-[14rem] align-top">
                                    <SearchableSelect
                                        value={line.medicine_id}
                                        onChange={(value) => updateLine(index, { medicine_id: value })}
                                        disabled={disabled || !depotId}
                                        options={[
                                            { value: '', label: t('global.select') },
                                            ...formData.medicines.map((item) => ({
                                                value: String(item.id),
                                                label: item.name,
                                            })),
                                        ]}
                                    />
                                    {medicineError && (
                                        <p className="mt-1 text-xs text-red-600">{medicineError}</p>
                                    )}
                                    {stockUrl && (
                                        <LineStockHint
                                            stockUrl={stockUrl}
                                            depotId={depotId}
                                            line={line}
                                            onUseAvailable={(quantity) => updateLine(index, { quantity })}
                                        />
                                    )}
                                </TableCell>
                                <TableCell align="center" className="align-top">
                                    <TextInput
                                        type="number"
                                        min={1}
                                        className="w-24"
                                        value={line.quantity}
                                        disabled={disabled || !depotId}
                                        onChange={(event) =>
                                            updateLine(index, { quantity: event.target.value })
                                        }
                                    />
                                    {quantityError && (
                                        <p className="mt-1 text-xs text-red-600">{quantityError}</p>
                                    )}
                                </TableCell>
                                <TableCell className="align-top">
                                    <SearchableSelect
                                        value={line.unit_id}
                                        onChange={(value) => updateLine(index, { unit_id: value })}
                                        disabled={disabled || !depotId}
                                        options={[
                                            { value: '', label: t('global.select') },
                                            ...formData.units.map((unit) => ({
                                                value: String(unit.id),
                                                label: unit.name,
                                            })),
                                        ]}
                                    />
                                </TableCell>
                                <TableCell className="align-top">
                                    <TextInput
                                        value={line.batch_number}
                                        disabled={disabled || !depotId}
                                        onChange={(event) =>
                                            updateLine(index, { batch_number: event.target.value })
                                        }
                                    />
                                </TableCell>
                                <TableCell className="align-top">
                                    <TextInput
                                        type="date"
                                        value={line.expiry_date}
                                        disabled={disabled || !depotId}
                                        onChange={(event) =>
                                            updateLine(index, { expiry_date: event.target.value })
                                        }
                                    />
                                </TableCell>
                                <TableCell align="center" className="align-top">
                                    <Button
                                        type="button"
                                        color="light"
                                        size="xs"
                                        disabled={disabled || items.length <= 1}
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
    );
}
