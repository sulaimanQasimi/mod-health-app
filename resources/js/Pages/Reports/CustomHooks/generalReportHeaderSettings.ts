export interface ReportHeaderSettings {
    line1: string;
    line2: string;
    line3: string;
    line4: string;
    line5: string;
    line6: string;
}

export const REPORT_HEADER_LEFT_LOGO = '/images/logos/لوگو قومنداني.JPG';
export const REPORT_HEADER_RIGHT_LOGO = '/images/logos/لوگوی جدید وزارت دفاع ملی.png';

export const HEADER_LINE_FIELDS: Array<{
    key: keyof ReportHeaderSettings;
    label: string;
}> = [
    { key: 'line1', label: 'Title line' },
    { key: 'line2', label: 'Ministry' },
    { key: 'line3', label: 'General Staff' },
    { key: 'line4', label: 'Health Command' },
    { key: 'line5', label: 'Medical Academy' },
    { key: 'line6', label: 'Department / Section' },
];

export function createDefaultHeaderSettings(): ReportHeaderSettings {
    return {
        line1: 'امارت اسلامی افغانستان',
        line2: 'وزارت دفاع ملی',
        line3: 'ستر درستیز',
        line4: 'قوماندانیت صحیه',
        line5: 'قوماندانی اکادمی علوم طبی',
        line6: 'یورولوژی',
    };
}
