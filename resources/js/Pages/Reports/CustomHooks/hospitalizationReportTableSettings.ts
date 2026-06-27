export type BreakdownKey = 'genders' | 'discharge_statuses' | 'job_types';

export type SortField = 'department' | 'total' | `column:${string}`;

export interface TableColumnDefinition {
    id: string;
    itemKey: string;
    name: string;
    groupKey: BreakdownKey;
    groupTitle: string;
}

export interface HospitalizationTableSettings {
    hiddenColumnIds: string[];
    groupOrder: BreakdownKey[];
    columnOrderByGroup: Record<BreakdownKey, string[]>;
    sortBy: SortField;
    sortDirection: 'asc' | 'desc';
    departmentFilters: string[];
    hideZeroRows: boolean;
    showTotalsRow: boolean;
    minTotal: number;
}

export const BREAKDOWN_CONFIGS: Array<{ key: BreakdownKey; titleKey: string }> = [
    { key: 'genders', titleKey: 'global.gender' },
    { key: 'discharge_statuses', titleKey: 'global.discharge_status' },
    { key: 'job_types', titleKey: 'global.job_type' },
];

export const FIXED_GROUP_COLUMNS: Record<
    BreakdownKey,
    Array<{ itemKey: string; labelKey: string }>
> = {
    genders: [
        { itemKey: '0', labelKey: 'global.male' },
        { itemKey: '1', labelKey: 'global.female' },
    ],
    discharge_statuses: [
        { itemKey: 'recovered', labelKey: 'global.recovered' },
        { itemKey: 'died', labelKey: 'global.died' },
        { itemKey: 'moved', labelKey: 'global.moved' },
    ],
    job_types: [
        { itemKey: 'militant', labelKey: 'global.militant' },
        { itemKey: 'civilian', labelKey: 'global.civilian' },
        { itemKey: 'retired', labelKey: 'global.retired' },
    ],
};

export function buildDefaultColumnOrderByGroup(): Record<BreakdownKey, string[]> {
    return BREAKDOWN_CONFIGS.reduce(
        (acc, { key }) => {
            acc[key] = FIXED_GROUP_COLUMNS[key].map(({ itemKey }) => `${key}:${itemKey}`);
            return acc;
        },
        {} as Record<BreakdownKey, string[]>,
    );
}

export function createDefaultHospitalizationTableSettings(): HospitalizationTableSettings {
    return {
        hiddenColumnIds: [],
        groupOrder: BREAKDOWN_CONFIGS.map((config) => config.key),
        columnOrderByGroup: buildDefaultColumnOrderByGroup(),
        sortBy: 'department',
        sortDirection: 'asc',
        departmentFilters: [],
        hideZeroRows: false,
        showTotalsRow: true,
        minTotal: 0,
    };
}

export function getGroupColumnIds(groupKey: BreakdownKey): string[] {
    return FIXED_GROUP_COLUMNS[groupKey].map(({ itemKey }) => `${groupKey}:${itemKey}`);
}

export function isGroupFullyHidden(groupKey: BreakdownKey, hiddenColumnIds: string[]): boolean {
    return getGroupColumnIds(groupKey).every((columnId) => hiddenColumnIds.includes(columnId));
}
