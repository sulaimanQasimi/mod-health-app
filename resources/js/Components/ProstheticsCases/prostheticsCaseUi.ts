function prostheticOptionLabel(prefix: string, value: string, t: (key: string) => string): string {
    const key = `global.${prefix}_${value}`;
    const label = t(key);

    return label === key ? value.replace(/_/g, ' ') : label;
}

export function prostheticCaseSideLabel(side: string, t: (key: string) => string): string {
    return prostheticOptionLabel('prosthetics_side', side, t);
}

export function prostheticCaseCategoryLabel(category: string, t: (key: string) => string): string {
    return prostheticOptionLabel('prosthetics_category', category, t);
}

export function prostheticCasePriorityLabel(priority: string, t: (key: string) => string): string {
    return prostheticOptionLabel('prosthetics_priority', priority, t);
}
