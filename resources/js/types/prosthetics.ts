import { PaginationLink } from './settings';

export interface ProstheticCatalogItem {
    id: number;
    item_code: string;
    name: string;
    category: string | null;
    standard_cost: number | null;
}

export interface ProstheticReferralListItem {
    id: number;
    referral_number: string;
    status: string;
    referral_date: string | null;
    urgency: string | null;
    requested_service_type: string | null;
    patient_name: string | null;
    patient_nid: string | null;
    patient?: {
        id: number;
        name: string;
        last_name?: string;
        phone?: string;
        nid?: string;
        id_card?: string;
    };
    urls?: {
        show?: string;
    };
}

export interface ProstheticReferralFilters {
    q: string;
    referral_number: string;
    patient_id: string;
    patient_name: string;
    phone: string;
    nid: string;
    id_card: string;
    status: string;
    urgency: string;
    requested_service_type: string;
    from: string;
    to: string;
}

export interface ProstheticReferralDetail {
    id: number;
    referral_number: string;
    status: string;
    referral_date: string | null;
    referring_facility: string | null;
    referring_doctor: string | null;
    reason: string | null;
    diagnosis_summary: string | null;
    urgency: string | null;
    requested_service_type: string | null;
    notes: string | null;
    converted_case_id: number | null;
    patient: { id: number; name: string; phone?: string | null; nid?: string | null } | null;
    converted_case: { id: number; case_number: string } | null;
}

export interface ProstheticCaseListItem {
    id: number;
    case_number: string;
    status: string;
    updated_at: string | null;
    patient?: {
        id: number;
        name: string;
        last_name?: string;
        phone?: string;
        nid?: string;
    };
}

export interface ProstheticCasePermissions {
    is_read_only: boolean;
    edit_assessment: boolean;
    edit_measurements: boolean;
    edit_prescription: boolean;
    edit_estimate: boolean;
    submit_for_approval: boolean;
    approve_case: boolean;
    create_work_order: boolean;
    update_work_order: boolean;
    issue_stock: boolean;
    store_fitting: boolean;
    store_delivery: boolean;
    store_follow_up: boolean;
    close_case: boolean;
    manage_attachments: boolean;
}

export interface ProstheticMeasurementRow {
    name: string;
    value_numeric: string | number;
    unit: string;
    notes: string;
}

export interface ProstheticPrescriptionLine {
    catalog_id: string | number;
    quantity: string | number;
    notes: string;
}

export interface ProstheticCaseDetail {
    id: number;
    case_number: string;
    status: string;
    patient_id: number;
    patient_name: string | null;
    referral: { id: number; referral_number: string } | null;
    assessment: {
        fit_outcome: string;
        history_present_condition: string | null;
        skin_stump_notes: string | null;
        functional_goals: string | null;
    } | null;
    measurement_set: {
        id: number | null;
        version: number | null;
        is_locked: boolean;
        rows: ProstheticMeasurementRow[];
    };
    prescription: {
        device_timing: string;
        special_instructions: string | null;
        lines: ProstheticPrescriptionLine[];
    } | null;
    estimate: {
        id: number;
        parts_total: number;
        labor_total: number;
        discount: number;
        total: number;
        currency: string;
        status: string;
    } | null;
    work_order: {
        id: number;
        work_order_number: string;
        status: string;
        production_stage: string;
    } | null;
    attachments: Array<{
        id: number;
        original_name: string;
        category: string;
        file_url: string | null;
        created_at: string | null;
    }>;
}

export interface ProstheticCaseWorkflowOptions {
    fit_outcomes: string[];
    device_timings: string[];
    work_order_stages: string[];
    fitting_outcomes: string[];
    follow_up_types: string[];
}

export interface PaginatedProstheticReferrals {
    data: ProstheticReferralListItem[];
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

export interface PaginatedProstheticCases {
    data: ProstheticCaseListItem[];
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

export interface PaginatedProstheticCatalog {
    data: ProstheticCatalogItem[];
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
