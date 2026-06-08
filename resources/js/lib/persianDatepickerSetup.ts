import jQuery from 'jquery';
import persianDate from 'persian-date';
import 'persian-datepicker/dist/css/persian-datepicker.min.css';

if (typeof window !== 'undefined') {
    window.$ = jQuery;
    window.jQuery = jQuery;
    window.persianDate = persianDate as unknown as Window['persianDate'];
}

export { jQuery as $ };

let loadPromise: Promise<void> | null = null;

function isPersianDatepickerReady(): boolean {
    return typeof window !== 'undefined' && typeof window.$.fn.persianDatepicker === 'function';
}

export function ensurePersianDatepickerLoaded(): Promise<void> {
    if (isPersianDatepickerReady()) {
        return Promise.resolve();
    }

    if (!loadPromise) {
        loadPromise = import('persian-datepicker/dist/js/persian-datepicker').then(() => {
            if (!isPersianDatepickerReady()) {
                throw new Error('persian-datepicker failed to register on jQuery');
            }
        });
    }

    return loadPromise;
}
