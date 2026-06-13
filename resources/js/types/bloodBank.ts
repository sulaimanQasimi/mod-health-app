import { PaginationLink, PaginationMeta } from './settings';

export type BloodRequestListVariant = 'new' | 'approved' | 'rejected' | 'delivered';

export interface BloodBankListUrls {
    dashboard: string;
    new: string;
    approved: string;
    rejected: string;
    delivered: string;
    inventory: string;
    movements: string;
    branchTransfers: string;
    report: string;
    current?: string;
}

export interface BloodRequestListItem {
    id: number;
    row_number?: number | null;
    patient_id_card: string | null;
    patient_name: string | null;
    father_name: string | null;
    department_name: string | null;
    group: string | null;
    rh: string | null;
    type: string | null;
    quantity: number | null;
    status: string;
    created_at: string | null;
    urls: { show: string };
}

export interface PaginatedBloodRequests {
    data: BloodRequestListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface BloodRequestListFilters {
    q: string;
    department_id: string;
    group: string;
    rh: string;
    type: string;
    from: string;
    to: string;
    per_page: string;
}

export interface BloodRequestFilterOptions {
    departments: { id: number; name: string }[];
    bloodGroups: string[];
    bloodComponentTypes: string[];
}

export interface BloodRequestDetail {
    id: number;
    status: string;
    group: string | null;
    rh: string | null;
    type: string | null;
    quantity: number | null;
    reject_reason: string | null;
    created_at: string | null;
    patient: {
        name: string | null;
        father_name: string | null;
        id_card: string | null;
        phone: string | null;
    };
    department_name: string | null;
    receiver_department_name: string | null;
    receiver_nurse_name: string | null;
    created_by_name: string | null;
    appointment_id: number | null;
    requested_qty: number;
    reserved_compatible_qty: number;
    issued_qty: number;
    remaining_qty: number;
    quantity_inferred_from_volume_ml: boolean;
    order_quantity_display: {
        mode: 'empty' | 'units' | 'volume_ml';
        ml?: number;
        units?: number;
    };
    workflow: {
        current_step: number | null;
        steps: { number: number; done: boolean; current: boolean }[];
    };
    blood_check: {
        abo_group: string | null;
        rh: string | null;
        component_type: string | null;
        quantity: number | null;
        notes: string | null;
        patient_typed_group: string | null;
        patient_typed_rh: string | null;
        verified_at: string | null;
        verified_by_name: string | null;
    } | null;
    patient_samples: {
        id: number;
        sample_id: string | null;
        collected_at: string | null;
        collected_by_name: string | null;
        notes: string | null;
    }[];
    crossmatches: {
        id: number;
        blood_unit_id: number;
        bag_number: string | null;
        major_result: string | null;
        minor_result: string | null;
        status: string;
        is_reserved: boolean;
        tested_at: string | null;
        tested_by_name: string | null;
    }[];
    issued_units: {
        id: number;
        bag_number: string | null;
        expires_at: string | null;
        issued_at: string | null;
        urls?: { show: string };
    }[];
}

export interface BloodRequestWorkflowAvailableUnit {
    id: number;
    bag_number: string | null;
    blood_group: string | null;
    rh: string | null;
    component_type: string | null;
    expires_at: string | null;
    auto_abo_rh_compatible: boolean;
    is_reserved: boolean;
    crossmatch: {
        id: number;
        major_result: string | null;
        minor_result: string | null;
        status: string;
        auto_reason: string | null;
        patient_sample_id: number | null;
        urls: {
            reserve: string;
            override: string;
        };
    } | null;
    urls: {
        saveCrossmatch: string;
        unreserve: string;
        inventoryShow: string;
    };
}

export interface BloodRequestWorkflowInventoryUnit {
    id: number;
    bag_number: string | null;
    blood_group: string | null;
    rh: string | null;
    component_type: string | null;
    expires_at: string | null;
    screening_status: string;
    crossmatch_status: string | null;
    urls: { show: string };
}

export interface BloodRequestWorkflowData {
    availableUnits: BloodRequestWorkflowAvailableUnit[];
    inventoryPreviewUnits: BloodRequestWorkflowInventoryUnit[];
    hasCrossmatchFlow: boolean;
    deliverableUnitIds: number[];
    crossmatchResultValues: string[];
    bloodComponentTypes: string[];
    bloodCheckForm: {
        abo_group: string;
        rh: string;
        component_type: string;
        quantity: number;
        patient_typed_group: string;
        patient_typed_rh: string;
        notes: string;
    };
    deliveryDefaults: {
        receiver_department_id: number | null;
        receiver_nurse_id: number | null;
    };
}

export interface BloodRequestShowPermissions {
    approve: boolean;
    reject: boolean;
    deliver: boolean;
    manageCrossmatch: boolean;
    manageInventory: boolean;
}

export interface BloodRequestShowUrls extends BloodBankListUrls {
    back: string;
    approve: string;
    reject: string;
    deliver: string;
    bloodCheck: string;
    storeSample: string;
    inventory: string;
    legacyInventoryShow: string;
    nursesByDepartment: string;
}

export interface BloodUnitListItem {
    id: number;
    row_number?: number | null;
    bag_number: string | null;
    blood_group: string | null;
    rh: string | null;
    component_type: string | null;
    status: string;
    expires_at: string | null;
    created_at: string | null;
    urls: { show: string };
}

export interface PaginatedBloodUnits {
    data: BloodUnitListItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface BloodInventoryFilters {
    status: string;
    blood_group: string;
    rh: string;
    component_type: string;
    q: string;
    expires_within: string;
    sort: string;
    per_page: string;
}

export interface BloodInventoryFilterOptions {
    bloodGroups: string[];
    bloodComponentTypes: string[];
    statuses: string[];
}

export interface BloodStockMovementItem {
    id: number;
    row_number?: number | null;
    movement_type: string;
    bag_number: string | null;
    user_name: string | null;
    notes: string | null;
    created_at: string | null;
}

export interface PaginatedBloodMovements {
    data: BloodStockMovementItem[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface BloodMovementFilters {
    movement_type: string;
    from: string;
    to: string;
    bag_number: string;
    per_page: string;
}

export interface BloodReportItem {
    id: number;
    patient_name: string | null;
    department_name: string | null;
    branch_name: string | null;
    status: string;
    group: string | null;
    rh: string | null;
    appointment_id: number | null;
}

export interface BloodReportFilters {
    patient_name: string;
    status: string;
    group: string;
    rh: string;
    department_id: string;
    from: string;
    to: string;
}

export interface BloodReportFilterOptions {
    departments: { id: number; name: string }[];
    bloodGroups: string[];
    statuses: string[];
}

export interface BloodUnitReceiveForm {
    donor_record_department: boolean;
    department_id: string;
    donor_name: string;
    donor_father_name: string;
    donor_age: string;
    donor_gender: string;
    donor_phone: string;
    donor_national_id: string;
    donor_blood_pressure: string;
    donor_type: string;
    donor_military_department: string;
    donor_comorbidities: string;
    donor_receiver: string;
    phlebotomy_date: string;
    phlebotomy_time: string;
    blood_group: string;
    rh: string;
    component_type: string;
    bag_number: string;
    volume_ml: string;
    collected_date: string;
    collected_time: string;
    expires_date: string;
    expires_time: string;
    notes: string;
}

export interface BloodUnitTestRecord {
    id: number;
    abo_result: string | null;
    rh_result: string | null;
    dct_result: string | null;
    ict_result: string | null;
    hbs_result: string | null;
    hcv_result: string | null;
    hiv_result: string | null;
    vdrl_result: string | null;
    overall_status: string;
    remarks: string | null;
    tested_at: string | null;
    tested_by_name: string | null;
}

export interface BloodUnitStockMovement {
    id: number;
    movement_type: string;
    user_name: string | null;
    notes: string | null;
    created_at: string | null;
}

export interface BloodUnitDetail {
    id: number;
    bag_number: string | null;
    blood_group: string | null;
    rh: string | null;
    component_type: string | null;
    status: string;
    volume_ml: number | null;
    collected_at: string | null;
    expires_at: string | null;
    is_expired: boolean;
    is_expiring_soon: boolean;
    days_until_expiry: number | null;
    branch_name: string | null;
    screening_status: string;
    test: BloodUnitTestRecord | null;
    tests: BloodUnitTestRecord[];
    donation: {
        donor_name: string | null;
        department_name: string | null;
        patient: { id: number; name: string; urls: { show: string } } | null;
        phlebotomy_at: string | null;
        samples_count: number;
    } | null;
    stock_movements: BloodUnitStockMovement[];
}

export interface BloodUnitTestForm {
    abo_result: string;
    rh_result: string;
    dct_result: string;
    ict_result: string;
    hbs_result: string;
    hcv_result: string;
    hiv_result: string;
    vdrl_result: string;
    remarks: string;
}

export interface BloodUnitShowPermissions {
    manage: boolean;
    canQuarantine: boolean;
    canDiscard: boolean;
    canReleaseAfterTests: boolean;
}
