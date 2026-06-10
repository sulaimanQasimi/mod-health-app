import { PaginationLink, PaginationMeta } from './settings';

export interface HospitalizationDashboardStats {
    active: number;
    discharged: number;
    occupied_beds: number;
    total_beds: number;
    recovered?: number;
    moved?: number;
    died?: number;
}

export interface HospitalizationListItem {
    id: number;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    department_name?: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    doctor_name?: string | null;
    admission_date: string | null;
    discharged_at?: string | null;
    discharge_status?: string | null;
    urls: { show: string };
}

export interface PaginatedHospitalizations {
    data: HospitalizationListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface HospitalizationActiveFilters {
    q: string;
    room_id: string;
    date_from: string;
    date_to: string;
}

export interface HospitalizationDischargedFilters {
    q: string;
    patient_id: string;
    room_id: string;
    doctor_id: string;
    discharge_date_from: string;
    discharge_date_to: string;
}

export interface HospitalizationOption {
    id: number;
    name: string;
}

export interface HospitalizationClinicalRow {
    id: number;
    date?: string | null;
    time?: string | null;
    rbs?: string | number | null;
    fbs?: string | number | null;
    insulin_dose?: string | number | null;
    nurse_name?: string | null;
    medicine_name?: string | null;
    note_am?: string | null;
    note_pm?: string | null;
    order_date?: string | null;
    type_name?: string | null;
    schedules_count?: number;
    recorded_at?: string | null;
}

export interface HospitalizationBloodBank {
    id: number;
    group: string | null;
    created_at: string | null;
}

export interface HospitalizationDetail {
    id: number;
    reason: string;
    remarks: string;
    discharge_remark: string | null;
    discharge_status: string | null;
    is_discharged: boolean;
    admission_date: string | null;
    admission_time: string | null;
    discharged_at: string | null;
    appointment_id: number | null;
    department_name?: string | null;
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
    blood_banks: HospitalizationBloodBank[];
    medication_records: HospitalizationClinicalRow[];
    vital_signs: HospitalizationClinicalRow[];
    nursing_assessments_count: number;
    advices_count: number;
    complaints_count: number;
    icu_count: number;
    anesthesia_count: number;
}

export interface HospitalizationShowPermissions {
    edit: boolean;
    discharge: boolean;
    change_room_bed: boolean;
}

export interface HospitalizationEditForm {
    id: number;
    reason: string;
    remarks: string;
    room_id: number;
    bed_id: number;
    patient_id: number;
    appointment_id: number | null;
    branch_id: number;
    food_type_ids: number[];
    patinet_companion: string | null;
    companion_father_name: string | null;
    relation_to_patient: string | number | null;
    companion_card_type: string | null;
}

export interface HospitalizationBedOption {
    id: number;
    number: string | number;
    room_id: number;
    is_occupied: boolean;
}

export interface HospitalizationReportFilters {
    patient_name: string;
    doctor_id: string;
    room_id: string;
    food_type_id: string;
    is_discharged: string;
    date_from: string;
    date_to: string;
}

export interface HospitalizationReportItem {
    id: number;
    patient_name: string | null;
    patient_id_card: string | null;
    doctor_name: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    admission_date: string | null;
    discharged_at: string | null;
    is_discharged: boolean;
    urls: { show: string };
}

export interface HospitalizationRoomSummary {
    id: number;
    name: string;
    department_name: string | null;
    beds_count: number;
    occupied_beds_count: number;
    empty_beds_count: number;
    occupancy_rate: number;
}

export interface HospitalizationRoomManagementOverview {
    rooms_count: number;
    beds_count: number;
    occupied_beds_count: number;
    empty_beds_count: number;
    occupancy_rate: number;
}

export interface HospitalizationRoomBedRow {
    id: number;
    number: string | number;
    is_occupied: boolean;
    patient_name?: string | null;
    father_name?: string | null;
    admission_date?: string | null;
    hospitalization_id?: number | null;
    hospitalization_url?: string | null;
}

export interface HospitalizationRoomManagementSelected {
    id: number;
    name: string;
    department_name: string | null;
}
