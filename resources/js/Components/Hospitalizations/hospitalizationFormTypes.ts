export interface DepartmentOption {
    id: number;
    name: string;
}

export interface RoomOption {
    id: number;
    name: string;
    department_id: number | null;
}

export interface BedOption {
    id: number;
    number: string | number;
    room_id: number;
    is_occupied: boolean;
}

export interface HospitalizationFormValues {
    reason: string;
    remarks: string;
    department_id: string;
    room_id: string;
    bed_id: string;
}

export const EMPTY_HOSPITALIZATION_FORM: HospitalizationFormValues = {
    reason: '',
    remarks: '',
    department_id: '',
    room_id: '',
    bed_id: '',
};

export interface HospitalizationMeta {
    default_department_id?: number | null;
    departments: DepartmentOption[];
    rooms: RoomOption[];
    beds: BedOption[];
}
