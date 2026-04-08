@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            {{-- Page Header --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">
                            <i class="bx bx-hospital me-2 text-primary"></i>
                            {{ localize('global.hospitalizations') ?: 'Hospitalizations' }}
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Simple Filter (no accordion) --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-none border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bx bx-filter-alt text-primary me-2"></i>{{ localize('global.filter') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hospitalizations.index') }}" id="filterForm">
                        <div class="row g-3">
                            {{-- Search Input --}}
                            <div class="col-md-4">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="bx bx-search me-1 text-primary"></i>{{ localize('global.search') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="bx bx-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}"
                                           placeholder="{{ localize('global.search_by_patient_room_bed') ?: 'Search by patient, room, bed...' }}"
                                           autocomplete="off">
                                </div>
                            </div>

                            {{-- Room Filter --}}
                            <div class="col-md-3">
                                <label for="room_id" class="form-label fw-semibold">
                                    <i class="bx bx-building me-1 text-info"></i>{{ localize('global.room') }}
                                </label>
                                <select class="form-select select2" id="room_id" name="room_id">
                                    <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Date From (Dari datepicker) --}}
                            <div class="col-md-2">
                                <label for="date_from" class="form-label fw-semibold">
                                    <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.date_from') ?: 'Date From' }}
                                </label>
                                <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el" id="date_from" name="date_from"
                                       value="{{ request('date_from') }}" placeholder="1403/01/01" autocomplete="off">
                            </div>

                            {{-- Date To (Dari datepicker) --}}
                            <div class="col-md-2">
                                <label for="date_to" class="form-label fw-semibold">
                                    <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.date_to') ?: 'Date To' }}
                                </label>
                                <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el" id="date_to" name="date_to"
                                       value="{{ request('date_to') }}" placeholder="1403/01/01" autocomplete="off">
                            </div>

                            {{-- Filter Buttons --}}
                            <div class="col-md-1 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-filter me-1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-12 d-flex justify-content-end gap-2">
                                <a href="{{ route('hospitalizations.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Active Filters Display --}}
            @if(request()->hasAny(['search', 'room_id', 'date_from', 'date_to']))
                <div class="card mb-3 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-semibold">
                                    <i class="bx bx-filter me-1 text-primary"></i>{{ localize('global.active_filters') ?: 'Active Filters' }}:
                                </h6>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @if(request('search'))
                                        <span class="badge bg-primary">
                                            {{ localize('global.search') }}: {{ request('search') }}
                                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('room_id'))
                                        <span class="badge bg-info">
                                            {{ localize('global.room') }}: {{ $rooms->find(request('room_id'))->name ?? '' }}
                                            <a href="{{ request()->fullUrlWithQuery(['room_id' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('date_from'))
                                        <span class="badge bg-secondary">
                                            {{ localize('global.from') }}: {{ request('date_from') }}
                                            <a href="{{ request()->fullUrlWithQuery(['date_from' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('date_to'))
                                        <span class="badge bg-secondary">
                                            {{ localize('global.to') }}: {{ request('date_to') }}
                                            <a href="{{ request()->fullUrlWithQuery(['date_to' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Results Card --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul me-2 text-primary"></i>
                        {{ localize('global.patients_list') ?: 'Patients List' }}
                    </h5>
                    <div class="text-muted">
                        {{ localize('global.total') ?: 'Total' }}: {{ $hospitalizations->total() }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover border-top">
                            <thead>
                                <tr>
                                    <th>{{localize('global.id')}}</th>
                                    <th>{{localize('global.card_number')}}</th>
                                    <th>{{localize('global.patient_name')}}</th>
                                    <th>{{localize('global.father_name')}}</th>
                                    <th>{{ localize('global.department') ?: 'Department' }}</th>
                                    <th>{{localize('global.room')}}</th>
                                    <th>{{localize('global.bed')}}</th>
                                    <th>{{localize('global.doctor')}}</th>
                                    <th>{{localize('global.hospitalization_date')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hospitalizations as $hospitalization)
                                    <tr>
                                        <td>{{ $hospitalization->id }}</td>
                                        <td>
                                            @if($hospitalization->patient)
                                                <span class="badge bg-secondary">{{ $hospitalization->patient->id_card ?? '' }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($hospitalization->patient)
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bx-user me-2 text-primary"></i>
                                                    <strong>{{ $hospitalization->patient->name ?? 'N/A' }}</strong>
                                                </div>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($hospitalization->patient)
                                                <span class="text-muted">{{ $hospitalization->patient->father_name ?? '-' }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($hospitalization->appointment && $hospitalization->appointment->department)
                                                <span class="badge bg-label-primary">
                                                    <i class="bx bx-buildings me-1"></i>{{ $hospitalization->appointment->department->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hospitalization->room)
                                                <span class="badge bg-label-info">
                                                    <i class="bx bx-building me-1"></i>{{ $hospitalization->room->name }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($hospitalization->bed)
                                                <span class="badge bg-label-success">
                                                    <i class="bx bx-bed me-1"></i>{{ $hospitalization->bed->number }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($hospitalization->doctor)
                                                <span class="text-primary">
                                                    <i class="bx bx-user-md me-1"></i>{{ $hospitalization->doctor->name }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                <i class="bx bx-calendar me-1"></i>{{ $hospitalization->jalali_date ?? 'Not set' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('hospitalizations.show', $hospitalization->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="{{ localize('global.view') ?: 'View' }}">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-inbox me-2"></i>
                                                {{ localize('global.no_data_found') ?: 'No data found' }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Laravel Pagination --}}
                    @if($hospitalizations->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                {{ localize('global.showing') ?: 'Showing' }} 
                                {{ $hospitalizations->firstItem() }} 
                                {{ localize('global.to') ?: 'to' }} 
                                {{ $hospitalizations->lastItem() }} 
                                {{ localize('global.of') ?: 'of' }} 
                                {{ $hospitalizations->total() }} 
                                {{ localize('global.results') ?: 'results' }}
                            </div>
                            <div>
                                {{ $hospitalizations->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('custom-css')
    <style>
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .badge {
            font-weight: 500;
        }

        /* Select2 Styles */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 0;
            padding-right: 20px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 10px;
        }

        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-results__option {
            padding: 0.5rem 0.75rem;
        }

        .select2-results__option--highlighted {
            background-color: #0d6efd;
            color: white;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 20px;
        }

        .table th {
            text-align: right;
        }

        .table td {
            text-align: right;
        }
    </style>
@endpush

@push('custom-js')
    <script>
        $(document).ready(function() {
            // Function to initialize Select2
            function initSelect2() {
                if (typeof $.fn.select2 === 'undefined') {
                    console.error('Select2 is not loaded');
                    return;
                }
                
                $('.select2').each(function() {
                    var $select = $(this);
                    
                    // Skip if select has no options (except the default "All" option)
                    if ($select.find('option').length <= 1) {
                        console.warn('Select element has no options:', $select.attr('id'));
                        return;
                    }
                    
                    // Destroy existing Select2 if already initialized
                    if ($select.hasClass('select2-hidden-accessible')) {
                        try {
                            $select.select2('destroy');
                        } catch(e) {
                            // If destroy fails, remove select2 classes manually
                            $select.removeClass('select2-hidden-accessible');
                            $select.next('.select2-container').remove();
                        }
                    }
                    
                    // Initialize Select2
                    try {
                        $select.select2({
                            width: '100%',
                            placeholder: '{{ localize("global.select") }}...',
                            allowClear: true,
                            language: {
                                noResults: function() {
                                    return '{{ localize("global.no_results_found") ?: "No results found" }}';
                                }
                            }
                        });
                    } catch(e) {
                        console.error('Error initializing Select2:', e);
                    }
                });
            }

            // Initialize Select2 on page load with a small delay
            setTimeout(function() {
                initSelect2();
            }, 200);
        });
    </script>
@endpush
