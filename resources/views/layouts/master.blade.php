<!DOCTYPE html>

@php
    $textDir = session()->has('language') && session()->get('language') == 'en' ? 'ltr' : 'rtl';
@endphp
<html class="light-style layout-navbar-fixed layout-menu-fixed" lang="en"
    dir="{{ $textDir }}"
    data-text-dir="{{ $textDir }}"
    data-theme="theme-default" data-assets-path="{{ asset('assets/') }}"
    data-template="vertical-menu-template-no-customizer">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>{{ localize('global.system_name') }}</title>

    <meta name="description" content="" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <script>
        (function () {
            try {
                var template = document.documentElement.getAttribute('data-template') || 'vertical-menu-template';
                var key = 'templateCustomizer-' + template + '--PrimaryColor';
                var map = {
                    default: '#696cff',
                    raspberry: '#e30b5c',
                    ocean: '#00cfe8',
                    forest: '#28c76f',
                    amber: '#ff9f43',
                    blue: '#2092ec',
                    violet: '#9c27b0',
                    slate: '#4b465c'
                };
                var saved = localStorage.getItem(key);
                var hex = map[saved] || (/^#([0-9a-fA-F]{6})$/.test(saved || '') ? saved : '#696cff');
                var r = parseInt(hex.slice(1, 3), 16), g = parseInt(hex.slice(3, 5), 16), b = parseInt(hex.slice(5, 7), 16);
                document.documentElement.style.setProperty('--bs-primary', hex);
                document.documentElement.style.setProperty('--bs-primary-rgb', r + ', ' + g + ', ' + b);
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/theme-default.css')}}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/theme-default-dark.css')}}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/theme-semi-dark.css')}}"
        class="template-customizer-theme-css" />

    <!-- Fonts -->
    @include('layouts.partial.fonts')

    <!-- Icons -->
    @include('layouts.partial.icons')

    <!-- CSS -->
    @include('layouts.partial.css_links')

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{asset('assets/vendor/js/template-customizer.js')}}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('assets/js/primary-color.js') }}"></script>
    <style>
        .template-customizer-primary-colors-options {
            gap: 0.5rem;
        }
        .app-primary-color-swatch {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 50%;
            border: 2px solid transparent;
            box-shadow: 0 0 0 1px rgba(67, 89, 113, 0.2);
            padding: 0;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .app-primary-color-swatch:hover {
            transform: scale(1.08);
        }
        .app-primary-color-swatch.active {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px currentColor;
            outline: 2px solid rgba(67, 89, 113, 0.45);
            outline-offset: 2px;
        }
    </style>
    @if (session()->get('language') == 'en')
        <style type="text/css">
            @font-face {
                font-family: "eng_font";
                src: url({{ asset('assets/fonts/eng.ttf') }});
            }

            body,
            body *,
            .label {
                font-family: eng_font;
            }
        </style>
    @else
        <style type="text/css">
            @font-face {
                font-family: "persian_font";
                src: url({{ asset('assets/fonts/mod_font.ttf') }});
            }

            body,
            body *,
            .label {
                font-family: persian_font;
            }
            .multiselect__input, .multiselect__placeholder {
                color: black;
            }
            
            /* Dark mode styles for multiselect */
            .dark-style .multiselect__input,
            .dark-style .multiselect__placeholder,
            body[data-theme="dark"] .multiselect__input,
            body[data-theme="dark"] .multiselect__placeholder,
            html[data-theme="dark"] .multiselect__input,
            html[data-theme="dark"] .multiselect__placeholder,
            .theme-dark .multiselect__input,
            .theme-dark .multiselect__placeholder {
                color: #a3a4cc !important;
            }
            
            .dark-style .multiselect__input::placeholder,
            body[data-theme="dark"] .multiselect__input::placeholder,
            html[data-theme="dark"] .multiselect__input::placeholder,
            .theme-dark .multiselect__input::placeholder {
                color: #7c7db6 !important;
            }
        </style>
    @endif

    <!-- Select2 in modals: ensure dropdown appears above modal (options not behind modal) -->
    <style type="text/css">
        .modal .select2-dropdown,
        .modal .select2-container--open {
            z-index: 1060 !important;
        }
        .modal .select2-container--default .select2-selection--single,
        .modal .select2-container--default .select2-selection--multiple {
            z-index: 0;
        }
    </style>

    @yield('styles')
    
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.customizer')
            @include('layouts.partial.sidebar')


            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts.partial.navbar')

                <!-- / Navbar -->

                @yield('content')

            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>

    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    @include('layouts.partial.js_links')

    @yield('scripts')
    @stack('scripts')
</body>


<script type="text/javascript">
    $(function () {

        initHijrDatePicker();
    });




    function initHijrDatePicker() {

        $(".hijri-date-input").hijriDatePicker({
            locale: "ar-sa",
            format: "DD-MM-YYYY",
            hijriFormat: "iYYYY-iMM-iDD",
            dayViewHeaderFormat: "MMMM YYYY",
            hijriDayViewHeaderFormat: "iMMMM iYYYY",
            showSwitcher: true,
            allowInputToggle: true,
            showTodayButton: true,
            useCurrent: false,
            isRTL: false,
            viewMode: 'days',
            keepOpen: false,
            hijri: true,
            debug: false,
            showClear: true,
            showTodayButton: true,
            showClose: true
        });
    }
</script>
<script>
    $(document).ready(function () {
        $('[name = themeRadios]').click(function () {
            window.location.reload();
        });

        $('.template-customizer-t-panel_header').html("{{localize('global.system_theme_settings')}}");
        $('.template-customizer-t-panel_sub_header').html("{{localize('global.system_theme_subtext')}}");
        $('.template-customizer-t-theming_header').html("{{localize('global.system_theme_system_styles')}}");
        $('.template-customizer-t-theme_label').html("{{localize('global.system_theme_styles')}}");
        $('[for = themeRadiostheme-default]').html("{{localize('global.system_theme_default')}}");
        $('[for = themeRadiostheme-semi-dark]').html("{{localize('global.system_theme_semi_dark')}}");
        $('[for = themeRadiostheme-bordered]').html("{{localize('global.system_theme_bordered')}}");
        $('.template-customizer-t-style_label').html("{{localize('global.system_theme_dark')}}");
        $('.template-customizer-t-style_switch_light').html("{{localize('global.system_theme_switch_light')}}");
        $('.template-customizer-t-style_switch_dark').html("{{localize('global.system_theme_switch_dark')}}");
        window.__appPrimaryColorsLabel = @json(localize('global.system_theme_primary_colors'));
        $('.template-customizer-t-primary_colors_label').html(window.__appPrimaryColorsLabel);
        if (window.AppPrimaryColor && typeof window.AppPrimaryColor.mountSwatches === 'function') {
            window.AppPrimaryColor.mountSwatches();
        }
        $('.template-customizer-t-layout_header').html("{{localize('global.system_theme_system_layout')}}");
        $('.template-customizer-t-layout_label').html("{{localize('global.system_theme_layouts')}}");

        $('[for = layoutRadios-static]').html("{{localize('global.system_theme_static')}}");
        $('[for = layoutRadios-fixed]').html("{{localize('global.system_theme_fixed')}}");
        $('.template-customizer-t-layout_navbar_label').html("{{localize('global.system_theme_fixed_navbar')}}");
        $('.template-customizer-t-layout_footer_label').html("{{localize('global.system_theme_fixed_footer')}}");
        $('.template-customizer-t-layout_dd_open_label').html("{{localize('global.system_theme_dropdown')}}");


        $('.template-customizer-t-misc_header').html("{{localize('global.system_theme_misc')}}");
        $('.template-customizer-t-rtl_label').html("{{localize('global.system_theme_rtl')}}");
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $(".datepicker_dari").persianDatepicker({
            months: ["حمل", "ثور", "جوزا", "سرطان", "اسد", "سنبله", "میزان", "عقرب", "قوس", "جدی", "دلو", "حوت"],
            dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
        shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
        showGregorianDate: !1,
        persianNumbers: !0,
        formatDate: "YYYY/MM/DD",
        selectedBefore: !1,
        selectedDate: null,
        startDate: null,
        endDate: null,
        prevArrow: '\u25c4',
        nextArrow: '\u25ba',
        theme: 'default',
        alwaysShow: !1,
        selectableYears: null,
        selectableMonths: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        cellWidth: 25, // by px
        cellHeight: 20, // by px
        fontSize: 13, // by px
        isRTL: !1,
        calendarPosition: {
            x: 0,
            y: 0,
        },
        onShow: function () {},
        onHide: function () {},
        onSelect: function () {},
        onRender: function () {}
    });
        // Disable autocomplete for all date inputs (Persian picker and native date)
        $(".datepicker_dari").attr("autocomplete", "off");
        $('input[type="date"]').attr("autocomplete", "off");
    });
</script>

</html>