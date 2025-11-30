<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | This option controls the default theme for form components.
    | Available themes: 'default', 'minimal', 'modern'
    |
    */

    'theme' => env('TAILWIND_FORMS_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | CSS Framework
    |--------------------------------------------------------------------------
    |
    | This option controls which CSS framework to use for styling.
    | Available options: 'tailwind', 'bootstrap'
    |
    */

    'framework' => env('TAILWIND_FORMS_FRAMEWORK', 'tailwind'),

    /*
    |--------------------------------------------------------------------------
    | Default Classes
    |--------------------------------------------------------------------------
    |
    | These classes will be applied by default to form components.
    | You can override these in individual components.
    |
    */

    'default_classes' => [
        'input' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
        'textarea' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
        'select' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
        'checkbox' => 'h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded',
        'radio' => 'h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300',
        'button' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
        'label' => 'block text-sm font-medium text-gray-700',
        'error' => 'mt-1 text-sm text-red-600',
        'help' => 'mt-1 text-sm text-gray-500',
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Variants
    |--------------------------------------------------------------------------
    |
    | Define different variants for form components.
    |
    */

    'variants' => [
        'input' => [
            'default' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
            'error' => 'block w-full rounded-md border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm',
            'success' => 'block w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm',
        ],
        'button' => [
            'primary' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
            'secondary' => 'inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
            'danger' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500',
        ],
    ],
];
