import jQuery from 'jquery';
import persianDate from 'persian-date';
import 'persian-datepicker/dist/js/persian-datepicker';
import 'persian-datepicker/dist/css/persian-datepicker.min.css';

if (typeof window !== 'undefined') {
    window.$ = jQuery;
    window.jQuery = jQuery;
    window.persianDate = persianDate as unknown as Window['persianDate'];
}

export { jQuery as $ };
