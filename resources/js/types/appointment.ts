import { NamedOption } from './patient';

export interface AppointmentListItem {
    id: number;
    patient_id: number | null;
    id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    phone: string | null;
    doctor_name: string | null;
    department_name: string | null;
    date: string | null;
    time: string | null;
    is_completed: boolean;
    processed_by: string | null;
    permissions: {
        view: boolean;
        history: boolean;
        edit: boolean;
        delete: boolean;
    };
}

export interface TrashedAppointmentItem {
    id: number;
    patient_id: number | null;
    id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    doctor_name: string | null;
    department_name: string | null;
    date: string | null;
    time: string | null;
    deleted_at: string | null;
    permissions: {
        restore: boolean;
    };
}

export interface AppointmentFormValues {
    id: number;
    patient_id: number;
    patient_name: string | null;
    id_card: string | null;
    father_name: string | null;
    department_id: number | null;
    department_name: string | null;
    doctor_id: string;
    doctor_name: string | null;
    branch_id: number;
    clinic_type: string;
    date: string;
    time: string;
    refferal_remarks: string;
    is_completed: boolean;
    processed_by: boolean;
    doctor_reassigned: boolean;
    can_change_doctor: boolean;
}

export interface AppointmentEditFormData {
    clinicType: string;
    doctorsByDepartment: string;
}

export interface AppointmentEditPermissions {
    delete: boolean;
}

export interface AppointmentEditUrls {
    index: string;
    update: string;
    destroy: string;
    show: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedAppointments {
    data: AppointmentListItem[];
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

export interface PaginatedTrashedAppointments {
    data: TrashedAppointmentItem[];
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

export interface AppointmentIndexFilters {
    patient_name: string;
    id_card: string;
    patient_id: string;
    father_name: string;
    phone: string;
    doctor_id: string;
    department_id: string;
    is_completed: string;
    date_from: string;
    date_to: string;
}

export interface TrashedAppointmentFilters {
    patient_name: string;
    id_card: string;
}

export interface AppointmentIndexFilterOptions {
    doctors: NamedOption[];
    departments: NamedOption[];
}

export interface AppointmentIndexPermissions {
    create: boolean;
    view: boolean;
    edit: boolean;
    delete: boolean;
    restore: boolean;
    updateStatus: boolean;
}

export interface AppointmentIndexUrls {
    index: string;
    trashed: string;
    show: string;
    edit: string;
    destroy: string;
    patientHistory: string;
    patientsIndex: string;
    patientsCreate: string;
}

export interface TrashedAppointmentUrls {
    index: string;
    trashed: string;
    restore: string;
}

export interface DoctorOption {
    id: number;
    name: string;
}

export interface DepartmentAppointmentItem {
    id: number;
    patient_id: number | null;
    department_id: number | null;
    id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    department_name: string | null;
    date: string | null;
    time: string | null;
    is_accepted: boolean;
    refferal_remarks: string | null;
    referring_doctor_name: string | null;
    permissions: {
        accept: boolean;
        changeDepartment: boolean;
        view: boolean;
        history: boolean;
    };
}

export interface DoctorAppointmentItem {
    id: number;
    patient_id: number | null;
    id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    doctor_name: string | null;
    referring_doctor_name: string | null;
    date: string | null;
    time: string | null;
    permissions: {
        view: boolean;
        history: boolean;
    };
}

export interface DepartmentAppointmentFilters {
    search: string;
    token_id: string;
    patient_id: string;
}

export interface MyVisitAppointmentFilters {
    token_id: string;
    patient_id: string;
    patient_name?: string;
}

export interface MyVisitPermissions {
    view: boolean;
    history: boolean;
}

export interface MyVisitUrls {
    department: string;
    doctor: string;
    completed: string;
    show: string;
    patientHistory: string;
    accept: string;
    changeDepartment: string;
}

export interface PaginatedDepartmentAppointments {
    data: DepartmentAppointmentItem[];
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

export interface PaginatedDoctorAppointments {
    data: DoctorAppointmentItem[];
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

export interface AppointmentReportFilters {
    patient_name: string;
    doctor_id: string;
    processed_by: string;
    registered_by: string;
    is_completed: string;
    start: string;
    end: string;
    time: string;
    clinic_type: string;
    job: string;
    job_type: string;
    gender: string;
    rank: string;
    relation_id: string;
    province_id: string;
    district_id: string;
    per_page: string;
}

export interface AppointmentReportSummary {
    total: number;
    completed: number;
    ongoing: number;
}

export interface AppointmentReportItem {
    id: number;
    patient_name: string | null;
    doctor_name: string | null;
    branch_name: string | null;
    clinic_type: string | null;
    processed_by_name: string | null;
    registered_by_name: string | null;
    job: string | null;
    job_type: string | null;
    gender: string | number | null;
    rank: string | null;
    relation_name: string | null;
    province_name: string | null;
    district_name: string | null;
    is_completed: boolean;
    date: string | null;
    time: string | null;
    urls: { show: string };
}

export interface AppointmentReportFilterOptions {
    doctors: Array<{ id: number; name: string }>;
    users: Array<{ id: number; name: string; last_name?: string | null }>;
    provinces: Array<{ id: number; name_dr: string | null }>;
    districts: Array<{ id: number; name_dr: string | null; province_id: number }>;
    relations: Array<{ id: number; name: string }>;
}

export interface PaginatedAppointmentReport {
    data: AppointmentReportItem[];
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

export interface AppointmentReportUrls {
    current: string;
    index: string;
    export: string;
}
