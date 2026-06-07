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

export interface DashboardData {
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
