import { useCallback, useEffect, useState } from 'react';

export function useAvailableStock(
    stockUrl: string,
    depotId: string,
    itemType: 'medicine' | 'tool',
    itemId: string,
) {
    const [available, setAvailable] = useState<number | null>(null);
    const [loading, setLoading] = useState(false);

    const load = useCallback(async () => {
        if (!depotId || !itemId) {
            setAvailable(null);
            return;
        }

        setLoading(true);
        try {
            const params = new URLSearchParams({
                depot_id: depotId,
                item_type: itemType,
                ...(itemType === 'medicine' ? { medicine_id: itemId } : { tool_id: itemId }),
            });
            const response = await fetch(`${stockUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                setAvailable(null);
                return;
            }
            const payload = await response.json();
            if (payload.success) {
                setAvailable(payload.available_stock ?? null);
            } else {
                setAvailable(payload.available_stock ?? null);
            }
        } finally {
            setLoading(false);
        }
    }, [stockUrl, depotId, itemType, itemId]);

    useEffect(() => {
        load();
    }, [load]);

    return { available, loading, reload: load };
}
