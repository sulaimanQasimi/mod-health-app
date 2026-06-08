import { OptionItem, PaginationLink } from './settings';

export type NephrologyRegistrationStatus = 'pending' | 'in_progress' | 'completed' | 'cancelled';
export type DialysisType = 'HD' | 'PD' | 'CRRT' | null;
export type AccessType = 'av_fistula' | 'graft' | 'catheter' | null;

export interface NephrologyRegistrationListItem {
    id: number;
    ref_no: string | number | null;
    patient_identifier: string | number | null;
    patient_name: string | null;
    father_name: string | null;
    phone: string | null;
    age: string | number | null;
    gender: string | number | null;
    visit_date: string | null;
    doctor_name: string | null;
    status: NephrologyRegistrationStatus;
    diagnosis: string | null;
    needs_acceptance: boolean;
}

export interface NephrologyRegistrationStats {
    total: number;
    pending: number;
    in_progress: number;
    completed: number;
    cancelled: number;
}

export interface NephrologyRegistrationFilters {
    patient_id: string;
    patient_name: string;
    status: string;
    branch_id: string;
    doctor_id: string;
    visit_date_from: string;
    visit_date_to: string;
    per_page: string;
}

export interface NephrologyRegistrationFilterOptions {
    branches: OptionItem[];
    doctors: OptionItem[];
}

export interface PaginatedNephrologyRegistrations {
    data: NephrologyRegistrationListItem[];
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

export interface NephrologyRegistrationListPermissions {
    accept: boolean;
}

export interface HemodialysisSessionItem {
    id: number;
    ref_no: string | number | null;
    session_date: string | null;
    duration_minutes: number | null;
    doctor_name: string | null;
    status: string;
    show_url: string;
}

export interface NephrologyRegistrationDetail {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    doctor_id: number | null;
    doctor_name: string | null;
    branch_name: string | null;
    visit_date: string | null;
    status: NephrologyRegistrationStatus;
    chief_complaint: string | null;
    diagnosis: string | null;
    disease_id: number | null;
    disease_name: string | null;
    disease_category_id: number | null;
    disease_category_name: string | null;
    ckd_aki_stage: string | null;
    dialysis_required: boolean;
    dialysis_type: DialysisType;
    access_type: AccessType;
    notes: string | null;
    follow_up_plan: string | null;
    appointment_id: number | null;
    patient_id: number | null;
    needs_acceptance: boolean;
    hemodialysis_sessions: HemodialysisSessionItem[];
    counts: {
        diagnoses: number;
        lab_tests: number;
        prescriptions: number;
        hemodialysis: number;
    };
}

export interface NephrologyDiseaseOption {
    id: number;
    name: string;
    disease_category_id: number | null;
}

export interface NephrologyRegistrationFormOptions {
    doctors: OptionItem[];
    disease_categories: OptionItem[];
    diseases: NephrologyDiseaseOption[];
    has_uncategorized_diseases: boolean;
}

export interface NephrologyRegistrationShowPermissions {
    edit: boolean;
    delete: boolean;
    markStatus: boolean;
}
