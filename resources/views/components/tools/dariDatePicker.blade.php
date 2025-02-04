@props([
    'withButton' => '',
    'withID' => 'datePicker',
    'withButtonName' => '',
    'withPlaceHolder' => '',
    'withSize' => '1',
    'name' => '',
    'dateRequired' => true,
    'extraClasses' => '',
    'value' => '',
])

<input name="{{ $name }}" type="text" readonly class="form-control datepicker_dari {{ $extraClasses }}"
    {{ $dateRequired == true ? 'required' : '' }} id="{{ $withID }}" placeholder="{{ $withPlaceHolder }}"
    aria-describedby="button-addon2" value="{{ $value }}">
@if ($withButton)
    <button class="btn btn-outline-primary btn-sm" type="button"
        id="btn-{{ $withID }}">{{ $withButtonName }}</button>
@endif




@pushOnce('pscript')
    <script>
        let is_rtl = "{{ Session::get('lang') }}" == 'en' ? false : true;
        let required = true;
        let sizes = "{{ $withSize }}";
        let setSize = 45;
        switch (sizes) {
            case '1':
                setSize = 25;
                break;
            case '2':
                setSize = 30;
                break;
            case '3':
                setSize = 35;
                break;
            default:

        }

        $(function() {
            $(".datepicker_dari").persianDatepicker({
                isRTL: is_rtl,
                cellWidth: setSize,
                formatDate: 'YYYY-MM-DD',
                dateRequired: required,
            });
        });
    </script>
@endPushOnce
