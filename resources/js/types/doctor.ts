export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface DoctorListItem {
    id: number;
    name: string;
    qualification: string | null;
    contact_number: string | null;
    department_name: string | null;
    branch_name: string | null;
    specialization: string | null;
    gender: string | null;
    clinic_type: string | null;
    active_status: boolean;
    is_dentist: boolean;
    is_nephrologist: boolean;
}

export interface PaginatedDoctors {
    data: DoctorListItem[];
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

export interface DoctorIndexStats {
    active: number;
    inactive: number;
    total: number;
    dentists: number;
}

export interface DoctorIndexFilters {
    search: string;
    department_id: string;
    branch_id: string;
    gender: string;
    clinic_type: string;
    active_status: string;
    join_date_from: string;
    join_date_to: string;
    per_page: string;
}

export interface DoctorIndexFilterOptions {
    departments: Array<{ id: number; name: string }>;
    branches: Array<{ id: number; name: string }>;
}

export interface DoctorIndexPermissions {
    create: boolean;
    edit: boolean;
    delete: boolean;
    toggleStatus: boolean;
}

export interface DoctorIndexUrls {
    index: string;
    create: string;
    show: string;
    edit: string;
    destroy: string;
    updateStatus: string;
}

export interface DoctorUserOption {
    id: number;
    name: string;
    last_name: string | null;
    email: string;
}

export interface DoctorFormData {
    departments: Array<{ id: number; name: string }>;
    doctorUsers: DoctorUserOption[];
    clinicTypes: Array<{ value: string; label_key: string }>;
    genders: Array<{ value: string; label_key: string }>;
}

export interface DoctorFormValues {
    id: number;
    name: string;
    father_name: string;
    gender: string;
    contact_number: string;
    address: string;
    specialization: string;
    qualification: string;
    room_no: string;
    clinic_type: string;
    join_date: string;
    department_id: string;
    user_id: string;
    active_status: boolean;
    is_dentist: boolean;
    is_nephrologist: boolean;
}

export interface DoctorFormUrls {
    index: string;
    store: string;
    update: string;
    back: string;
}

export interface DoctorLinkedUser {
    id: number;
    name: string;
    email: string;
    roles: Array<{ id: number; name: string }>;
    permissions: Array<{ id: number; name: string }>;
}

export interface DoctorDetail {
    id: number;
    name: string;
    father_name: string | null;
    gender: string | null;
    contact_number: string | null;
    address: string | null;
    specialization: string | null;
    qualification: string | null;
    room_no: string | null;
    clinic_type: string | null;
    join_date: string | null;
    active_status: boolean;
    is_dentist: boolean;
    is_nephrologist: boolean;
    department_name: string | null;
    branch_name: string | null;
    linked_user: DoctorLinkedUser | null;
}

export interface DoctorShowPermissions {
    edit: boolean;
    delete: boolean;
}

export interface DoctorShowUrls {
    index: string;
    edit: string;
    destroy: string;
}
