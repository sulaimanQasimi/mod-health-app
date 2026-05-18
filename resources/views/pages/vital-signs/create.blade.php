@extends('layouts.master')

@php
    $hasMorphable = $morphableType && $morphableId;
    $pageTitle = $hasMorphable ? localize('global.manage_vital_signs') : localize('global.create_vital_sign');
    $backUrl = null;
    if ($hasMorphable) {
        $backUrl =
            $morphableType === 'App\\Models\\Hospitalization'
                ? route('hospitalizations.show', $morphableId)
                : route('under_reviews.show', $morphableId);
    }

    $initialRows = old('schedule_rows');
    if ($initialRows === null && $hasMorphable) {
        $initialRows = $schedulesByDate[$defaultScheduleDate] ?? [];
    }
    if (!is_array($initialRows) || count($initialRows) === 0) {
        $initialRows = [['vital_sign_type_id' => '', 'morning_time' => '', 'evening_time' => '']];
    }
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $pageTitle }}</h4>
                        @if ($backUrl)
                            <a href="{{ $backUrl }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> {{ localize('global.back') }}
                            </a>
                        @else
                            <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                            </a>
                        @endif
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (!$hasMorphable)
                            <div class="alert alert-info">
                                {{ localize('global.add_first_vital_sign') }}
                            </div>
                        @else
                            <form id="vitalSignScheduleForm" action="{{ route('vital-signs.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="morphable_type" value="{{ $morphableType }}">
                                <input type="hidden" name="morphable_id" value="{{ $morphableId }}">

                                <table class="table table-bordered align-middle mb-3">
                                    <thead class="table-light">
                                        <tr>
                                            <th colspan="3">{{ localize('global.date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3">
                                                <input type="text" autocomplete="off" id="schedule_date"
                                                    name="schedule_date"
                                                    class="form-control datepicker_dari pdp-el @error('schedule_date') is-invalid @enderror"
                                                    value="{{ old('schedule_date', $defaultScheduleDate) }}" required>
                                                @error('schedule_date')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr class="table-light">
                                            <th>{{ localize('global.vital_sign') }}</th>
                                            <th>{{ localize('global.morning_time') }}</th>
                                            <th>{{ localize('global.evening_time') }}</th>
                                        </tr>
                                    </tbody>
                                    <tbody id="schedule-rows-body">
                                        @foreach ($initialRows as $index => $row)
                                            @include('pages.vital-signs.partials.schedule-row', [
                                                'index' => $index,
                                                'row' => $row,
                                                'vitalSignTypes' => $vitalSignTypes,
                                            ])
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary" id="add-schedule-row">
                                        <i class="fas fa-plus"></i> {{ localize('global.add') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ localize('global.save') }}
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($hasMorphable)
        <template id="schedule-row-template">
            @include('pages.vital-signs.partials.schedule-row', [
                'index' => '__INDEX__',
                'row' => [
                    'vital_sign_type_id' => '',
                    'morning_time' => '',
                    'evening_time' => '',
                ],
                'vitalSignTypes' => $vitalSignTypes,
            ])
        </template>

        @push('scripts')
            <script>
                (function() {
                    'use strict';

                    const schedulesByDate = @json($schedulesByDate);
                    const rowsBody = document.getElementById('schedule-rows-body');
                    const rowTemplate = document.getElementById('schedule-row-template');
                    const dateInput = document.getElementById('schedule_date');
                    let rowIndex = rowsBody ? rowsBody.querySelectorAll('tr.schedule-row').length : 0;

                    function initDatepicker(scope) {
                        if (typeof $.fn.persianDatepicker !== 'function') {
                            return;
                        }
                        $(scope).find('input.datepicker_dari').each(function() {
                            const $el = $(this);
                            if ($el.data('datepicker') || $el.hasClass('pdp-el')) {
                                return;
                            }
                            const isScheduleDate = $el.attr('id') === 'schedule_date';
                            $el.persianDatepicker({
                                months: ['حمل', 'ثور', 'جوزا', 'سرطان', 'اسد', 'سنبله', 'میزان', 'عقرب', 'قوس',
                                    'جدی', 'دلو', 'حوت'
                                ],
                                dowTitle: ['شنبه', 'یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنج شنبه', 'جمعه'],
                                shortDowTitle: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
                                showGregorianDate: false,
                                persianNumbers: true,
                                formatDate: 'YYYY/MM/DD',
                                selectedBefore: false,
                                selectedDate: null,
                                startDate: null,
                                endDate: null,
                                prevArrow: '\u25c4',
                                nextArrow: '\u25ba',
                                theme: 'default',
                                alwaysShow: false,
                                onSelect: function() {
                                    if (isScheduleDate) {
                                        loadRowsForDate($el.val());
                                    }
                                }
                            });
                        });
                    }

                    function reindexRows() {
                        rowsBody.querySelectorAll('tr.schedule-row').forEach(function(row, index) {
                            row.querySelectorAll('[data-field]').forEach(function(input) {
                                const field = input.getAttribute('data-field');
                                input.name = 'schedule_rows[' + index + '][' + field + ']';
                            });
                        });
                        rowIndex = rowsBody.querySelectorAll('tr.schedule-row').length;
                    }

                    function buildRowHtml(data, index) {
                        const template = rowTemplate.innerHTML.replace(/__INDEX__/g, index);
                        const wrapper = document.createElement('tbody');
                        wrapper.innerHTML = template.trim();
                        const row = wrapper.firstElementChild;

                        const typeSelect = row.querySelector('[data-field="vital_sign_type_id"]');
                        const morningInput = row.querySelector('[data-field="morning_time"]');
                        const eveningInput = row.querySelector('[data-field="evening_time"]');
                        const scheduleIdInput = row.querySelector('[data-field="schedule_id"]');
                        const vitalSignIdInput = row.querySelector('[data-field="vital_sign_id"]');

                        if (typeSelect) {
                            typeSelect.value = data.vital_sign_type_id || '';
                        }
                        if (morningInput) {
                            morningInput.value = data.morning_time || '';
                        }
                        if (eveningInput) {
                            eveningInput.value = data.evening_time || '';
                        }
                        if (scheduleIdInput) {
                            scheduleIdInput.value = data.schedule_id || '';
                        }
                        if (vitalSignIdInput) {
                            vitalSignIdInput.value = data.vital_sign_id || '';
                        }

                        return row;
                    }

                    function loadRowsForDate(dateValue) {
                        const rows = schedulesByDate[dateValue] || [];
                        rowsBody.innerHTML = '';

                        if (rows.length === 0) {
                            rowsBody.appendChild(buildRowHtml({}, 0));
                        } else {
                            rows.forEach(function(rowData, index) {
                                rowsBody.appendChild(buildRowHtml(rowData, index));
                            });
                        }

                        reindexRows();
                    }

                    document.getElementById('add-schedule-row')?.addEventListener('click', function() {
                        rowsBody.appendChild(buildRowHtml({}, rowIndex));
                        reindexRows();
                    });

                    rowsBody?.addEventListener('click', function(e) {
                        const removeBtn = e.target.closest('.remove-schedule-row');
                        if (!removeBtn) {
                            return;
                        }
                        e.preventDefault();
                        const rows = rowsBody.querySelectorAll('tr.schedule-row');
                        if (rows.length <= 1) {
                            return;
                        }
                        removeBtn.closest('tr.schedule-row')?.remove();
                        reindexRows();
                    });

                    document.getElementById('vitalSignScheduleForm')?.addEventListener('submit', function() {
                        rowsBody.querySelectorAll('tr.schedule-row').forEach(function(row) {
                            const typeSelect = row.querySelector('[data-field="vital_sign_type_id"]');
                            if (typeSelect && !typeSelect.value) {
                                row.remove();
                            }
                        });
                        reindexRows();
                    });

                    initDatepicker(document);
                })();
            </script>
        @endpush
    @endif
@endsection
