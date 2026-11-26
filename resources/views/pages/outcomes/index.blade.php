@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">{{ localize('global.medicine_usage_statistics') }}</h5>
                        </div>
                        <div>
                            <form action="{{ route('outcomes.export-index-report') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="medicine_id" value="{{ request('medicine_id') }}">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="pharmacy_id" value="{{ request('pharmacy_id') ?: ($selectedPharmacyId ?? '') }}">
                                <input type="hidden" name="pharmacy_user_id" value="{{ request('pharmacy_user_id') }}">
                                <input type="hidden" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                <input type="hidden" name="all_dates" value="{{ request('all_dates') ? 1 : 0 }}">
                                <input type="hidden" name="sort_by" value="{{ request('sort_by', 'usage_count') }}">
                                <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                                <button type="submit" name="type" value="excel" class="btn btn-success btn-sm">
                                    <i class="bx bx-file me-1"></i>{{ localize('global.export_excel') }}
                                </button>
                                <button type="submit" name="type" value="pdf" class="btn btn-danger btn-sm">
                                    <i class="bx bx-file me-1"></i>{{ localize('global.export_pdf') }}
                                </button>
                            </form>
                            <a href="{{ route('outcomes.report') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-file me-1"></i>{{ localize('global.reports') }}
                            </a>
                        </div>
                    </div>

                    <!-- Advanced Search Form -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('outcomes.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="medicine_id" class="form-label">{{ localize('global.medicine') }}</label>
                                <select class="form-select select2" id="medicine_id" name="medicine_id"
                                        data-placeholder="{{ localize('global.all_medicines') ?: localize('global.search_by_medicine') }}">
                                    <option value="">{{ localize('global.all_medicines') ?: localize('global.all') }}</option>
                                    @foreach (($medicines ?? []) as $medicine)
                                        <option value="{{ $medicine->id }}" {{ (string) request('medicine_id') === (string) $medicine->id ? 'selected' : '' }}>
                                            {{ $medicine->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($pharmacies && $pharmacies->count() > 0)
                            <div class="col-md-2">
                                <label for="pharmacy_id" class="form-label">{{ localize('global.pharmacy') }}</label>
                                <select class="form-select select2" id="pharmacy_id" name="pharmacy_id"
                                        data-placeholder="{{ localize('global.all_pharmacies') }}">
                                    <option value="">{{ localize('global.all_pharmacies') }}</option>
                                    @foreach ($pharmacies as $pharmacy)
                                        <option value="{{ $pharmacy->id }}" {{ (string) ($selectedPharmacyId ?? request('pharmacy_id')) === (string) $pharmacy->id ? 'selected' : '' }}>
                                            {{ $pharmacy->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @elseif(!empty($selectedPharmacyId))
                                <input type="hidden" name="pharmacy_id" value="{{ $selectedPharmacyId }}">
                            @endif
                            <div class="col-md-2">
                                <label for="pharmacy_user_id" class="form-label">{{ localize('global.pharmacy_user') ?: localize('global.updated_by') }}</label>
                                <select class="form-select select2" id="pharmacy_user_id" name="pharmacy_user_id"
                                        data-placeholder="{{ localize('global.select') }}"
                                        @if(empty($selectedPharmacyId)) disabled @endif>
                                    <option value="">{{ localize('global.all') ?: localize('global.select') }}</option>
                                    @foreach (($pharmacyUsers ?? []) as $pharmacyUser)
                                        <option value="{{ $pharmacyUser['id'] }}" {{ (string) request('pharmacy_user_id') === (string) $pharmacyUser['id'] ? 'selected' : '' }}>
                                            {{ $pharmacyUser['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ localize('global.select_pharmacy_first_for_user') }}</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                <div class="input-group input-daterange">
                                    <input type="text" autocomplete="off" name="date_from"
                                        placeholder="{{ localize('global.from') }}"
                                        class="form-control form-control datepicker_dari pdp-el persian-date" 
                                        value="{{ $dateFrom ?? request('date_from') }}" />
                                    <span class="input-group-text">...</span>
                                    <input type="text" autocomplete="off" name="date_to"
                                        placeholder="{{ localize('global.to') }}"
                                        class="form-control form-control datepicker_dari pdp-el persian-date" 
                                        value="{{ $dateTo ?? request('date_to') }}" />
                                </div>
                                @if(!empty($usingDefaultDates))
                                    <small class="text-muted">{{ localize('global.showing_current_month_by_default') ?: 'پیش‌فرض: ماه جاری (برای سرعت بیشتر)' }}</small>
                                @endif
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="all_dates" value="1" id="all_dates"
                                        {{ request('all_dates') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="all_dates">
                                        {{ localize('global.all_dates') ?: 'همه تاریخ‌ها' }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="per_page" class="form-label">{{ localize('global.per_page') }}</label>
                                <select class="form-select" id="per_page" name="per_page">
                                    @foreach([10, 15, 25, 50, 100] as $perPage)
                                        <option value="{{ $perPage }}" {{ request('per_page', 15) == $perPage ? 'selected' : '' }}>
                                            {{ $perPage }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i> {{ localize('global.search') }}
                                </button>
                                <a href="{{ route('outcomes.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh"></i> {{ localize('global.clear') }}
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <!-- Results Summary -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-muted">
                                    {{ localize('global.showing') }} {{ $outcomes->firstItem() ?? 0 }} 
                                    {{ localize('global.to') }} {{ $outcomes->lastItem() ?? 0 }} 
                                    {{ localize('global.of') }} {{ $outcomes->total() }} 
                                    {{ localize('global.results') }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="sort_by" class="form-label me-2 mb-0">{{ localize('global.sort_by') }}:</label>
                                <select class="form-select form-select-sm" id="sort_by" name="sort_by" style="width: auto;" onchange="updateSort()">
                                    <option value="usage_count" {{ request('sort_by', 'usage_count') == 'usage_count' ? 'selected' : '' }}>{{ localize('global.usage_count') }}</option>
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>{{ localize('global.medicine') }}</option>
                                    <option value="pharmacy_name" {{ request('sort_by') == 'pharmacy_name' ? 'selected' : '' }}>{{ localize('global.pharmacy') }}</option>
                                    <option value="prescription_updated_at" {{ request('sort_by') == 'prescription_updated_at' ? 'selected' : '' }}>{{ localize('global.prescription_completed_date') }}</option>
                                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>{{ localize('global.id') }}</option>
                                </select>
                                <select class="form-select form-select-sm ms-2" id="sort_order" name="sort_order" style="width: auto;" onchange="updateSort()">
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>{{ localize('global.descending') }}</option>
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>{{ localize('global.ascending') }}</option>
                                </select>
                            </div>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.medicine') }}</th>
                                    <th>{{ localize('global.pharmacy') }}</th>
                                    <th>{{ localize('global.usage_count') }}</th>
                                    <th>{{ localize('global.updated_by') }}</th>
                                    <th>{{ localize('global.prescription_completed_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($outcomes as $row)
                                    <tr>
                                        <td>{{ $row->name ?? 'N/A' }}</td>
                                        <td>{{ $row->pharmacy_name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $row->usage_count ?? 0 }}</span>
                                        </td>
                                        <td>{{ $row->updated_by_name ? trim($row->updated_by_name) : '—' }}</td>
                                        <td>
                                            @if(!empty($row->prescription_updated_at))
                                                {{ \Hekmatinasser\Verta\Facades\Verta::instance($row->prescription_updated_at)->format('Y/m/d H:i') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ localize('global.no_medicine_usage_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <!-- Enhanced Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                {{ localize('global.page') }} {{ $outcomes->currentPage() }} 
                                {{ localize('global.of') }} {{ $outcomes->lastPage() }}
                            </div>
                            <div>
                                {{ $outcomes->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateSort() {
            const sortBy = document.getElementById('sort_by').value;
            const sortOrder = document.getElementById('sort_order').value;
            const url = new URL(window.location);
            url.searchParams.set('sort_by', sortBy);
            url.searchParams.set('sort_order', sortOrder);
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (!(window.jQuery && jQuery.fn.select2)) {
                return;
            }

            const pharmacyUsersUrl = @json(route('outcomes.pharmacy-users', ['pharmacy' => '__PHARMACY_ID__']));
            const selectedPharmacyUserId = @json((string) request('pharmacy_user_id', ''));
            const $pharmacySelect = jQuery('#pharmacy_id');
            const $userSelect = jQuery('#pharmacy_user_id');

            function initSelect2(selector) {
                jQuery(selector).each(function () {
                    const $el = jQuery(this);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }
                    $el.select2({
                        width: '100%',
                        allowClear: true,
                        placeholder: $el.data('placeholder') || ''
                    });
                });
            }

            function resetPharmacyUsers(disabled) {
                $userSelect.empty().append(
                    jQuery('<option>', { value: '', text: @json(localize('global.all') ?: localize('global.select')) })
                );
                $userSelect.prop('disabled', !!disabled);
                initSelect2('#pharmacy_user_id');
            }

            function loadPharmacyUsers(pharmacyId, preferredUserId) {
                if (!pharmacyId) {
                    resetPharmacyUsers(true);
                    return;
                }

                $userSelect.prop('disabled', true);
                jQuery.get(pharmacyUsersUrl.replace('__PHARMACY_ID__', pharmacyId))
                    .done(function (users) {
                        resetPharmacyUsers(false);
                        (users || []).forEach(function (user) {
                            $userSelect.append(
                                jQuery('<option>', { value: user.id, text: user.name || ('User #' + user.id) })
                            );
                        });
                        if (preferredUserId) {
                            $userSelect.val(String(preferredUserId)).trigger('change');
                        }
                        initSelect2('#pharmacy_user_id');
                    })
                    .fail(function () {
                        resetPharmacyUsers(false);
                    });
            }

            initSelect2('#medicine_id, #pharmacy_id, #pharmacy_user_id');

            if ($pharmacySelect.length) {
                $pharmacySelect.on('change', function () {
                    loadPharmacyUsers(jQuery(this).val(), '');
                });
            }

            const initialPharmacyId = $pharmacySelect.length
                ? $pharmacySelect.val()
                : @json((string) ($selectedPharmacyId ?? ''));

            if (initialPharmacyId && $userSelect.find('option').length <= 1) {
                loadPharmacyUsers(initialPharmacyId, selectedPharmacyUserId);
            }
        });
    </script>
@endsection
@push('custom-css')
    <style>
        .persian-date {
            direction: rtl;
            text-align: right;
        }
        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush