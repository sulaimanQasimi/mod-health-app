import { PaginationLink, PaginationMeta } from './settings';

export type OperationListVariant = 'new' | 'approved' | 'reserved' | 'completed';

export interface SelectOption {
    id: number;
    name: string;
}

export interface OperationListItem {
    id: number;
    row_number: number | null;
    patient_id?: number | null;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    operation_type_name: string | null;
    date: string | null;
    time: string | null;
    is_operation_approved: boolean;
    is_operation_done: boolean;
    is_reserved: boolean;
    reserve_reason?: string | null;
    status: string;
    scrub_nurse_name?: string | null;
    circulation_nurse_name?: string | null;
    urls: { show: string };
}

export interface PaginatedOperations {
    data: OperationListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface OperationListFilters {
    search: string;
    branch_id: string;
    department_id: string;
    operation_type_id: string;
    surgeon_id: string;
    date_from: string;
    date_to: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

export interface OperationListUrls {
    current?: string;
    new: string;
    approved: string;
    reserved: string;
    completed: string;
    report: string;
}

export interface OperationReportItem {
    id: number;
    patient_name: string | null;
    surgion_name: string | null;
    operation_type_name: string | null;
    date: string | null;
    time: string | null;
    is_operation_done: boolean;
    is_operation_approved: boolean;
    is_reserved: boolean;
}

export interface OperationReportFilters {
    patient_name: string;
    surgeon_id: string;
    operation_status: string;
    operation_approval: string;
    reserve_status: string;
    operation_type_id: string;
    date_from: string;
    date_to: string;
}

export interface OperationNurseOption {
    id: number;
    name: string;
}

export interface OperationDetail {
    id: number;
    status: string;
    plan: string | null;
    anesthesia_plan: string | null;
    anesthesia_log_reply: string | null;
    position_on_bed: string | null;
    planned_duration: string | null;
    estimated_blood_waste: string | null;
    other_problems: string | null;
    anesthesia_type: string | null;
    operation_remark: string | null;
    operation_expense_remarks: string | null;
    reserve_reason: string | null;
    patient_status: string | null;
    operation_result: number | null;
    date: string;
    date_display: string | null;
    time: string | null;
    appointment_id: number | null;
    is_operation_approved: boolean;
    is_operation_done: boolean;
    is_reserved: boolean;
    is_referred_to_operation: boolean;
    operation_scrub_nurse_id: number | null;
    operation_circulation_nurse_id: number | null;
    patient: {
        id: number;
        name: string | null;
        father_name: string | null;
        id_card: string | null;
        phone: string | null;
    } | null;
    operation_type_name: string | null;
    doctor_name: string | null;
    surgion_name: string | null;
    anesthesist_name: string | null;
    anesthesia_log_name: string | null;
    scrub_nurse_name: string | null;
    circulation_nurse_name: string | null;
    department_name: string | null;
    operation_assistants_names: string[];
}

export interface OperationLinkedHospitalization {
    id: number;
    reason: string;
    remarks: string;
    department_id: string;
    room_id: string;
    bed_id: string;
    is_active: boolean;
}

export interface OperationShowPermissions {
    prescription: boolean;
    hospitalize: boolean;
}
