export interface NamedOption {
    id: number;
    name?: string;
    name_dr?: string;
    code?: string;
}

export interface PatientFormData {
    branchId: number;
    clinicType: string | null;
    registrationDate: string;
    provinces: NamedOption[];
    recipients: NamedOption[];
    relations: NamedOption[];
    militeryTypes: NamedOption[];
    departments: NamedOption[];
    districts?: NamedOption[];
    recipientParts?: NamedOption[];
    referralRecipientParts?: NamedOption[];
}

export interface PatientFormUrls {
    store?: string;
    update?: string;
    show?: string;
    destroy?: string;
    districts: string;
    recipientParts: string;
    doctorsByDepartment: string;
    back: string;
}

/** @deprecated Use PatientFormUrls */
export type PatientCreateUrls = PatientFormUrls;

export type PatientType = '0' | '1' | '2';

export type PatientFormMode = 'create' | 'edit';

export interface PatientFormValues {
    id: number;
    type: PatientType;
    id_card: string;
    name: string;
    last_name: string;
    father_name: string;
    nid: string;
    job: string;
    job_category: '0' | '1';
    militery_type_id: string;
    rank: string;
    phone: string;
    age_year: string;
    age_month: string;
    age_day: string;
    gender: string;
    referred_by: string;
    recipient_part_id: string;
    province_id: string;
    district_id: string;
    referral_name: string;
    referral_last_name: string;
    referral_father_name: string;
    referral_nid: string;
    referral_id_card: string;
    referral_phone: string;
    referral_recipient: string;
    referral_recipient_part_id: string;
    relation_id: string;
}

export interface PatientFormPermissions {
    delete?: boolean;
}

export interface DoctorOption {
    id: number;
    name: string;
}

export interface StorePatientResponse {
    success: boolean;
    message: string;
    patient?: {
        id: number;
        name: string;
        last_name: string | null;
    };
    appointment?: {
        id: number;
        department: string;
        doctor: string;
        date: string;
        time: string;
        token_url: string;
    };
    errors?: Record<string, string[]>;
}

export interface UpdatePatientResponse {
    success: boolean;
    message: string;
    patient?: {
        id: number;
        name: string;
        last_name: string | null;
    };
    errors?: Record<string, string[]>;
}

export interface PatientListItem {
    id: number;
    id_card: string | null;
    name: string;
    last_name: string | null;
    father_name: string | null;
    location: string;
    age: string | null;
    militery_type: string | null;
    phone: string | null;
    created_by: string | null;
}

export interface PatientDetail {
    id: number;
    id_card: string | null;
    name: string;
    last_name: string | null;
    father_name: string | null;
    nid: string | null;
    phone: string | null;
    age: string | null;
    gender: number | string | null;
    job: string | null;
    rank: string | null;
    job_category: number | string | null;
    type: number | string | null;
    province: string | null;
    district: string | null;
    militery_type: string | null;
    relation: string | null;
    referred_by: string | null;
    recipient_part: string | null;
    referral_name: string | null;
    referral_last_name: string | null;
    referral_father_name: string | null;
    referral_nid: string | null;
    referral_id_card: string | null;
    referral_phone: string | null;
    registration_date: string | null;
    created_at: string;
    created_by: string | null;
    image: string | null;
}

export interface PatientShowPermissions {
    edit: boolean;
    delete: boolean;
    printCard: boolean;
    createAppointment: boolean;
    uploadImage: boolean;
    nephrology: boolean;
}

export interface PatientShowUrls {
    index: string;
    edit: string;
    destroy: string;
    printCard: string;
    webcam: string;
    appointmentStore: string;
    doctorsByDepartment: string;
    hemodialysisCreate: string;
    hemodialysisIndex: string;
}

export interface PatientAppointmentItem {
    id: number;
    number: number;
    doctor_name: string | null;
    date: string;
}

export interface PatientDiagnosisItem {
    id: number;
    description: string;
    date: string;
}

export interface PatientDiagnosesGroup {
    primary: PatientDiagnosisItem[];
    final: PatientDiagnosisItem[];
}

export interface NephrologyRegistrationItem {
    id: number;
    ref_no: string | number | null;
    visit_date: string | null;
    doctor_name: string | null;
    diagnosis: string | null;
    show_url: string;
}

export interface HemodialysisSessionItem {
    id: number;
    ref_no: string | number | null;
    session_date: string | null;
    duration_minutes: number | null;
    status: string;
    show_url: string;
}

export interface PatientShowAppointmentForm {
    branchId: number;
    clinicType: string | null;
    departments: NamedOption[];
}

export interface CreateAppointmentResponse {
    success: boolean;
    message: string;
    patient?: {
        id: number;
        name: string;
        last_name: string | null;
    };
    appointment?: {
        id: number;
        department: string;
        doctor: string;
        date: string;
        time: string;
        token_url: string;
    };
    errors?: Record<string, string[]>;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedPatients {
    data: PatientListItem[];
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

export interface PatientIndexFilters {
    name: string;
    father_name: string;
    last_name: string;
    phone: string;
    card_search: string;
    militery_type_id: string;
    province_id: string;
    gender: string;
    job_category: string;
}

export interface PatientIndexFilterOptions {
    militeryTypes: NamedOption[];
    provinces: NamedOption[];
}

export interface PatientIndexUrls {
    index: string;
    create: string;
    show: string;
    edit: string;
    destroy: string;
}

export interface PatientIndexPermissions {
    create: boolean;
    edit: boolean;
    delete: boolean;
}
