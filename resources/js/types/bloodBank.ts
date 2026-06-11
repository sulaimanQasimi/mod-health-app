import { PaginationLink, PaginationMeta } from './settings';

export type BloodRequestListVariant = 'new' | 'approved' | 'rejected' | 'delivered';

export interface BloodBankListUrls {
    dashboard: string;
    new: string;
    approved: string;
    rejected: string;
    delivered: string;
    inventory: string;
    movements: string;
    branchTransfers: string;
    report: string;
    current?: string;
}

export interface BloodRequestListItem {
    id: number;
    row_number?: number | null;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    department_name: string | null;
    group: string | null;
    rh: string | null;
    type: string | null;
    quantity: number | null;
    status: string;
    created_at: string | null;
    urls: { show: string };
}

export interface PaginatedBloodRequests {
    data: BloodRequestListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface BloodRequestListFilters {
    q: string;
    department_id: string;
    group: string;
    rh: string;
    type: string;
    from: string;
    to: string;
    per_page: string;
}

export interface BloodRequestFilterOptions {
    departments: { id: number; name: string }[];
    bloodGroups: string[];
    bloodComponentTypes: string[];
}

export interface BloodRequestDetail {
    id: number;
    status: string;
    group: string | null;
    rh: string | null;
    type: string | null;
    quantity: number | null;
    reject_reason: string | null;
    created_at: string | null;
    patient: {
        name: string | null;
        father_name: string | null;
        id_card: string | null;
        phone: string | null;
    };
    department_name: string | null;
    receiver_department_name: string | null;
    receiver_nurse_name: string | null;
    created_by_name: string | null;
    appointment_id: number | null;
    requested_qty: number;
    reserved_compatible_qty: number;
    issued_qty: number;
    remaining_qty: number;
    quantity_inferred_from_volume_ml: boolean;
    order_quantity_display: {
        mode: 'empty' | 'units' | 'volume_ml';
        ml?: number;
        units?: number;
    };
    workflow: {
        current_step: number | null;
        steps: { number: number; done: boolean; current: boolean }[];
    };
    blood_check: {
        patient_typed_group: string | null;
        patient_typed_rh: string | null;
        verified_at: string | null;
        verified_by_name: string | null;
    } | null;
    patient_samples: {
        id: number;
        sample_id: string | null;
        collected_at: string | null;
        collected_by_name: string | null;
        notes: string | null;
    }[];
    crossmatches: {
        id: number;
        blood_unit_id: number;
        bag_number: string | null;
        major_result: string | null;
        minor_result: string | null;
        status: string;
        is_reserved: boolean;
        tested_at: string | null;
        tested_by_name: string | null;
    }[];
    issued_units: {
        id: number;
        bag_number: string | null;
        issued_at: string | null;
    }[];
}

export interface BloodUnitListItem {
    id: number;
    row_number?: number | null;
    bag_number: string | null;
    blood_group: string | null;
    rh: string | null;
    component_type: string | null;
    status: string;
    expires_at: string | null;
    created_at: string | null;
    urls: { show: string };
}

export interface PaginatedBloodUnits {
    data: BloodUnitListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface BloodInventoryFilters {
    status: string;
    blood_group: string;
    rh: string;
    component_type: string;
    q: string;
    expires_within: string;
    sort: string;
    per_page: string;
}

export interface BloodInventoryFilterOptions {
    bloodGroups: string[];
    bloodComponentTypes: string[];
    statuses: string[];
}

export interface BloodStockMovementItem {
    id: number;
    row_number?: number | null;
    movement_type: string;
    bag_number: string | null;
    user_name: string | null;
    notes: string | null;
    created_at: string | null;
}

export interface PaginatedBloodMovements {
    data: BloodStockMovementItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface BloodMovementFilters {
    movement_type: string;
    from: string;
    to: string;
    bag_number: string;
    per_page: string;
}

export interface BloodReportItem {
    id: number;
    patient_name: string | null;
    department_name: string | null;
    branch_name: string | null;
    status: string;
    group: string | null;
    rh: string | null;
    appointment_id: number | null;
}

export interface BloodReportFilters {
    patient_name: string;
    status: string;
    group: string;
    rh: string;
    department_id: string;
    from: string;
    to: string;
}

export interface BloodReportFilterOptions {
    departments: { id: number; name: string }[];
    bloodGroups: string[];
    statuses: string[];
}

export interface BloodUnitReceiveForm {
    donor_record_department: boolean;
    department_id: string;
    donor_name: string;
    donor_father_name: string;
    donor_age: string;
    donor_gender: string;
    donor_phone: string;
    donor_national_id: string;
    donor_blood_pressure: string;
    donor_type: string;
    donor_military_department: string;
    donor_comorbidities: string;
    donor_receiver: string;
    phlebotomy_at: string;
    blood_group: string;
    rh: string;
    component_type: string;
    bag_number: string;
    volume_ml: string;
    collected_at: string;
    expires_date: string;
    expires_time: string;
    notes: string;
}
