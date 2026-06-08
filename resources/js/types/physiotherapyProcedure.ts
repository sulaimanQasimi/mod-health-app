import { OptionItem, PaginationLink } from './settings';

export type PhysiotherapyProcedureStatus = 'pending' | 'in_progress' | 'completed' | 'cancelled';

export interface PhysiotherapyProcedureListItem {
    id: number;
    appointment_id: number;
    patient_name: string | null;
    patient_id_card: string | null;
    patient_father_name: string | null;
    patient_phone: string | null;
    physiotherapy_type: string | null;
    physiotherapist: string | null;
    type: string | null;
    duration: number | null;
    counter: number;
    days_count: number;
    progress_counter: number;
    progress_total: number;
    progress_percentage: number;
    status: PhysiotherapyProcedureStatus;
    start_date: string | null;
    end_date: string | null;
    reviews_count: number;
}

export interface PhysiotherapyProcedureReview {
    id: number;
    description: string;
    status: PhysiotherapyProcedureStatus;
    days_count: number;
    created_by_name: string | null;
    updated_by_name: string | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface PhysiotherapyProcedureDetail extends PhysiotherapyProcedureListItem {
    physiotherapy_type_id: number;
    doctor_id: number;
    description: string | null;
    notes: string | null;
    created_by_name: string | null;
    updated_by_name: string | null;
    created_at: string | null;
    updated_at: string | null;
    appointment: {
        id: number;
        date: string | null;
        patient_name: string | null;
    } | null;
    reviews: PhysiotherapyProcedureReview[];
}

export interface PhysiotherapyProcedureStats {
    total: number;
    pending: number;
    in_progress: number;
    completed: number;
    cancelled: number;
}

export interface PhysiotherapyProcedureFilters {
    search: string;
    status: string;
    physiotherapy_type_id: string;
    doctor_id: string;
    start_date: string;
    end_date: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

export interface PaginatedPhysiotherapyProcedures {
    data: PhysiotherapyProcedureListItem[];
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

export interface PhysiotherapyProcedureListPermissions {
    view: boolean;
    create: boolean;
    edit: boolean;
    delete: boolean;
    updateProgress: boolean;
    viewMyProcedures: boolean;
    viewAllProcedures: boolean;
    viewReports: boolean;
}

export interface PhysiotherapyProcedureShowPermissions {
    edit: boolean;
    delete: boolean;
    updateProgress: boolean;
    addReview: boolean;
    editReview: boolean;
    deleteReview: boolean;
}

export interface PhysiotherapyProcedureFilterOptions {
    physiotherapy_types: OptionItem[];
    physiotherapists: OptionItem[];
}

export interface PhysiotherapyProcedureFormOptions {
    physiotherapy_types: OptionItem[];
    physiotherapists: OptionItem[];
}
