export interface OperationReferralFormValues {
    plan: string;
    other_problems: string;
    operation_type_id: string;
    operation_surgion_id: string;
    operation_assistants_id: string[];
    date: string;
    time: string;
    planned_duration: string;
    position_on_bed: string;
    estimated_blood_waste: string;
}

export const EMPTY_OPERATION_REFERRAL_FORM: OperationReferralFormValues = {
    plan: '',
    other_problems: '',
    operation_type_id: '',
    operation_surgion_id: '',
    operation_assistants_id: [],
    date: '',
    time: '',
    planned_duration: '',
    position_on_bed: '',
    estimated_blood_waste: '',
};

export interface OperationReferralMeta {
    patient_name: string | null;
    current_room_name: string | null;
    current_bed_number: string | number | null;
    will_clear_bed: boolean;
    operation_types: { id: number; name: string }[];
    hospital_doctors: { id: number; name: string }[];
}
