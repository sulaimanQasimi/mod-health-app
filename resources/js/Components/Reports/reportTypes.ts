export interface NamedCount {
    name: string;
    count: number;
}

export interface DateCount {
    date: string;
    count: number;
}

export interface ReportAnalytics {
    by_status?: NamedCount[];
    by_doctor?: NamedCount[];
    by_department?: NamedCount[];
    by_type?: NamedCount[];
    by_gender?: NamedCount[];
    by_date?: DateCount[];
    [key: string]: NamedCount[] | DateCount[] | undefined;
}

export interface ReportKpiStat {
    key: string;
    label: string;
    value: number | string;
    icon: string;
    accent: string;
    subtitle?: string;
}

export interface ReportChartCard {
    key: string;
    title: string;
    type: 'bar' | 'donut' | 'trend';
    labels: string[];
    values: number[];
    color?: string;
    colors?: string[];
}
