import { PaginationLink, PaginationMeta } from './settings';

export interface UnderReviewListItem {
    id: number;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    admission_date: string | null;
    urls: { show: string };
}

export interface PaginatedUnderReviews {
    data: UnderReviewListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface UnderReviewFilters {
    q: string;
}

export interface UnderReviewVisit {
    id: number;
    description: string | null;
    doctor_name: string | null;
}

export interface UnderReviewDiabetesChart {
    id: number;
    date: string | null;
    time: string | null;
    rbs: string | number | null;
    fbs: string | number | null;
    insulin_dose: string | number | null;
    nurse_name: string | null;
    medicine_name: string | null;
}

export interface UnderReviewNurseNote {
    id: number;
    date: string | null;
    note_am: string | null;
    note_pm: string | null;
    nurse_name: string | null;
}

export interface UnderReviewMedicationRecord {
    id: number;
    order_date: string | null;
    medicine_name: string | null;
    nurse_name: string | null;
}

export interface UnderReviewVitalSign {
    id: number;
    type_name: string | null;
    schedules_count: number;
    recorded_at: string | null;
}

export interface UnderReviewNutritionCare {
    id: number;
    date: string | null;
    nurse_name: string | null;
}

export interface UnderReviewDetail {
    id: number;
    reason: string;
    remarks: string;
    discharge_remark: string | null;
    is_discharged: boolean;
    admission_date: string | null;
    admission_time: string | null;
    appointment_id: number | null;
    patient: {
        id: number;
        name: string;
        father_name: string | null;
        id_card: string | null;
        phone: string | null;
    } | null;
    doctor_name: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    visits: UnderReviewVisit[];
    diabetes_charts: UnderReviewDiabetesChart[];
    nurse_notes: UnderReviewNurseNote[];
    medication_records: UnderReviewMedicationRecord[];
    vital_signs: UnderReviewVitalSign[];
    nutrition_cares: UnderReviewNutritionCare[];
    nursing_assessments_count: number;
    hospitalizations_count: number;
}

export interface UnderReviewShowPermissions {
    edit: boolean;
    discharge: boolean;
    store_visit: boolean;
    edit_visit: boolean;
    delete_visit: boolean;
}

export interface UnderReviewEditForm {
    id: number;
    reason: string;
    remarks: string;
    room_id: number;
    bed_id: number;
    patient_id: number;
    appointment_id: number;
    branch_id: number;
}

export interface UnderReviewRoomOption {
    id: number;
    name: string;
}

export interface UnderReviewBedOption {
    id: number;
    number: string | number;
    room_id: number;
    is_occupied: boolean;
}
