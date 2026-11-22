type Translate = (key: string) => string;

const STATUS_KEYS: Record<string, string> = {
    draft: 'global.draft',
    received: 'global.received',
    cancelled: 'global.cancelled',
    pending: 'global.pending',
    approved: 'global.approved',
    fulfilled: 'global.fulfilled',
    rejected: 'global.rejected',
};

const MOVEMENT_TYPE_KEYS: Record<string, string> = {
    purchase_receive: 'global.purchase_receive',
    depot_to_pharmacy: 'global.depot.depot_to_pharmacy',
    depot_to_depot: 'global.depot.depot_to_depot',
    sale: 'global.movement_sale',
    adjustment: 'global.movement_adjustment',
};

export function inventoryStatusLabel(t: Translate, status: string): string {
    const key = STATUS_KEYS[status];
    if (!key) return status;
    const value = t(key);
    return value === key ? status : value;
}

export function inventoryMovementTypeLabel(t: Translate, type: string): string {
    const key = MOVEMENT_TYPE_KEYS[type];
    if (!key) return type.replace(/_/g, ' ');
    const value = t(key);
    return value === key ? type.replace(/_/g, ' ') : value;
}

export function medicineFieldLabel(t: Translate, field: string): string {
    const map: Record<string, string> = {
        name: 'global.name',
        type: 'global.type',
        gram: 'global.gram',
        ml: 'global.ml',
    };
    const key = map[field];
    return key ? t(key) : field.replace(/_/g, ' ');
}
