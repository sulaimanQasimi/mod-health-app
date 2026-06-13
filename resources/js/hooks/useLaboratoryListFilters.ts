import { router } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useState } from 'react';

function cleanFilters<T extends Record<string, string>>(filters: T): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export function useLaboratoryListFilters<T extends Record<string, string>>(
    serverFilters: T,
    url: string,
    emptyFilters: T,
) {
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: T) => {
            setProcessing(true);
            router.get(url, cleanFilters(nextFilters), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [url],
    );

    const updateFilter = (field: keyof T, value: string) => {
        setFilters((current) => ({ ...current, [field]: value }));
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        const reset = { ...emptyFilters, per_page: filters.per_page } as T;
        setFilters(reset);
        applyFilters(reset);
    };

    return {
        filters,
        processing,
        updateFilter,
        handleSubmit,
        handleReset,
    };
}
