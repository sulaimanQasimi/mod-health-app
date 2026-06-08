import jQuery from 'jquery';

const PERSIAN_DATEPICKER_SCRIPT = '/assets/persian%20date2/js/persianDatepicker.js';
const PERSIAN_DATEPICKER_CSS = '/assets/persian%20date2/css/persianDatepicker-default.css';

export const DARI_MONTHS = [
    'حمل',
    'ثور',
    'جوزا',
    'سرطان',
    'اسد',
    'سنبله',
    'میزان',
    'عقرب',
    'قوس',
    'جدی',
    'دلو',
    'حوت',
];

export const DARI_WEEKDAYS = [
    'شنبه',
    'یکشنبه',
    'دوشنبه',
    'سه شنبه',
    'چهارشنبه',
    'پنج شنبه',
    'جمعه',
];

export const DARI_WEEKDAYS_SHORT = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];

if (typeof window !== 'undefined') {
    window.$ = jQuery;
    window.jQuery = jQuery;
}

export { jQuery as $ };

let scriptPromise: Promise<void> | null = null;

function loadStylesheet(href: string): void {
    if (typeof document === 'undefined') {
        return;
    }

    if (document.querySelector('link[data-persian-datepicker-style]')) {
        return;
    }

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    link.dataset.persianDatepickerStyle = 'true';
    document.head.appendChild(link);
}

function isPersianDatepickerReady(): boolean {
    return typeof window !== 'undefined' && typeof window.$.fn.persianDatepicker === 'function';
}

export function ensurePersianDatepickerLoaded(): Promise<void> {
    if (isPersianDatepickerReady()) {
        return Promise.resolve();
    }

    if (!scriptPromise) {
        scriptPromise = new Promise((resolve, reject) => {
            loadStylesheet(PERSIAN_DATEPICKER_CSS);

            const script = document.createElement('script');
            script.src = PERSIAN_DATEPICKER_SCRIPT;
            script.async = true;
            script.onload = () => {
                if (isPersianDatepickerReady()) {
                    resolve();
                    return;
                }

                reject(new Error('persianDatepicker failed to register on jQuery'));
            };
            script.onerror = () => reject(new Error('Failed to load persianDatepicker script'));
            document.head.appendChild(script);
        });
    }

    return scriptPromise;
}
