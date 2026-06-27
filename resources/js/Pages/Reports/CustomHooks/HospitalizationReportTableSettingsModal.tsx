import { Button, Checkbox, Label, Modal, ModalBody, ModalFooter, ModalHeader } from 'flowbite-react';
import { useEffect, useMemo, useState } from 'react';
import SearchableMultiSelect from '../../../Components/ui/SearchableMultiSelect';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    BREAKDOWN_CONFIGS,
    BreakdownKey,
    createDefaultHospitalizationTableSettings,
    FIXED_GROUP_COLUMNS,
    getGroupColumnIds,
    HospitalizationTableSettings,
    isGroupFullyHidden,
    SortField,
    TableColumnDefinition,
} from './hospitalizationReportTableSettings';

interface HospitalizationReportTableSettingsModalProps {
    open: boolean;
    settings: HospitalizationTableSettings;
    allColumns: TableColumnDefinition[];
    departmentOptions: Array<{ value: string; label: string }>;
    minTotalOptions: Array<{ value: string; label: string }>;
    onClose: () => void;
    onApply: (settings: HospitalizationTableSettings) => void;
}

function moveItem<T>(items: T[], index: number, direction: 'up' | 'down'): T[] {
    const targetIndex = direction === 'up' ? index - 1 : index + 1;
    if (targetIndex < 0 || targetIndex >= items.length) {
        return items;
    }

    const next = [...items];
    [next[index], next[targetIndex]] = [next[targetIndex], next[index]];
    return next;
}

export default function HospitalizationReportTableSettingsModal({
    open,
    settings,
    allColumns,
    departmentOptions,
    minTotalOptions,
    onClose,
    onApply,
}: HospitalizationReportTableSettingsModalProps) {
    const { t } = useTranslation();
    const [draft, setDraft] = useState<HospitalizationTableSettings>(settings);

    useEffect(() => {
        if (open) {
            setDraft(settings);
        }
    }, [open, settings]);

    const yesNoOptions = useMemo(
        () => [
            { value: '0', label: t('global.no') },
            { value: '1', label: t('global.yes') },
        ],
        [t],
    );

    const sortOptions = useMemo(
        () => [
            { value: 'department', label: t('global.department') },
            { value: 'total', label: t('global.total') },
            ...allColumns.map((column) => ({
                value: `column:${column.id}`,
                label: `${column.groupTitle} / ${column.name}`,
            })),
        ],
        [allColumns, t],
    );

    const toggleColumnVisibility = (columnId: string) => {
        setDraft((current) => ({
            ...current,
            hiddenColumnIds: current.hiddenColumnIds.includes(columnId)
                ? current.hiddenColumnIds.filter((id) => id !== columnId)
                : [...current.hiddenColumnIds, columnId],
        }));
    };

    const toggleGroupVisibility = (groupKey: BreakdownKey, visible: boolean) => {
        const groupColumnIds = getGroupColumnIds(groupKey);

        setDraft((current) => ({
            ...current,
            hiddenColumnIds: visible
                ? current.hiddenColumnIds.filter((id) => !groupColumnIds.includes(id))
                : [...new Set([...current.hiddenColumnIds, ...groupColumnIds])],
        }));
    };

    const moveGroup = (groupKey: BreakdownKey, direction: 'up' | 'down') => {
        setDraft((current) => {
            const index = current.groupOrder.indexOf(groupKey);
            if (index === -1) {
                return current;
            }

            return {
                ...current,
                groupOrder: moveItem(current.groupOrder, index, direction),
            };
        });
    };

    const moveColumnInGroup = (groupKey: BreakdownKey, columnId: string, direction: 'up' | 'down') => {
        setDraft((current) => {
            const order = [...current.columnOrderByGroup[groupKey]];
            const index = order.indexOf(columnId);
            if (index === -1) {
                return current;
            }

            return {
                ...current,
                columnOrderByGroup: {
                    ...current.columnOrderByGroup,
                    [groupKey]: moveItem(order, index, direction),
                },
            };
        });
    };

    const showAllColumns = () => {
        setDraft((current) => ({ ...current, hiddenColumnIds: [] }));
    };

    const hideAllBreakdownColumns = () => {
        setDraft((current) => ({
            ...current,
            hiddenColumnIds: allColumns.map((column) => column.id),
        }));
    };

    return (
        <Modal show={open} onClose={onClose} size="4xl">
            <ModalHeader>{t('global.advanced_filters')}</ModalHeader>
            <ModalBody>
                <div className="space-y-6">
                    <section className="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Columns</h3>
                                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Show, hide, and reorder breakdown columns.
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Button type="button" size="xs" color="light" onClick={showAllColumns}>
                                    Show all
                                </Button>
                                <Button type="button" size="xs" color="light" onClick={hideAllBreakdownColumns}>
                                    Hide all
                                </Button>
                            </div>
                        </div>

                        <div className="space-y-4">
                            {draft.groupOrder.map((groupKey) => {
                                const config = BREAKDOWN_CONFIGS.find((item) => item.key === groupKey);
                                if (!config) {
                                    return null;
                                }

                                const groupTitle = t(config.titleKey);
                                const groupHidden = isGroupFullyHidden(groupKey, draft.hiddenColumnIds);
                                const orderedColumnIds = draft.columnOrderByGroup[groupKey] ?? [];

                                return (
                                    <div
                                        key={groupKey}
                                        className="rounded-lg border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-gray-900/40"
                                    >
                                        <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                                            <label className="flex items-center gap-2">
                                                <Checkbox
                                                    checked={!groupHidden}
                                                    onChange={(event) =>
                                                        toggleGroupVisibility(groupKey, event.target.checked)
                                                    }
                                                />
                                                <span className="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {groupTitle}
                                                </span>
                                            </label>
                                            <div className="flex items-center gap-1">
                                                <Button
                                                    type="button"
                                                    size="xs"
                                                    color="light"
                                                    onClick={() => moveGroup(groupKey, 'up')}
                                                    aria-label={`Move ${groupTitle} up`}
                                                >
                                                    <i className="bx bx-up-arrow-alt" />
                                                </Button>
                                                <Button
                                                    type="button"
                                                    size="xs"
                                                    color="light"
                                                    onClick={() => moveGroup(groupKey, 'down')}
                                                    aria-label={`Move ${groupTitle} down`}
                                                >
                                                    <i className="bx bx-down-arrow-alt" />
                                                </Button>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            {orderedColumnIds.map((columnId, columnIndex) => {
                                                const columnMeta = FIXED_GROUP_COLUMNS[groupKey].find(
                                                    ({ itemKey }) => `${groupKey}:${itemKey}` === columnId,
                                                );
                                                if (!columnMeta) {
                                                    return null;
                                                }

                                                const columnName = t(columnMeta.labelKey);

                                                return (
                                                    <div
                                                        key={columnId}
                                                        className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                                                    >
                                                        <label className="flex min-w-0 flex-1 items-center gap-2">
                                                            <Checkbox
                                                                checked={!draft.hiddenColumnIds.includes(columnId)}
                                                                onChange={() => toggleColumnVisibility(columnId)}
                                                            />
                                                            <span className="text-sm text-gray-800 dark:text-gray-100">
                                                                {columnName}
                                                            </span>
                                                        </label>
                                                        <div className="flex items-center gap-1">
                                                            <Button
                                                                type="button"
                                                                size="xs"
                                                                color="light"
                                                                disabled={columnIndex === 0}
                                                                onClick={() =>
                                                                    moveColumnInGroup(groupKey, columnId, 'up')
                                                                }
                                                                aria-label={`Move ${columnName} up`}
                                                            >
                                                                <i className="bx bx-chevron-up" />
                                                            </Button>
                                                            <Button
                                                                type="button"
                                                                size="xs"
                                                                color="light"
                                                                disabled={columnIndex === orderedColumnIds.length - 1}
                                                                onClick={() =>
                                                                    moveColumnInGroup(groupKey, columnId, 'down')
                                                                }
                                                                aria-label={`Move ${columnName} down`}
                                                            >
                                                                <i className="bx bx-chevron-down" />
                                                            </Button>
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </section>

                    <section className="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">{t('global.sort_by')}</h3>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <Label className="mb-2 block">Field</Label>
                                <SearchableSelect
                                    value={draft.sortBy}
                                    onChange={(value) =>
                                        setDraft((current) => ({
                                            ...current,
                                            sortBy: value as SortField,
                                        }))
                                    }
                                    options={sortOptions}
                                    placeholder={t('global.sort_by')}
                                    searchPlaceholder={t('global.search')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">{t('global.order')}</Label>
                                <div className="flex gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        color={draft.sortDirection === 'asc' ? 'blue' : 'light'}
                                        className="flex-1"
                                        onClick={() =>
                                            setDraft((current) => ({ ...current, sortDirection: 'asc' }))
                                        }
                                    >
                                        {t('global.asc')}
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        color={draft.sortDirection === 'desc' ? 'blue' : 'light'}
                                        className="flex-1"
                                        onClick={() =>
                                            setDraft((current) => ({ ...current, sortDirection: 'desc' }))
                                        }
                                    >
                                        {t('global.desc')}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section className="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Row filters</h3>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <div className="md:col-span-2">
                                <Label className="mb-2 block">{t('global.department')}</Label>
                                <SearchableMultiSelect
                                    values={draft.departmentFilters}
                                    onChange={(values) =>
                                        setDraft((current) => ({
                                            ...current,
                                            departmentFilters: values,
                                        }))
                                    }
                                    options={departmentOptions}
                                    placeholder={t('global.all')}
                                    searchPlaceholder={t('global.search')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">
                                    Minimum {t('global.total')}
                                </Label>
                                <SearchableSelect
                                    value={String(draft.minTotal)}
                                    onChange={(value) =>
                                        setDraft((current) => ({
                                            ...current,
                                            minTotal: value === '' ? 0 : Number(value),
                                        }))
                                    }
                                    options={minTotalOptions}
                                    placeholder={t('global.select')}
                                    searchPlaceholder={t('global.search')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">Hide rows with zero total</Label>
                                <SearchableSelect
                                    value={draft.hideZeroRows ? '1' : '0'}
                                    onChange={(value) =>
                                        setDraft((current) => ({
                                            ...current,
                                            hideZeroRows: value === '1',
                                        }))
                                    }
                                    options={yesNoOptions}
                                    placeholder={t('global.select')}
                                    searchPlaceholder={t('global.search')}
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">Show totals row</Label>
                                <SearchableSelect
                                    value={draft.showTotalsRow ? '1' : '0'}
                                    onChange={(value) =>
                                        setDraft((current) => ({
                                            ...current,
                                            showTotalsRow: value === '1',
                                        }))
                                    }
                                    options={yesNoOptions}
                                    placeholder={t('global.select')}
                                    searchPlaceholder={t('global.search')}
                                />
                            </div>
                        </div>
                    </section>
                </div>
            </ModalBody>
            <ModalFooter className="flex flex-wrap gap-2">
                <Button
                    type="button"
                    color="light"
                    onClick={() => setDraft(createDefaultHospitalizationTableSettings())}
                >
                    {t('global.reset')}
                </Button>
                <Button type="button" color="gray" onClick={onClose}>
                    Cancel
                </Button>
                <Button type="button" color="blue" onClick={() => onApply(draft)}>
                    {t('global.save')}
                </Button>
            </ModalFooter>
        </Modal>
    );
}
