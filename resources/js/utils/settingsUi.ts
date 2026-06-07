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
