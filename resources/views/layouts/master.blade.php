<!DOCTYPE html>

<html class="light-style layout-navbar-fixed layout-menu-fixed" lang="en"
      direction="{{ session()->has('language') && session()->get('language') == 'en' ? 'ltr' : 'rtl' }}"
      dir="{{ session()->has('language') && session()->get('language') == 'en' ? 'ltr' : 'rtl' }}"
      style="direction: {{ session()->has('language') && session()->get('language') == 'en' ? 'ltr' : 'rtl' }}"
      data-theme="theme-default" data-assets-path="{{ asset('assets/') }}"
      data-template="vertical-menu-template-no-customizer">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>{{ localize('global.system_name') }}</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/theme-default.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/theme-default-dark.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/theme-semi-dark.css')}}" class="template-customizer-theme-css" />

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
        </style>
    @endif

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
</body>


<script type="text/javascript">
    $(function() {

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
    $(document).ready(function() {
    $('[name = themeRadios]').click(function() {
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

</html>
