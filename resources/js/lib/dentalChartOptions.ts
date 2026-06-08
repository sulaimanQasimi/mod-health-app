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
