export const SETTINGS_INDEX_WIDTH = {
    simple: 'max-w-6xl',
    wide: 'max-w-7xl',
} as const;

export const SETTINGS_FORM_WIDTH = 'max-w-3xl';
export const SETTINGS_WIDE_FORM_WIDTH = 'max-w-7xl';

export const settingsActionClasses = {
    view: 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30',
    edit: 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30',
    delete: 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 disabled:opacity-50',
} as const;

export const settingsHeaderButtonClass = {
    secondary:
        'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-gray-600',
    warning:
        'border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100 focus:ring-amber-200 dark:border-amber-800/80 dark:bg-amber-900/30 dark:text-amber-200 dark:hover:bg-amber-900/50 dark:focus:ring-amber-800',
    success:
        'border border-emerald-200 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-300 dark:border-emerald-800 dark:bg-emerald-700 dark:text-white dark:hover:bg-emerald-600 dark:focus:ring-emerald-800',
    danger:
        'border border-red-200 bg-red-600 text-white hover:bg-red-700 focus:ring-red-300 dark:border-red-800 dark:bg-red-700 dark:hover:bg-red-600 dark:focus:ring-red-800',
} as const;
