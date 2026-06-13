import { PaginationLink, PaginationMeta } from './settings';

export type PacuListVariant = 'new' | 'completed';

export interface PacuListItem {
    id: number;
    row_number: number | null;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    description: string | null;
    status: string;
    created_at: string | null;
    urls: { show: string };
}

export interface PaginatedPacus {
    data: PacuListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface PacuListFilters {
    search: string;
    patient_name: string;
    card_number: string;
    father_name: string;
    per_page: string;
}

export interface PacuListUrls {
    current: string;
    new: string;
    completed: string;
    report: string;
}

export interface PacuReportItem {
    id: number;
    patient_name: string | null;
    branch_name: string | null;
    status: string;
    created_at: string | null;
}

export interface PacuReportFilters {
    patient_name: string;
    status: string;
    date_from: string;
    date_to: string;
}

export interface PacuVisitItem {
    id: number;
    description: string | null;
    department_name: string | null;
    doctor_name: string | null;
}

export interface PacuDetail {
    id: number;
    description: string | null;
    status: string;
    created_at: string | null;
    appointment_id: number | null;
    patient: {
        id: number;
        name: string | null;
        last_name: string | null;
        father_name: string | null;
        id_card: string | null;
        phone: string | null;
        nid: string | null;
        province_name: string | null;
        district_name: string | null;
        recipient_name: string | null;
        patient_created_at: string | null;
        image: string | null;
    } | null;
    department_name: string | null;
    branch_name: string | null;
    visits: PacuVisitItem[];
}

export interface PacuShowPermissions {
    complete: boolean;
    add_visit: boolean;
}
