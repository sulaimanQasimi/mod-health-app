import { PaginationLink, PaginationMeta } from './settings';

export type IcuListVariant = 'new' | 'approved' | 'rejected';

export type IcuDischargeFilter =
    | 'all'
    | 'in_icu'
    | 'discharged'
    | 'recovered'
    | 'died'
    | 'moved';

export interface IcuListItem {
    id: number;
    row_number: number | null;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    description: string | null;
    status: string;
    is_discharged: boolean;
    discharge_status: string | null;
    created_at: string | null;
    urls: { show: string };
}

export interface PaginatedIcus {
    data: IcuListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface IcuListFilters {
    search: string;
    patient_name: string;
    card_number: string;
    father_name: string;
    per_page: string;
    discharge_filter?: string;
}

export interface IcuListUrls {
    current: string;
    new: string;
    approved: string;
    rejected: string;
    report: string;
}

export interface IcuReportItem {
    id: number;
    patient_name: string | null;
    doctor_name: string | null;
    branch_name: string | null;
    status: string;
    created_at: string | null;
}

export interface IcuReportFilters {
    patient_name: string;
    status: string;
    date_from: string;
    date_to: string;
}

export interface IcuDetail {
    id: number;
    description: string | null;
    status: string;
    icu_enterance_note: string | null;
    icu_reject_reason: string | null;
    discharge_status: string | null;
    discharge_remark: string | null;
    is_discharged: boolean;
    discharged_at: string | null;
    cause_of_death: string | null;
    death_date: string | null;
    death_time: string | null;
    brief_history: string | null;
    transfer_date: string | null;
    created_at: string | null;
    appointment_id: number | null;
    patient: {
        id: number;
        name: string | null;
        last_name: string | null;
        father_name: string | null;
        id_card: string | null;
        phone: string | null;
    } | null;
    doctor_name: string | null;
    branch_name: string | null;
    department_name: string | null;
    room_name: string | null;
    bed_number: string | number | null;
}

export interface IcuShowPermissions {
    edit: boolean;
    delete: boolean;
    approve: boolean;
    reject: boolean;
}
