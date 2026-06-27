export type SortField = 'department' | 'total' | `column:${string}`;

export interface TableColumnDefinition {
    id: string;
    itemKey: string;
    name: string;
    groupKey: string;
    groupTitle: string;
}

export interface GeneralReportTableSettings {
    hiddenColumnIds: string[];
    groupOrder: string[];
    columnOrderByGroup: Record<string, string[]>;
    sortBy: SortField;
    sortDirection: 'asc' | 'desc';
    departmentFilters: string[];
    hideZeroRows: boolean;
    showTotalsRow: boolean;
    minTotal: number;
}

export interface DepartmentReportRow {
    department_id: number | null;
    department_name: string | null;
    count: number;
}

export interface ColumnGroupView {
    key: string;
    title: string;
    columns: Array<{ id: string; itemKey: string; name: string }>;
}

export function createDefaultTableSettings(allColumns: TableColumnDefinition[]): GeneralReportTableSettings {
    const groupOrder = [...new Set(allColumns.map((column) => column.groupKey))];
    const columnOrderByGroup = groupOrder.reduce<Record<string, string[]>>((acc, groupKey) => {
        acc[groupKey] = allColumns.filter((column) => column.groupKey === groupKey).map((column) => column.id);
        return acc;
    }, {});

    return {
        hiddenColumnIds: [],
        groupOrder,
        columnOrderByGroup,
        sortBy: 'department',
        sortDirection: 'asc',
        departmentFilters: [],
        hideZeroRows: false,
        showTotalsRow: true,
        minTotal: 0,
    };
}

export function getGroupColumnIds(groupKey: string, allColumns: TableColumnDefinition[]): string[] {
    return allColumns.filter((column) => column.groupKey === groupKey).map((column) => column.id);
}

export function isGroupFullyHidden(
    groupKey: string,
    hiddenColumnIds: string[],
    allColumns: TableColumnDefinition[],
): boolean {
    const columnIds = getGroupColumnIds(groupKey, allColumns);
    return columnIds.length > 0 && columnIds.every((columnId) => hiddenColumnIds.includes(columnId));
}

export function buildOrderedColumnGroups(
    settings: GeneralReportTableSettings,
    allColumns: TableColumnDefinition[],
): ColumnGroupView[] {
    const columnById = new Map(allColumns.map((column) => [column.id, column]));

    return settings.groupOrder
        .map((groupKey) => {
            const orderedIds = settings.columnOrderByGroup[groupKey] ?? getGroupColumnIds(groupKey, allColumns);
            const columns = orderedIds
                .map((columnId) => columnById.get(columnId))
                .filter(
                    (column): column is TableColumnDefinition =>
                        column !== undefined && !settings.hiddenColumnIds.includes(column.id),
                )
                .map((column) => ({
                    id: column.id,
                    itemKey: column.itemKey,
                    name: column.name,
                }));

            if (columns.length === 0) {
                return null;
            }

            const title =
                allColumns.find((column) => column.groupKey === groupKey)?.groupTitle ?? groupKey;

            return {
                key: groupKey,
                title,
                columns,
            };
        })
        .filter((group): group is ColumnGroupView => group !== null);
}

export function getDepartmentRowKey(row: DepartmentReportRow): string {
    return String(row.department_id ?? row.department_name ?? '');
}

export function buildDepartmentOptions(rows: DepartmentReportRow[]) {
    const options = new Map<string, string>();

    rows.forEach((row) => {
        const value = getDepartmentRowKey(row);
        if (!options.has(value)) {
            options.set(value, row.department_name ?? 'Unknown');
        }
    });

    return Array.from(options.entries())
        .map(([value, label]) => ({ value, label }))
        .sort((left, right) => left.label.localeCompare(right.label));
}

export function buildMinTotalOptions(rows: DepartmentReportRow[], allLabel: string) {
    const totals = [...new Set(rows.map((row) => row.count))].sort((left, right) => left - right);

    return [
        { value: '0', label: allLabel },
        ...totals
            .filter((total) => total > 0)
            .map((total) => ({
                value: String(total),
                label: `>= ${total}`,
            })),
    ];
}

export function countActiveTableFilters(settings: GeneralReportTableSettings): number {
    let count = 0;

    if (settings.departmentFilters.length > 0) {
        count += 1;
    }
    if (settings.hideZeroRows) {
        count += 1;
    }
    if (settings.minTotal > 0) {
        count += 1;
    }
    if (settings.hiddenColumnIds.length > 0) {
        count += 1;
    }
    if (settings.sortBy !== 'department' || settings.sortDirection !== 'asc') {
        count += 1;
    }
    if (!settings.showTotalsRow) {
        count += 1;
    }

    return count;
}

export function applyTableSettings<T extends DepartmentReportRow>(
    rows: T[],
    settings: GeneralReportTableSettings,
    getColumnCount: (row: T, columnId: string) => number,
): T[] {
    let nextRows = [...rows];

    if (settings.departmentFilters.length > 0) {
        nextRows = nextRows.filter((row) => settings.departmentFilters.includes(getDepartmentRowKey(row)));
    }

    if (settings.hideZeroRows) {
        nextRows = nextRows.filter((row) => row.count > 0);
    }

    if (settings.minTotal > 0) {
        nextRows = nextRows.filter((row) => row.count >= settings.minTotal);
    }

    nextRows.sort((left, right) => {
        let comparison = 0;

        if (settings.sortBy === 'department') {
            comparison = (left.department_name ?? '').localeCompare(right.department_name ?? '');
        } else if (settings.sortBy === 'total') {
            comparison = left.count - right.count;
        } else if (settings.sortBy.startsWith('column:')) {
            const columnId = settings.sortBy.replace('column:', '');
            comparison = getColumnCount(left, columnId) - getColumnCount(right, columnId);
        }

        return settings.sortDirection === 'asc' ? comparison : -comparison;
    });

    return nextRows;
}
