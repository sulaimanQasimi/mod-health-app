import { OptionItem, PaginatedResult, SettingsPermissions } from './settings';

export interface DepotNavUrls {
    index: string;
    transactions: string;
    requests: string;
    depotToDepot: string;
    reports: string;
    tools: string;
}

export interface DepotListItem {
    id: number;
    name: string;
    address: string | null;
    branch_name: string | null;
    department_name: string | null;
    pharmacy_name: string | null;
    parent_depot_name: string | null;
    is_active: boolean;
    is_base: boolean;
    users_count: number;
}

export interface DepotListFilters {
    search: string;
    branch_id: string;
    department_id: string;
    pharmacy_id: string;
    parent_depot_id: string;
    is_active: string;
    is_base: string;
    per_page: string;
}

export interface DepotUserAssignment {
    user_id: string;
    role: string;
}

export interface DepotDetail {
    id: number;
    name: string;
    address: string | null;
    branch_id: number | null;
    department_id: number | null;
    pharmacy_id: number | null;
    parent_depot_id: number | null;
    branch_name: string | null;
    department_name: string | null;
    pharmacy_name: string | null;
    parent_depot_name: string | null;
    is_active: boolean;
    is_base: boolean;
    users: Array<{ id: number; full_name: string; email: string; role: string }>;
    assignments: DepotUserAssignment[];
}

export interface DepotActiveOption extends OptionItem {
    pharmacy_id?: number | null;
    parent_depot_id?: number | null;
    branch_id?: number | null;
    department_id?: number | null;
    branch_name?: string | null;
    department_name?: string | null;
    pharmacy_name?: string | null;
}

export interface DepotSourceOption {
    id: number;
    name: string;
}

export interface DepotFormData {
    branches: OptionItem[];
    departments: OptionItem[];
    pharmacies: OptionItem[];
    depots: OptionItem[];
    activeDepots: DepotActiveOption[];
    medicines: OptionItem[];
    tools: OptionItem[];
    units: OptionItem[];
    users: Array<{ id: number; full_name: string; email: string }>;
    roles: string[];
}

export interface DepotTransactionListItem {
    id: number;
    transaction_number: string | null;
    type: string;
    status: string;
    quantity: number;
    item_name: string | null;
    item_type: string | null;
    source_name: string | null;
    destination_name: string | null;
    transaction_date: string | null;
    created_by_name: string | null;
}

export interface DepotRequestLineDetail {
    id: number;
    medicine_id: number | null;
    tool_id: number | null;
    unit_id: number | null;
    item_type: string | null;
    item_name: string;
    quantity: number;
    unit_name: string | null;
    batch_number: string | null;
    transaction_id: number | null;
    transaction_number: string | null;
}

export interface DepotRequestListItem {
    id: number;
    request_number: string | null;
    status: string;
    destination_type: 'depot' | 'pharmacy';
    items_count: number;
    total_quantity: number;
    items_summary: string;
    requesting_depot_name: string | null;
    pharmacy_id: number | null;
    pharmacy_name: string | null;
    destination_name: string | null;
    source_depot_name: string | null;
    requested_by_name: string | null;
    branch_name: string | null;
    department_name: string | null;
    pharmacy_depot_label: string | null;
    request_user_name: string | null;
    created_at: string | null;
}

export interface DepotRequestDetail extends DepotRequestListItem {
    requesting_depot_id: number | null;
    source_depot_id: number;
    notes: string | null;
    workflow_rank: number;
    rejection_reason: string | null;
    approved_by_name: string | null;
    fulfilled_by_name: string | null;
    approved_at: string | null;
    fulfilled_at: string | null;
    items: DepotRequestLineDetail[];
    transfers: Array<{ id: number; transaction_number: string | null }>;
    status_logs: Array<{
        from_status: string;
        to_status: string;
        notes: string | null;
        user_name: string | null;
        created_at: string | null;
    }>;
}

export type PaginatedDepots = PaginatedResult<DepotListItem>;
export type PaginatedDepotTransactions = PaginatedResult<DepotTransactionListItem>;
export type PaginatedDepotRequests = PaginatedResult<DepotRequestListItem>;

export type DepotCrudPermissions = SettingsPermissions;

export type DepotNavPermissions = Partial<Record<'index' | 'transactions' | 'requests' | 'reports' | 'tools', boolean>>;
