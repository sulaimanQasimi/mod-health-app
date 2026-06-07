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
    };
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

export interface AppointmentIndexFilterOptions {
    doctors: NamedOption[];
    departments: NamedOption[];
}

export interface AppointmentIndexPermissions {
    create: boolean;
    view: boolean;
    updateStatus: boolean;
}

export interface AppointmentIndexUrls {
    index: string;
    show: string;
    patientHistory: string;
    patientsIndex: string;
    patientsCreate: string;
}
