import { PaginationLink, PaginationMeta } from './settings';

export interface UnderReviewListItem {
    id: number;
    patient_id?: number;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    department_name?: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    admission_date: string | null;
    is_accepted?: boolean;
    processed_by?: string | null;
    is_discharged?: boolean;
    permissions?: {
        accept?: boolean;
    };
    urls: { show: string };
}

export interface PaginatedUnderReviews {
    data: UnderReviewListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface UnderReviewFilters {
    patient_name: string;
    id_card: string;
    father_name: string;
    room_id: string;
    department_id: string;
}

export interface UnderReviewWorkflowFilters {
    search: string;
    record_id: string;
    patient_id: string;
}

export interface UnderReviewWorkflowUrls {
    index: string;
    pending: string;
    myCases: string;
    discharged: string;
    show?: string;
    accept?: string;
}

export interface UnderReviewFilterOptions {
    rooms: Array<{ id: number; name: string }>;
    departments: Array<{ id: number; name: string }>;
}

export interface UnderReviewVisit {
    id: number;
    description: string | null;
    doctor_name: string | null;
}

export interface UnderReviewMedicationRecord {
    id: number;
    order_date: string | null;
    medicine_name: string | null;
    nurse_name: string | null;
}

export interface UnderReviewDetail {
    id: number;
    reason: string;
    remarks: string;
    discharge_remark: string | null;
    is_discharged: boolean;
    is_accepted: boolean;
    processed_by_name: string | null;
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
    department_name: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    visits: UnderReviewVisit[];
    medication_records: UnderReviewMedicationRecord[];
    nursing_assessments_count: number;
    hospitalizations_count: number;
}

export interface UnderReviewShowPermissions {
    accept: boolean;
    discharge: boolean;
    store_visit: boolean;
    edit_visit: boolean;
    delete_visit: boolean;
}

export interface UnderReviewSectionPermissions {
    prescription: boolean;
    lab: boolean;
    blood: boolean;
    physiotherapy: boolean;
    hospitalization: boolean;
}

export interface UnderReviewShowUrls {
    index: string;
    pending: string;
    myCases: string;
    discharged: string;
    accept: string;
    discharge: string;
    visit_store: string;
    visit_update: string;
}

export interface UnderReviewEditForm {
    id: number;
    reason: string;
    remarks: string;
    department_id: number | null;
    room_id: number;
    bed_id: number;
    patient_id: number;
    appointment_id: number;
    branch_id: number;
}

export interface UnderReviewDepartmentOption {
    id: number;
    name: string;
}

export interface UnderReviewRoomOption {
    id: number;
    name: string;
    department_id: number | null;
}

export interface UnderReviewBedOption {
    id: number;
    number: string | number;
    room_id: number;
    is_occupied: boolean;
}
