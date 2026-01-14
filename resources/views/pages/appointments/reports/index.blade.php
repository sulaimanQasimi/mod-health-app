@extends('layouts.master')
@section('title', ' گزارش')
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <div class="accordion m-3" id="accordionWithIcon">
                <div class="card accordion-item active">
                    <h2 class="accordion-header d-flex align-items-center">
                        <button type="button" class="accordion-button" data-bs-toggle="collapse"
                            data-bs-target="#accordionWithIcon-1" aria-expanded="true">
                            <i class="bx bx-search"></i>
                            {{ localize('global.documents.search') }}
                        </button>
                    </h2>
                    <div id="accordionWithIcon-1" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <form method="GET" action="{{ route('appointments.report') }}">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label for="patient_name" class="form-label">{{ localize('global.patient_name') }}</label>
                                        <input type="text" class="form-control pager-search" name="patient_name"
                                            value="{{ old('patient_name', request('patient_name')) }}" 
                                            placeholder="{{ localize('global.patient_name') }}" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="doctor_id" class="form-label">{{ localize('global.doctor') }}</label>
                                        <select class="form-control pager-search select2" name="doctor_id" id="doctor_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($doctors ?? [] as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id', request('doctor_id')) == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="processed_by" class="form-label">{{ localize('global.processed_by') }}</label>
                                        <select class="form-control pager-search select2" name="processed_by" id="processed_by">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($users ?? [] as $user)
                                                <option value="{{ $user->id }}" {{ old('processed_by', request('processed_by')) == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="is_completed" class="form-label">{{ localize('global.status') }}</label>
                                        <select class="form-control pager-search select2" name="is_completed">
                                            <option value="" {{ old('is_completed', request('is_completed')) == '' ? 'selected' : '' }}>{{ localize('global.select') }}</option>
                                            <option value="1" {{ old('is_completed', request('is_completed')) == '1' ? 'selected' : '' }}>{{ localize('global.completed_appointments') }}</option>
                                            <option value="0" {{ old('is_completed', request('is_completed')) == '0' ? 'selected' : '' }}>{{ localize('global.ongoing_appointments') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                        <div class="input-group input-daterange">
                                            <input type="text" name="start" 
                                                value="{{ old('start', request('start')) }}"
                                                placeholder="{{ localize('global.from') }}"
                                                class="form-control datepicker_dari" />
                                            <span class="input-group-text">...</span>
                                            <input type="text" name="end" 
                                                value="{{ old('end', request('end')) }}"
                                                placeholder="{{ localize('global.to') }}"
                                                class="form-control datepicker_dari" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="time" class="form-label">{{ localize('global.time') }}</label>
                                        <input type="time" class="form-control pager-search" name="time"
                                            value="{{ old('time', request('time')) }}" 
                                            placeholder="{{ localize('global.time') }}" />
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-label-primary">
                                            <i class="fa fa-search m-2"></i> <span>
                                                {{ localize('global.documents.search') }}</span>
                                        </button>
                                        <button type="button" class="btn btn-label-secondary" id="reset-form-btn">
                                            <i class="fa fa-history m-2"></i>
                                            <span>{{ localize('global.reset') }}</span>
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="per_page" class="form-label">{{ localize('global.per_page') }}</label>
                                        <select class="form-select" name="per_page" id="per_page">
                                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                                            <option value="all" {{ request('per_page', 15) == 'all' ? 'selected' : '' }}>{{ localize('global.all') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive m-1" id="app">
                @if(isset($items))
                    @include('pages.appointments.reports.report', ['items' => $items])
                @endif
            </div>
        </div>
        <!--/ Basic Bootstrap Table -->
    </div>
    <!-- / Content -->
</div>
@endsection

@push('custom-js')
<script>
        // Auto-submit when per_page changes
        $('#per_page').on('change', function() {
            $('form').submit();
        });

        // Handle reset button click
        $('#reset-form-btn').on('click', function(e) {
            e.preventDefault();
            
            // Reset all input fields
            $('form input[type="text"]').val('');
            $('form input[type="time"]').val('');
            
            // Reset Select2 dropdowns
            if ($('#doctor_id').hasClass('select2-hidden-accessible')) {
                $('#doctor_id').val('').trigger('change');
            } else {
                $('#doctor_id').val('');
            }
            
            if ($('#processed_by').hasClass('select2-hidden-accessible')) {
                $('#processed_by').val('').trigger('change');
            } else {
                $('#processed_by').val('');
            }
            
            if ($('select[name="is_completed"]').hasClass('select2-hidden-accessible')) {
                $('select[name="is_completed"]').val('').trigger('change');
            } else {
                $('select[name="is_completed"]').val('');
            }
            
            // Reset per_page to default
            $('#per_page').val('15');
            
            // Clear date pickers
            $('.datepicker_dari').val('');
            
            // Redirect to clean report URL (without query parameters)
            window.location.href = '{{ route("appointments.report") }}';
        });
</script>
@endpush
@push('custom-css')
<style>
.sadira_date_range,
.wareda_date_range {
    display: none;
}
</style>
@endpush