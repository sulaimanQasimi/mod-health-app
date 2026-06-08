import { OptionItem, PaginatedResult } from './settings';

export interface PrescriptionIndexItem {
    id: number;
    patient_id: number;
    patient_name: string;
    card_number: string | null;
    father_name: string | null;
    doctor_name: string;
    token_number: string | null;
    token_date: string | null;
    department_name: string | null;
    is_completed: boolean;
    created_at: string | null;
}

export interface PrescriptionIndexFilters {
    patient_name: string;
    card_number: string;
    father_name: string;
    patient_id: string;
    token_filter: string;
    doctor_id: string;
    status: string;
    date_from: string;
    date_to: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

export interface PrescriptionIndexPermissions {
    view: boolean;
    edit: boolean;
    delete: boolean;
    export: boolean;
}

export interface PrescriptionIndexUrls {
    index: string;
    delivered: string;
    show: string;
    bulkUpdateStatus: string;
    bulkDelete: string;
    export: string;
    thermalReceipt: string;
    scanCode: string;
    current: string;
}

export interface PrescriptionAlternativeItem {
    id: number;
    medicine_name: string;
    medicine_type: string | null;
    usage_type_name: string | null;
    dosage: string;
    frequency: string;
    amount: string;
    notes: string | null;
    is_delivered: boolean;
    is_selected: boolean;
}

export interface PrescriptionShowItem {
    id: number;
    medicine_name: string;
    medicine_type: string | null;
    usage_type_name: string | null;
    dosage: string;
    frequency: string;
    amount: string;
    is_delivered: boolean;
    medicine_type_id: number | null;
    usage_type_id: number | null;
    medicine_id: number | null;
    selected_alternative: PrescriptionAlternativeItem | null;
    alternatives_count: number;
    alternatives: PrescriptionAlternativeItem[];
}

export interface PrescriptionDetail {
    id: number;
    patient_id: number;
    patient_name: string;
    doctor_name: string;
    pharmacy_name: string | null;
    is_completed: boolean;
    created_at: string | null;
    items: PrescriptionShowItem[];
}

export interface PrescriptionFormOptions {
    medicines: OptionItem[];
    medicineTypes: { id: number; type: string }[];
    medicineUsageTypes: OptionItem[];
}

export interface PrescriptionShowPermissions {
    edit: boolean;
    delete: boolean;
    manageItems: boolean;
}

export interface PrescriptionShowUrls {
    index: string;
    delivered: string;
    updateStatus: string;
    markAllDelivered: string;
    destroy: string;
    thermalReceipt: string;
    itemsBase: string;
    alternativesBase: string;
    addAlternative: string;
}

export type PaginatedPrescriptions = PaginatedResult<PrescriptionIndexItem>;
