import { NamedOption } from './patient';

export interface AppointmentListItem {
    id: number;
    patient_id: number | null;
    id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
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
