export interface NamedOption {
    id: number;
    name?: string;
    name_dr?: string;
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
}

export interface PatientCreateUrls {
    store: string;
    districts: string;
    doctorsByDepartment: string;
    back: string;
}

export type PatientType = '0' | '1' | '2';

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
