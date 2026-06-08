import { PaginationLink } from './settings';

export interface DentalChartRegistrationHeader {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    dentist_name: string | null;
}

export interface DentalChartRecord {
    id: number;
    dentist_registration_id: number;
    tooth_number: number;
    tooth_condition: string;
    gum_health: string | null;
    oral_hygiene_score: string | number | null;
    pocket_depth: string | number | null;
    bleeding: boolean;
    mobility: string | null;
    treatment_history: string | null;
    notes: string | null;
    chart_date: string | null;
    implant_system_brand: string | null;
    implant_diameter: string | number | null;
    implant_length: string | number | null;
    implant_status: string | null;
    implant_notes: string | null;
    created_by_name?: string | null;
    images_count?: number;
    periodontal_count?: number;
}

export interface DentalChartFormData {
    tooth_number: string;
    tooth_condition: string;
    gum_health: string;
    oral_hygiene_score: string;
    pocket_depth: string;
    bleeding: string;
    mobility: string;
    treatment_history: string;
    notes: string;
    implant_system_brand: string;
    implant_diameter: string;
    implant_length: string;
    implant_status: string;
    implant_notes: string;
}

export interface DentalChartUrls {
    registrationShow: string;
    index: string;
    create: string;
    store: string;
    history: string;
    compare: string;
    print: string;
    export: string;
    edit?: string;
    update?: string;
    destroy?: string;
}

export interface PaginatedDentalCharts {
    data: DentalChartRecord[];
    links: PaginationLink[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
