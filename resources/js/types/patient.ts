export interface NamedOption {
    id: number;
    name?: string;
    name_dr?: string;
}

export interface PatientFormData {
    branchId: number;
    clinicType: string | null;
    registrationDate: string;
    provinces: NamedOption[];
    recipients: NamedOption[];
    relations: NamedOption[];
    militeryTypes: NamedOption[];
    departments: NamedOption[];
}

export interface PatientCreateUrls {
    store: string;
    districts: string;
    doctorsByDepartment: string;
    back: string;
}

export type PatientType = '0' | '1' | '2';

export interface DoctorOption {
    id: number;
    name: string;
}

export interface StorePatientResponse {
    success: boolean;
    message: string;
    patient?: {
        id: number;
        name: string;
        last_name: string | null;
    };
    appointment?: {
        id: number;
        department: string;
        doctor: string;
        date: string;
        time: string;
        token_url: string;
    };
    errors?: Record<string, string[]>;
}

export interface PatientListItem {
    id: number;
    id_card: string | null;
    name: string;
    last_name: string | null;
    father_name: string | null;
    location: string;
    age: string | null;
    militery_type: string | null;
    phone: string | null;
    created_by: string | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedPatients {
    data: PatientListItem[];
    links: PaginationLink[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
}

export interface PatientIndexFilters {
    name: string;
    father_name: string;
    last_name: string;
    phone: string;
    card_search: string;
    militery_type_id: string;
    province_id: string;
    gender: string;
    job_category: string;
}

export interface PatientIndexFilterOptions {
    militeryTypes: NamedOption[];
    provinces: NamedOption[];
}

export interface PatientIndexUrls {
    index: string;
    create: string;
    show: string;
    edit: string;
}

export interface PatientIndexPermissions {
    create: boolean;
    edit: boolean;
}
