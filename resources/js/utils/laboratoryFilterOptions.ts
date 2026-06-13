import { SearchableSelectOption } from '../Components/ui/SearchableSelect';
import { SelectOption } from '../types/laboratory';

type TranslateFn = (key: string) => string;

export function allOption(t: TranslateFn): SearchableSelectOption {
    return { value: '', label: t('global.all') };
}

export function statusFilterOptions(t: TranslateFn): SearchableSelectOption[] {
    return [
        allOption(t),
        { value: 'pending', label: t('global.pending') },
        { value: 'in_progress', label: t('global.in_progress') },
        { value: 'completed', label: t('global.completed') },
        { value: 'cancelled', label: t('global.cancelled') },
    ];
}

export function priorityFilterOptions(t: TranslateFn): SearchableSelectOption[] {
    return [
        allOption(t),
        { value: 'normal', label: t('global.normal') },
        { value: 'urgent', label: t('global.urgent') },
        { value: 'stat', label: t('global.stat') },
    ];
}

export function perPageFilterOptions(values: string[]): SearchableSelectOption[] {
    return values.map((value) => ({ value, label: value }));
}

export function perPageFilterOptionsWithAll(t: TranslateFn, values: string[]): SearchableSelectOption[] {
    return [
        ...values.map((value) => ({ value, label: value })),
        { value: 'all', label: t('global.all') },
    ];
}

export function selectOptionsFromItems(items: SelectOption[]): SearchableSelectOption[] {
    return items.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));
}

export function selectOptionsWithAll(t: TranslateFn, items: SelectOption[]): SearchableSelectOption[] {
    return [allOption(t), ...selectOptionsFromItems(items)];
}
