import { OptionItem, PaginationLink } from './settings';

export type HemodialysisSessionStatus = 'pending' | 'in_progress' | 'completed' | 'cancelled';
export type VascularAccessType = 'av_fistula' | 'graft' | 'catheter' | null;

export interface HemodialysisSessionListItem {
    id: number;
    ref_no: string | number | null;
    patient_identifier: string | number | null;
    patient_name: string | null;
    diagnosis: string | null;
    session_date: string | null;
    session_time: string | null;
    duration_minutes: number | null;
    doctor_name: string | null;
    status: HemodialysisSessionStatus;
}

export interface HemodialysisSessionStats {
    total: number;
    pending: number;
    in_progress: number;
    completed: number;
    cancelled: number;
}

export interface HemodialysisSessionFilters {
    patient_id: string;
    patient_name: string;
    session_date: string;
    date_from: string;
    date_to: string;
    doctor_id: string;
    status: string;
    per_page: string;
}

export interface HemodialysisSessionFilterOptions {
    doctors: OptionItem[];
}

export interface PaginatedHemodialysisSessions {
    data: HemodialysisSessionListItem[];
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

export interface HemodialysisSessionListPermissions {
    edit: boolean;
    delete: boolean;
}

export interface HemodialysisSessionDetail {
    id: number;
    ref_no: string | number | null;
    status: HemodialysisSessionStatus;
    patient_id: number;
    patient_identifier: string | number | null;
    patient_name: string | null;
    nephrology_registration_id: number | null;
    nephrology_registration_ref_no: string | number | null;
    doctor_id: number | null;
    doctor_name: string | null;
    diagnosis: string | null;
    dialysis_schedule: string | null;
    session_date: string | null;
    session_time: string | null;
    duration_minutes: number | null;
    vascular_access_type: VascularAccessType;
    pre_blood_pressure: string | null;
    pre_weight: string | number | null;
    pre_pulse: number | null;
    pre_temperature: string | number | null;
    post_blood_pressure: string | null;
    post_weight: string | number | null;
    post_pulse: number | null;
    post_temperature: string | number | null;
    fluid_removed_ml: string | number | null;
    dialyzer_type: string | null;
    blood_type: string | null;
    complications_notes: string | null;
    branch_name: string | null;
}

export interface HemodialysisSessionFormData extends HemodialysisSessionDetail {
    patient_label: string | null;
    nephrology_registration_label: string | null;
    default_diagnosis: string | null;
}

export interface HemodialysisSessionFormOptions {
    doctors: OptionItem[];
}

export interface HemodialysisSessionPrefill {
    patient: { id: number; name: string; identifier: string | number } | null;
    registration: { id: number; ref_no: string | number; diagnosis: string | null } | null;
}
