window.addEventListener('load', function() {

    document.querySelectorAll('.select-cal').forEach(function(e) {

        e.addEventListener('click', function() {
            let x = this.getAttribute('data-calendar');
            $('.select-cal').removeClass('cal-selected');
            $(this).addClass('cal-selected');
            if (x == 'shamsi') {
                config.SelectedCal = 'jalali';


            } else {

                config.SelectedCal = 'gregorian';
            }
            setCalendarParams(x);
        });
    });
});
// console.log("hellos");

function setCalendarParams(t) {

    if (t == 'shamsi') {
        Calendar._DN = new Array("۱ شنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنجشنبه", "جمعه", "شنبه", "یکشنبه");
        Calendar._SDN = new Array("۱ شنبه", "۲ شنبه", " ۳ شنبه", "۴ شنبه", "۵ شنبه", "جمعه", "شنبه", "۱ شنبه");
        Calendar._FD = 6;
        Calendar._MN = new Array("ژانویه", "فوریه", "مارس", "آوریل", "می", "جون", "جولای", "آگوست", "سپتامبر", "اکتبر", "نوامبر", "دسامبر");
        Calendar._JMN = new Array("حمل", "ثور", "جوزا", "سرطان", "اسد", "سنبله", "میزان", "عقرب", "قوس", "جدی", "دلوه", "حوت");
        Calendar._JSMN = new Array("حمل", "ثور", "جوزا", "سرطان", "اسد", "سنبله", "میزان", "عقرب", "قوس", "جدی", "دلوه", "حوت");
        Calendar._TT = {};
        Calendar._TT["INFO"] = "";
        Calendar._TT["ABOUT"] = "";
        Calendar._TT["PREV_YEAR"] = "سال قبل (دکمه را نگه دارید)";
        Calendar._TT["PREV_MONTH"] = "ماه قبل (دکمه را نگه دارید)";
        Calendar._TT["GO_TODAY"] = "رفتن به امروز";
        Calendar._TT["NEXT_MONTH"] = "ماه بعد (دکمه را نگه دارید)";
        Calendar._TT["NEXT_YEAR"] = "سال بعد (دکمه را نگه دارید)";
        Calendar._TT["SEL_DATE"] = "";
        Calendar._TT["DRAG_TO_MOVE"] = "";
        Calendar._TT["PART_TODAY"] = " (امروز)";
        Calendar._TT["DAY_FIRST"] = "b";
        Calendar._TT["SELECT_COLUMN"] = "انتخاب تمام %s‌های این ماه";
        Calendar._TT["SELECT_ROW"] = "انتخاب روزهای این هفته";
        Calendar._TT["WEEKEND"] = "5";
        Calendar._TT["CLOSE"] = "b";
        Calendar._TT["TODAY"] = "امروز";
        Calendar._TT["TIME_PART"] = "(Shift-)Click or drag to change value";
        Calendar._TT["DEF_DATE_FORMAT"] = "%Y %m %d";
        Calendar._TT["TT_DATE_FORMAT"] = "%A %e %b %Y";
        Calendar._TT["WK"] = "هفته";
        Calendar._TT["TIME"] = "";
        Calendar._TT["LAM"] = "";
        Calendar._TT["AM"] = "";
        Calendar._TT["LPM"] = "";
        Calendar._TT["PM"] = "";
        Calendar._NUMBERS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        Calendar._DIR = 'rtl';
        document.getElementById('calendar-hold').innerHTML = '';
        Calendar.setup({
            flatCallback: dateChanged
        });
    } else {
        Calendar._DN = new Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
        Calendar._SDN = new Array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun");
        Calendar._FD = 1;
        Calendar._MN = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        Calendar._SMN = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        Calendar._TT = {};
        Calendar._TT["INFO"] = "";
        Calendar._TT["ABOUT"] = "";
        Calendar._TT["PREV_YEAR"] = "Prev. year (hold for menu)";
        Calendar._TT["PREV_MONTH"] = "Prev. month (hold for menu)";
        Calendar._TT["GO_TODAY"] = "Go Today";
        Calendar._TT["NEXT_MONTH"] = "Next month (hold for menu)";
        Calendar._TT["NEXT_YEAR"] = "Next year (hold for menu)";
        Calendar._TT["SEL_DATE"] = "calendar by HRMS-Developers";
        Calendar._TT["DRAG_TO_MOVE"] = "";
        Calendar._TT["PART_TODAY"] = " (today)";
        Calendar._TT["DAY_FIRST"] = "x";
        Calendar._TT["SELECT_COLUMN"] = "Select all %ss of this month";
        Calendar._TT["SELECT_ROW"] = "Select all days of this week";
        Calendar._TT["WEEKEND"] = "0,6";
        Calendar._TT["CLOSE"] = "Close";
        Calendar._TT["TODAY"] = "Today";
        Calendar._TT["TIME_PART"] = "(Shift-)Click or drag to change value";
        Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
        Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e, %Y";
        Calendar._TT["WK"] = "";
        Calendar._TT["TIME"] = ":";
        Calendar._TT["LAM"] = "";
        Calendar._TT["AM"] = "";
        Calendar._TT["LPM"] = "";
        Calendar._TT["PM"] = "";
        Calendar._NUMBERS = null;
        Calendar._DIR = 'ltr';
        document.getElementById('calendar-hold').innerHTML = '';
        Calendar.setup({
            flatCallback: dateChanged
        });

    }
}
