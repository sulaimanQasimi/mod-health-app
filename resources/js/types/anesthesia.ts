import { PaginationLink, PaginationMeta } from './settings';

export type AnesthesiaListVariant = 'new' | 'approved' | 'rejected';

export interface SelectOption {
    id: number;
    name: string;
}

export interface AnesthesiaListItem {
    id: number;
    row_number: number | null;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    operation_type_name: string | null;
    surgion_name: string | null;
    anesthesia_type: string | null;
    date: string | null;
    time: string | null;
    status: string;
    urls: { show: string };
}

export interface PaginatedAnesthesias {
    data: AnesthesiaListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface AnesthesiaListFilters {
    search: string;
    operation_type_id: string;
    department_id: string;
    anesthesia_type: string;
    date_from: string;
    date_to: string;
    per_page: string;
}

export interface AnesthesiaListUrls {
    current: string;
    new: string;
    approved: string;
    rejected: string;
    report: string;
}

export interface AnesthesiaReportItem {
    id: number;
    patient_name: string | null;
    doctor_name: string | null;
    branch_name: string | null;
    status: string;
    anesthesia_type: string | null;
    date: string | null;
    time: string | null;
}

export interface AnesthesiaReportFilters {
    patient_name: string;
    status: string;
    doctor_id: string;
    anesthesia_type: string;
    operation_type_id: string;
    department_id: string;
    time: string;
    from: string;
    to: string;
}

export interface AnesthesiaDoctorOption {
    id: number;
    name: string;
}

export interface AnesthesiaNurseOption {
    id: number;
    name: string;
}

export interface AnesthesiaDetail {
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
    date: string | null;
    time: string | null;
    appointment_id: number | null;
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
    room_name: string | null;
    bed_number: string | number | null;
    operation_anesthesia_log_id: number | null;
    operation_anesthesist_id: number | null;
}

export interface AnesthesiaEditForm {
    id: number;
    plan: string;
    other_problems: string;
    anesthesia_plan: string;
    position_on_bed: string;
    planned_duration: string;
    estimated_blood_waste: string;
    date: string;
    time: string;
    operation_type_id: string;
    anesthesia_type: string;
    operation_surgion_id: string;
    operation_assistants_id: string[];
    operation_anesthesia_log_id: string;
    operation_anesthesist_id: string;
    operation_scrub_nurse_id: string;
    operation_circulation_nurse_id: string;
    patient_id: number;
    appointment_id: number;
    doctor_id: number;
    branch_id: number;
}

export interface AnesthesiaShowPermissions {
    edit: boolean;
    delete: boolean;
    approve: boolean;
    reject: boolean;
}
