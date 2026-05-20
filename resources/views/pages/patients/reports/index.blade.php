@extends('layouts.master')
@section('title', ' گزارش')
@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Basic Bootstrap Table -->
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
                            <form method="GET" action="{{ route('patients.report-search') }}">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label for="patient_name"
                                            class="form-label">{{ localize('global.patient_name') }}</label>
                                        <input type="text" class="form-control" name="patient_name"
                                            value="{{ $filters['patient_name'] ?? old('patient_name') }}"
                                            placeholder="{{ localize('global.patient_name') }}" />
                                    </div>

                                    <div class="col-md-3">
                                        <label for="nid">{{ localize('global.nid') }}</label>
                                        <input type="text" name="nid" value="{{ $filters['nid'] ?? old('nid') }}"
                                            class="form-control">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="id_card">{{ localize('global.id_card') }}</label>
                                        <input type="text" name="id_card" id="id_card" value="{{ $filters['id_card'] ?? old('id_card') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="referral_name"
                                            class="form-label">{{ localize('global.referral_name') }}</label>
                                        <input type="text" class="form-control" name="referral_name"
                                            value="{{ $filters['referral_name'] ?? old('referral_name') }}"
                                            placeholder="{{ localize('global.referral_name') }}" />
                                    </div>

                                    <div class="col-md-3">
                                        <label for="age">{{ localize('global.age') }}</label>
                                        <input type="text" name="age" id="age" value="{{ $filters['age'] ?? old('age') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="gender">{{ localize('global.gender') }}</label>
                                        <select class="form-control select2" name="gender" id="gender">
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="0" {{ ($filters['gender'] ?? old('gender')) == '0' ? 'selected' : '' }}>{{localize('global.male')}}</option>
                                            <option value="1" {{ ($filters['gender'] ?? old('gender')) == '1' ? 'selected' : '' }}>{{localize('global.female')}}</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="job_category">{{ localize('global.job_category') }}</label>
                                        <select class="form-control select2" name="job_category" id="job_category">
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="0" {{ ($filters['job_category'] ?? old('job_category')) == '0' ? 'selected' : '' }}>{{localize('global.military')}}</option>
                                            <option value="1" {{ ($filters['job_category'] ?? old('job_category')) == '1' ? 'selected' : '' }}>{{localize('global.civilian')}}</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="disease_type"
                                            class="form-label">{{ localize('global.disease_type') }}</label>
                                        <select class="form-control select2" name="type">
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="0" {{ ($filters['type'] ?? old('type')) == '0' ? 'selected' : '' }}>{{ localize('global.mod') }}</option>
                                            <option value="1" {{ ($filters['type'] ?? old('type')) == '1' ? 'selected' : '' }}>{{ localize('global.recipient') }}</option>
                                            <option value="2" {{ ($filters['type'] ?? old('type')) == '2' ? 'selected' : '' }}>{{ localize('global.family') }}</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="referred_by">{{ localize('global.referred_by') }}</label>
                                        <select class="form-control select2" name="referred_by">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($recipients as $value)
                                                <option value="{{ $value->id }}" {{ ($filters['referred_by'] ?? old('referred_by')) == $value->id ? 'selected' : '' }}> {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="province_id">{{ localize('global.province') }}</label>
                                            <select class="form-control select2" name="province_id" id="province_id" onchange="this.form.submit()">
                                                <option value="">{{ localize('global.select') }}</option>
                                                @foreach ($provinces as $value)
                                                    <option value="{{ $value->id }}" {{ ($filters['province_id'] ?? old('province_id')) == $value->id ? 'selected' : '' }}> {{ $value->name_dr }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="district_id">{{ localize('global.district') }}</label>
                                            <select class="form-control select2" name="district_id" id="district_id">
                                                <option value="">{{ localize('global.select') }}</option>
                                                @if(!empty($filters['province_id']))
                                                    @php $selectedProvince = $provinces->firstWhere('id', $filters['province_id']); @endphp
                                                    @if($selectedProvince && $selectedProvince->districts)
                                                        @foreach($selectedProvince->districts as $d)
                                                            <option value="{{ $d->id }}" {{ ($filters['district_id'] ?? '') == $d->id ? 'selected' : '' }}>{{ $d->name_dr }}</option>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                        <div class="input-group input-daterange">
                                            <input type="text" autocomplete="off" name="from" id="from_date"
                                                value="{{ $filters['from'] ?? old('from') }}"
                                                placeholder="{{ localize('global.from') }}"
                                                class="form-control datepicker_dari pdp-el" />
                                            <span class="input-group-text">...</span>
                                            <input type="text" autocomplete="off" name="to" id="to_date"
                                                value="{{ $filters['to'] ?? old('to') }}"
                                                placeholder="{{ localize('global.to') }}"
                                                class="form-control datepicker_dari pdp-el" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-label-primary">
                                            <i class="fa fa-search m-2"></i> <span>{{ localize('global.documents.search') }}</span>
                                        </button>
                                        <a href="{{ route('patients.report') }}" class="btn btn-label-secondary">
                                            <i class="fa fa-history m-2"></i>
                                            <span>{{ localize('global.reset') }}</span>
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="per_page" class="form-label">{{ localize('global.per_page') }}</label>
                                        <select class="form-select" name="per_page" id="per_page">
                                            <option value="10" {{ ($filters['per_page'] ?? 15) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ ($filters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ ($filters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ ($filters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ ($filters['per_page'] ?? 15) == 100 ? 'selected' : '' }}>100</option>
                                            <option value="all" {{ ($filters['per_page'] ?? '') === 'all' ? 'selected' : '' }}>{{ localize('global.all') ?? 'All' }}</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card m-3">
                <div class="table-responsive m-1" id="app">
                    @if(isset($items))
                        {{-- Export --}}
                        <div class="card-body border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1 text-primary fw-semibold">
                                        <i class="fas fa-download me-2"></i>{{ localize('global.export_report') }}
                                    </h5>
                                    <p class="text-muted mb-0 small">{{ localize('global.select_export_format') }}</p>
                                </div>
                                <a href="{{ route('patients.report-search', array_merge($reportExportParams ?? [], ['export' => 'excel'])) }}" class="btn btn-success btn-lg px-4">
                                    <i class="fas fa-file-excel me-2"></i>
                                    <span class="fw-medium">Excel</span>
                                </a>
                                <a href="{{ route('patients.report-search', array_merge($reportExportParams ?? [], ['export' => 'print'])) }}" class="btn btn-danger btn-lg px-4" target="_blank">
                                    <i class="fas fa-print me-2"></i>
                                    <span class="fw-medium">{{ localize('global.print') }}</span>
                                </a>
                            </div>
                        </div>
                        {{-- Table --}}
                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0">{{ localize('global.patient_records') }}</h5>
                            <span class="badge bg-primary rounded-pill">
                                {{ $items->total() }} {{ localize('global.records') }}
                                @if($items->hasPages())
                                    ({{ $items->firstItem() }}-{{ $items->lastItem() }} {{ localize('global.of') }} {{ $items->total() }})
                                @endif
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="print_excel_table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">#</th>
                                            <th>{{ localize('global.patient_name') }}</th>
                                            <th>{{ localize('global.nid') }}</th>
                                            <th>{{ localize('global.id_card') }}</th>
                                            <th>{{ localize('global.referral_name') }}</th>
                                            <th class="text-center">{{ localize('global.age') }}</th>
                                            <th class="text-center">{{ localize('global.gender') }}</th>
                                            <th class="text-center">{{ localize('global.job_category') }}</th>
                                            <th class="text-center">{{ localize('global.disease_type') }}</th>
                                            <th>{{ localize('global.referred_by') }}</th>
                                            <th>{{ localize('global.province') }}</th>
                                            <th>{{ localize('global.district') }}</th>
                                            <th>{{ localize('global.registered_date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($items as $item)
                                            <tr>
                                                <td class="text-center text-muted">{{ $items->firstItem() + $loop->index }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-user text-white small"></i>
                                                        </div>
                                                        <span class="fw-medium">{{ $item->name }}</span>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-light text-dark border">{{ $item->nid }}</span></td>
                                                <td><span class="badge bg-light text-dark border">{{ $item->id_card }}</span></td>
                                                <td><span class="text-muted">{{ $item->referral_name }}</span></td>
                                                <td class="text-center"><span class="badge bg-info rounded-pill">{{ $item->age }}</span></td>
                                                <td class="text-center">
                                                    @if ($item->gender == '0')
                                                        <span class="badge bg-primary rounded-pill">{{ localize('global.male') }}</span>
                                                    @else
                                                        <span class="badge bg-pink rounded-pill">{{ localize('global.female') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($item->job_category == '0')
                                                        <span class="badge bg-warning rounded-pill">{{ localize('global.military') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">{{ localize('global.civilian') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($item->type == '0')
                                                        <span class="badge bg-success rounded-pill">{{ localize('global.mod') }}</span>
                                                    @elseif($item->type == '1')
                                                        <span class="badge bg-info rounded-pill">{{ localize('global.recipient') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">{{ localize('global.family') }}</span>
                                                    @endif
                                                </td>
                                                <td><span class="text-muted">{{ $item->recipient->name ?? '—' }}</span></td>
                                                <td><span class="badge bg-light text-dark border">{{ $item->province->name_dr ?? '—' }}</span></td>
                                                <td><span class="badge bg-light text-dark border">{{ $item->district->name_dr ?? '—' }}</span></td>
                                                <td><span class="badge bg-light text-dark border">{{ verta($item->registration_date)->format('Y/m/d') }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="13" class="text-center py-5 text-muted">
                                                    <i class="fas fa-search fa-3x mb-3"></i>
                                                    <h5>{{ localize('global.no_records_found') }}</h5>
                                                    <p class="mb-0">{{ localize('global.try_adjusting_your_search_criteria') }}</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($items->hasPages())
                                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                                    <div class="text-muted small">
                                        {{ localize('global.showing') }} {{ $items->firstItem() }} {{ localize('global.to') }} {{ $items->lastItem() }}
                                        {{ localize('global.of') }} {{ $items->total() }} {{ localize('global.results') }}
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <form method="GET" action="{{ route('patients.report-search') }}" class="d-inline-flex align-items-center gap-2" id="report-per-page-form">
                                            @foreach($reportExportParams ?? [] as $key => $val)
                                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                            @endforeach
                                            <label for="per-page-select" class="form-label mb-0 small">{{ localize('global.show') }}:</label>
                                            <select class="form-select form-select-sm" name="per_page" id="per-page-select" style="width: auto;" onchange="document.getElementById('report-per-page-form').submit()">
                                                <option value="10" {{ $items->perPage() == 10 ? 'selected' : '' }}>10</option>
                                                <option value="15" {{ $items->perPage() == 15 ? 'selected' : '' }}>15</option>
                                                <option value="25" {{ $items->perPage() == 25 ? 'selected' : '' }}>25</option>
                                                <option value="50" {{ $items->perPage() == 50 ? 'selected' : '' }}>50</option>
                                                <option value="100" {{ $items->perPage() == 100 ? 'selected' : '' }}>100</option>
                                                <option value="all" {{ ($filters['per_page'] ?? '') === 'all' ? 'selected' : '' }}>{{ localize('global.all') ?? 'All' }}</option>
                                            </select>
                                            <span class="small text-muted">{{ localize('global.per_page') }}</span>
                                        </form>
                                        <div>{{ $items->links() }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="bx bx-search-alt fs-1"></i>
                            <p class="mb-0 mt-2">{{ localize('global.use_filters_above_to_search') ?? 'Use the search form above to view patient report.' }}</p>
                        </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            var perPageEl = document.getElementById('per_page');
            if (perPageEl && perPageEl.closest('form')) {
                perPageEl.addEventListener('change', function() { this.closest('form').submit(); });
            }
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