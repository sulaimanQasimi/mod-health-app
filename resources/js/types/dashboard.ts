export interface ChartSeries {
    labels: string[];
    data: number[];
}

export interface WordCloudItem {
    name: string;
    weight: number;
}

export interface BranchOption {
    id: number;
    name: string;
}

export interface DashboardVisibility {
    today_patients: boolean;
    emergency_today_patients: boolean;
    all_patients: boolean;
    all_appointments: boolean;
    consultations: boolean;
    hospitalizations: boolean;
    checkups: boolean;
    icu: boolean;
    ccu: boolean;
    prescriptions: boolean;
    operations: boolean;
    physiotherapy: boolean;
    beds: boolean;
    patients_trend: boolean;
    appointments_trend: boolean;
    appointments_by_user: boolean;
    doctors_activity: boolean;
    nurses_activity: boolean;
}

export interface DashboardData {
    visible?: DashboardVisibility;
    statsLoaded?: boolean;
    chartsLoaded?: boolean;
    totalPatients: number;
    totalCheckups: number;
    totalAppointments: number;
    totalPrescriptions: number;
    totalConsultations: number;
    totalOperations: number;
    totalIcuAdmissions: number;
    totalCcuAdmissions: number;
    totalInPatientAdmissions: number;
    totalPhysiotherapyProcedures: number;
    todayPatients: number;
    totalEmergencyPatients: number;
    occupied_beds: number;
    free_beds: number;
    all_beds: number;
    patientsTrendData: ChartSeries;
    appointmentsTrendData: ChartSeries;
    appointmentsByUserData: ChartSeries;
    nurseActivityData: ChartSeries;
    wordCloudData: WordCloudItem[];
    branches: BranchOption[];
    chartBranchId: number;
}
