import { OptionItem, PaginationLink } from './settings';

export type DentistRegistrationStatus = 'pending' | 'in_progress' | 'completed' | 'cancelled';
export type DentalTreatmentStatus = 'planned' | 'in_progress' | 'completed' | 'cancelled';

export interface DentistRegistrationListItem {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    appointment_date: string | null;
    dentist_name: string | null;
    branch_name: string | null;
    registration_date: string | null;
    status: DentistRegistrationStatus;
}

export interface DentistRegistrationStats {
    total: number;
    pending: number;
    in_progress: number;
    completed: number;
    cancelled: number;
}

export interface DentistRegistrationFilters {
    search: string;
    status: string;
    branch_id: string;
    dentist_id: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

export interface DentistRegistrationFilterOptions {
    branches: OptionItem[];
    dentists: OptionItem[];
}

export interface PaginatedDentistRegistrations {
    data: DentistRegistrationListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    links: PaginationLink[];
}

export interface DentistRegistrationListPermissions {
    edit: boolean;
    delete: boolean;
}

export interface DentalTreatmentItem {
    id: number;
    treatment_type: string;
    tooth_number: number | null;
    treatment_description: string;
    treatment_date: string | null;
    status: DentalTreatmentStatus;
    cost: string | number | null;
    notes: string | null;
}

export interface DentalXrayItem {
    id: number;
    xray_type: string;
    xray_date: string | null;
    file_url: string | null;
    description: string | null;
    notes: string | null;
}

export interface DentalNoteItem {
    id: number;
    note_date: string | null;
    note_type: string;
    content: string;
}

export interface DentalChartEntry {
    id: number;
    tooth_number: number;
    tooth_condition: string | null;
    gum_health: string | null;
    oral_hygiene_score: string | number | null;
    pocket_depth: string | number | null;
    bleeding: boolean;
    mobility: string | null;
    chart_date: string | null;
    legacy_edit_url: string;
}

export interface DentistRegistrationDetail {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    dentist_id: number | null;
    dentist_name: string | null;
    branch_name: string | null;
    registration_date: string | null;
    status: DentistRegistrationStatus;
    notes: string | null;
    appointment_id: number | null;
    appointment_date: string | null;
    appointment_completed: boolean;
    treatments: DentalTreatmentItem[];
    xrays: DentalXrayItem[];
    dental_notes: DentalNoteItem[];
    chart_entries: DentalChartEntry[];
    counts: {
        treatments: number;
        xrays: number;
        notes: number;
        charts: number;
        prescriptions: number;
    };
    created_at: string | null;
}

export interface DentistRegistrationFormOptions {
    dentists: OptionItem[];
}

export interface DentistRegistrationShowPermissions {
    edit: boolean;
    delete: boolean;
    manageTreatments: boolean;
    manageXrays: boolean;
    manageNotes: boolean;
    markStatus: boolean;
}
