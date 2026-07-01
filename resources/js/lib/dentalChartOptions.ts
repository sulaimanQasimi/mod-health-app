export const TOOTH_CONDITION_COLORS: Record<string, string> = {
    healthy: '#008000',
    cavity: '#ffc107',
    filling: '#17a2b8',
    crown: '#6f42c1',
    bridge: '#6610f2',
    missing: '#6c757d',
    extraction: '#dc3545',
    impacted: '#fd7e14',
    root_canal: '#20c997',
    implant: '#0d6efd',
    decay: '#e83e8c',
    fractured: '#ff6b6b',
    no_data: '#d1d5db',
};

export const UPPER_RIGHT_TEETH = [18, 17, 16, 15, 14, 13, 12, 11];
export const UPPER_LEFT_TEETH = [21, 22, 23, 24, 25, 26, 27, 28];
export const LOWER_LEFT_TEETH = [48, 47, 46, 45, 44, 43, 42, 41];
export const LOWER_RIGHT_TEETH = [31, 32, 33, 34, 35, 36, 37, 38];

export const FDI_TOOTH_NUMBERS = [
    ...Array.from({ length: 8 }, (_, index) => 11 + index),
    ...Array.from({ length: 8 }, (_, index) => 21 + index),
    ...Array.from({ length: 8 }, (_, index) => 31 + index),
    ...Array.from({ length: 8 }, (_, index) => 41 + index),
];

export const TOOTH_CONDITIONS = [
    'healthy',
    'cavity',
    'filling',
    'crown',
    'bridge',
    'root_canal',
    'implant',
    'decay',
    'fractured',
    'extraction',
    'missing',
    'impacted',
] as const;

export const GUM_HEALTH_OPTIONS = [
    'healthy',
    'gingivitis',
    'periodontitis',
    'recession',
    'bleeding',
] as const;

export const MOBILITY_OPTIONS = ['none', 'grade1', 'grade2', 'grade3'] as const;

export const IMPLANT_STATUS_OPTIONS = ['planned', 'placed', 'failed', 'removed'] as const;

export const selectClassName =
    'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white';
