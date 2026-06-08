import { PaginationLink } from './appointment';

export type LaboratoryListMode = 'pending' | 'in_progress' | 'completed';

export type LaboratoryStatus = 'pending' | 'in_progress' | 'completed' | 'cancelled';

export type LaboratoryPriority = 'normal' | 'urgent' | 'stat';

export interface LaboratoryRegistrationPermissions {
    accept: boolean;
    enterResults: boolean;
    markCompleted: boolean;
    cancel: boolean;
    print: boolean;
}

export interface LaboratoryRegistrationUrls {
    accept: string;
    enterResults: string;
    print: string;
    markCompleted: string;
    cancel: string;
}

export interface LaboratoryRegistrationItem {
    id: number;
    ref_no: string;
    lab_type_name: string | null;
    category_name: string | null;
    is_parametered: boolean;
    status: LaboratoryStatus;
    priority: LaboratoryPriority;
    doctor_name: string | null;
    assigned_to_name: string | null;
    date: string | null;
    registration_date: string | null;
    completed_at: string | null;
    permissions: LaboratoryRegistrationPermissions;
    urls: LaboratoryRegistrationUrls;
}

export interface LaboratoryPatientGroup {
    patient_id: number;
    patient_name: string;
    father_name: string | null;
    phone: string | null;
    age: string | number | null;
    registration_count: number;
    pending_accept_count: number;
    registrations: LaboratoryRegistrationItem[];
}

export interface PaginatedLaboratoryPatients {
    data: LaboratoryPatientGroup[];
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

export interface LaboratoryResultsFilters {
    search: string;
    patient_id: string;
    status: string;
    priority: string;
    date_from: string;
    date_to: string;
    per_page: string;
}

export interface LaboratoryNavUrls {
    pending: string;
    inProgress: string;
    completed: string;
    grouped: string;
    scan: string;
    index: string;
}

export interface LaboratoryGroupedRegistration {
    id: number;
    ref_no: string;
    lab_type_name: string | null;
    status: LaboratoryStatus;
    priority: LaboratoryPriority;
    doctor_name: string | null;
    print_url: string;
}

export interface LaboratoryGroupedCategory {
    category_id: number;
    patient_name: string | null;
    test_count: number;
    status_summary: {
        pending: number;
        in_progress: number;
        completed: number;
    };
    print_group_url: string;
    registrations: LaboratoryGroupedRegistration[];
}

export interface LaboratoryGroupedStats {
    pending: number;
    in_progress: number;
    completed: number;
    cancelled: number;
    total: number;
}

export interface LaboratoryGroupedFilters {
    search: string;
    patient_id: string;
    status: string;
    priority: string;
    doctor: string;
    date_from: string;
    date_to: string;
    per_page: string;
}

export interface LaboratoryReportRow {
    lab_type_id: number;
    lab_type_name: string;
    total_count: number;
}

export interface LaboratoryDetailedReportRow {
    id: number;
    ref_no: string;
    registration_date: string | null;
    patient_name: string | null;
    lab_type_name: string | null;
    status: LaboratoryStatus;
    priority: LaboratoryPriority;
    doctor_name: string | null;
    branch_name: string | null;
    created_by_name: string | null;
    updated_by_name: string | null;
    completed_by_name: string | null;
    completed_at: string | null;
    assigned_to_name: string | null;
    assigned_at: string | null;
    assigned_section_name: string | null;
    department_name: string | null;
    notes: string | null;
}

export interface SelectOption {
    id: number;
    name: string;
}

export interface SectionOption extends SelectOption {
    department_id: number;
    department?: { id: number; name: string };
}

export interface LaboratoryResultParameter {
    id: number | null;
    lab_parameter_id: number | null;
    parameter_name: string | null;
    unit: string | null;
    normal_range: string | null;
    result: string | null;
    text_result: string | null;
}

export interface LaboratoryResultShowPatient {
    id: number;
    name: string;
    father_name: string | null;
    age: string | number | null;
    phone: string | null;
    id_card: string | null;
    gender: string | null;
}

export interface LaboratoryResultShowRegistration {
    id: number;
    ref_no: string;
    status: LaboratoryStatus;
    priority: LaboratoryPriority;
    lab_type_name: string | null;
    category_name: string | null;
    doctor_name: string | null;
    assigned_to_name: string | null;
    registration_date: string | null;
    notes: string | null;
}

