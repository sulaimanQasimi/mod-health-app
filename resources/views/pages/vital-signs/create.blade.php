@extends('layouts.master')

@section('title', localize('global.create_vital_sign'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> {{ localize('global.create_vital_sign') }}
                    </h3>
                    <div class="card-tools">
                        @if($morphableType && $morphableId)
                            @if($morphableType == 'App\\Models\\Hospitalization')
                                <a href="{{ route('hospitalizations.show', $morphableId) }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> {{ localize('global.back') }}
                                </a>
                            @else
                                <a href="{{ route('under_reviews.show', $morphableId) }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> {{ localize('global.back') }}
                                </a>
                            @endif
                        @else
                            <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($morphableType && $morphableId)
                        {{-- Multiple vital signs with schedules (professional form) --}}
                        <form action="{{ route('vital-signs.store') }}" method="POST" id="multipleVitalSignsForm" data-user-is-nurse="{{ $currentUserNurse ? '1' : '0' }}">
                            @csrf
                            <input type="hidden" name="morphable_type" value="{{ $morphableType }}">
                            <input type="hidden" name="morphable_id" value="{{ $morphableId }}">

                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-light border d-flex align-items-center" role="alert">
                                        <i class="fas fa-link text-primary me-2"></i>
                                        <div>
                                            <strong>{{ localize('global.related_record') }}:</strong>
                                            {{ class_basename($morphableType) }} #{{ $morphableId }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(!$currentUserNurse)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info d-flex align-items-center" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <div>
                                            <strong>{{ localize('global.note') }}</strong>
                                            {{ localize('global.nurse_will_be_assigned_automatically_by_system') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-heartbeat me-1"></i> {{ localize('global.vital_signs') }} &amp; {{ localize('global.schedules') }}
                                </h5>
                                <p class="text-muted small mb-0">
                                    {{ localize('global.add_one_or_more_vital_signs_with_optional_schedules') }}
                                </p>
                            </div>

                            <div id="vital-signs-container">
                                {{-- First vital sign block --}}
                                <div class="vital-sign-block card border shadow-sm mb-4" data-index="0">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                        <span class="fw-semibold">
                                            <i class="fas fa-heartbeat text-primary me-1"></i>
                                            <span class="vital-sign-label">{{ localize('global.vital_sign') }} 1</span>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-vital-sign d-none" title="{{ localize('global.remove_vital_sign') }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ localize('global.vital_sign_type_id') }} <span class="text-danger">*</span></label>
                                                <select name="vital_signs[0][vital_sign_type_id]" class="form-select select2 vital-sign-type-select" data-placeholder="{{ localize('global.select_vital_sign_type') }}" required>
                                                    <option value="">{{ localize('global.select_vital_sign_type') }}</option>
                                                    @forelse($vitalSignTypes as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @empty
                                                        <option value="" disabled>{{ localize('global.no_vital_sign_types_available') }}</option>
                                                    @endforelse
                                                </select>
                                            </div>
                                        </div>
                                        <div class="schedules-section">
                                            <label class="form-label d-block">
                                                <i class="fas fa-calendar-alt me-1"></i> {{ localize('global.schedules') }}
                                            </label>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm schedules-table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 140px;">{{ localize('global.date') }}</th>
                                                            <th style="width: 140px;">{{ localize('global.morning_time') }}</th>
                                                            <th style="width: 140px;">{{ localize('global.evening_time') }}</th>
                                                            <th style="width: 50px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="schedule-rows">
                                                        <tr class="schedule-row">
                                                            <td>
                                                                <input type="text" name="vital_signs[0][schedules][0][date]" class="form-control form-control-sm datepicker_dari schedule-date" placeholder="1403/01/01" autocomplete="off">
                                                            </td>
                                                            <td>
                                                                <input type="text" name="vital_signs[0][schedules][0][morning_time]" class="form-control form-control-sm" placeholder="{{ localize('global.enter_morning_time') }}">
                                                            </td>
                                                            <td>
                                                                <input type="text" name="vital_signs[0][schedules][0][evening_time]" class="form-control form-control-sm" placeholder="{{ localize('global.enter_evening_time') }}">
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-danger remove-schedule-row" title="{{ localize('global.remove') }}">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary add-schedule-row mt-2">
                                                <i class="fas fa-plus me-1"></i> {{ localize('global.add_schedule_row') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <button type="button" id="add-vital-sign-btn" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> {{ localize('global.add_another_vital_sign') }}
                                </button>
                            </div>

                            @error('vital_signs')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> {{ localize('global.save_all_vital_signs') }}
                                </button>
                                @if($morphableType == 'App\\Models\\Hospitalization')
                                    <a href="{{ route('hospitalizations.show', $morphableId) }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                                @else
                                    <a href="{{ route('under_reviews.show', $morphableId) }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                                @endif
                            </div>
                        </form>
                    @else
                        {{-- Single vital sign (no context) --}}
                        <form action="{{ route('vital-signs.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="vital_sign_type_id">{{ localize('global.vital_sign_type_id') }} <span class="text-danger">*</span></label>
                                        <select class="form-control @error('vital_sign_type_id') is-invalid @enderror" id="vital_sign_type_id" name="vital_sign_type_id" required>
                                            <option value="">{{ localize('global.select_vital_sign_type') }}</option>
                                            @foreach($vitalSignTypes as $type)
                                                <option value="{{ $type->id }}" {{ old('vital_sign_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('vital_sign_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ localize('global.create_vital_sign') }}</button>
                                    <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> {{ localize('global.cancel') }}</a>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($morphableType && $morphableId)
@push('scripts')
<script>
(function() {
    const selectVitalSignPlaceholder = '{{ localize("global.select_vital_sign_type") }}';

    function stripSelect2FromElement(container) {
        if (!container) return;
        container.querySelectorAll('.select2-container').forEach(function(el) { el.remove(); });
        container.querySelectorAll('select.select2').forEach(function(sel) {
            sel.classList.remove('select2-hidden-accessible');
            sel.style.width = '';
        });
    }

    function initSelect2InContainer(container) {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            console.warn('jQuery or Select2 not available');
            return;
        }
        $(container).find('select.vital-sign-type-select').each(function() {
            var $select = $(this);
            var optionsCount = $select.find('option').length;
            console.log('Initializing Select2 for vital sign type select. Options count:', optionsCount);
            
            if (optionsCount <= 1) {
                console.warn('Select has no options or only placeholder. Select HTML:', $select[0].outerHTML);
            }
            
            // Destroy existing Select2 if already initialized
            if ($select.hasClass('select2-hidden-accessible')) {
                try {
                    $select.select2('destroy');
                } catch (e) {
                    console.warn('Error destroying Select2:', e);
                    // If destroy fails, manually remove Select2 classes and containers
                    $select.removeClass('select2-hidden-accessible');
                    $select.next('.select2-container').remove();
                }
            }
            
            // Ensure select is visible before initializing
            if ($select.is(':hidden')) {
                $select.show();
            }
            
            // Initialize Select2
            try {
                $select.select2({
                    placeholder: selectVitalSignPlaceholder,
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $(container).closest('.card-body, .card, form').first()
                });
                console.log('Select2 initialized successfully for:', $select.attr('name'));
            } catch (e) {
                console.error('Error initializing Select2:', e);
            }
        });
    }

    function getNextVitalSignIndex() {
        const blocks = document.querySelectorAll('.vital-sign-block');
        let max = -1;
        blocks.forEach(function(b) {
            const idx = parseInt(b.getAttribute('data-index'), 10);
            if (!isNaN(idx) && idx > max) max = idx;
        });
        return max + 1;
    }

    function getNextScheduleIndex(block) {
        const rows = block.querySelectorAll('.schedule-row');
        let max = -1;
        rows.forEach(function(r) {
            const inputEl = r.querySelector('input[name*="[date]"]') || r.querySelector('input[name*="[morning_time]"]');
            if (!inputEl || !inputEl.name) return;
            const m = inputEl.name.match(/schedules\]\[(\d+)\]/);
            if (m) { const n = parseInt(m[1], 10); if (n > max) max = n; }
        });
        return max + 1;
    }

    function renameBlockInputs(block, vsIndex) {
        block.setAttribute('data-index', vsIndex);
        block.querySelector('.vital-sign-label').textContent = '{{ localize("global.vital_sign") }} ' + (vsIndex + 1);
        block.querySelectorAll('.remove-vital-sign').forEach(function(btn) {
            if (vsIndex > 0) btn.classList.remove('d-none'); else btn.classList.add('d-none');
        });
        block.querySelectorAll('select.vital-sign-type-select').forEach(function(el) {
            el.name = 'vital_signs[' + vsIndex + '][vital_sign_type_id]';
        });
        block.querySelectorAll('.schedule-row').forEach(function(row, rowIdx) {
            const prefix = 'vital_signs[' + vsIndex + '][schedules][' + rowIdx + ']';
            row.querySelectorAll('input').forEach(function(input) {
                const match = input.name && input.name.match(/\[(date|morning_time|evening_time)\]$/);
                if (match) input.name = prefix + '[' + match[1] + ']';
            });
        });
    }

    function reindexAllBlocks() {
        document.querySelectorAll('.vital-sign-block').forEach(function(block, i) {
            renameBlockInputs(block, i);
        });
    }

    function addVitalSignBlock() {
        try {
            const idx = getNextVitalSignIndex();
            const container = document.getElementById('vital-signs-container');
            if (!container) {
                console.error('vital-signs-container not found');
                return;
            }
            const firstBlock = document.querySelector('.vital-sign-block');
            if (!firstBlock) {
                console.error('No vital sign block found to clone');
                return;
            }
            const clone = firstBlock.cloneNode(true);
            stripSelect2FromElement(clone);
            clone.setAttribute('data-index', idx);
            const labelEl = clone.querySelector('.vital-sign-label');
            if (labelEl) {
                labelEl.textContent = '{{ localize("global.vital_sign") }} ' + (idx + 1);
            }
            var vsSelect = clone.querySelector('.vital-sign-type-select');
            if (vsSelect) {
                vsSelect.value = '';
                vsSelect.name = 'vital_signs[' + idx + '][vital_sign_type_id]';
            }
            clone.querySelectorAll('.schedule-row').forEach(function(row) {
                row.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
            });
            clone.querySelectorAll('input, select').forEach(function(el) {
                if (el.name && el.name.indexOf('vital_signs[0]') !== -1) {
                    el.name = el.name.replace('vital_signs[0]', 'vital_signs[' + idx + ']');
                    const schedMatch = el.name.match(/schedules\]\[(\d+)\]/);
                    if (schedMatch) el.name = el.name.replace(/schedules\]\[\d+\]/, 'schedules][0]');
                }
            });
            const removeBtn = clone.querySelector('.remove-vital-sign');
            if (removeBtn) removeBtn.classList.remove('d-none');
            clone.querySelectorAll('.remove-schedule-row').forEach(function(btn, i) {
                if (i === 0) {
                    const tr = btn.closest('tr');
                    if (tr) {
                        tr.querySelectorAll('input, select').forEach(function(inp) {
                            inp.removeAttribute('required');
                        });
                    }
                }
            });
            container.appendChild(clone);
            reindexAllBlocks();
            initSelect2InContainer(clone);
            initPersianDatepickerInContainer(clone);
        } catch (err) {
            console.error('Error in addVitalSignBlock:', err);
            alert('Error adding vital sign block. Please check console for details.');
        }
    }

    function addScheduleRow(btn) {
        try {
            if (!btn) {
                console.error('Button element not provided');
                return;
            }
            const block = btn.closest('.vital-sign-block');
            if (!block) {
                console.error('Vital sign block not found');
                return;
            }
            const vsIndex = parseInt(block.getAttribute('data-index'), 10);
            if (isNaN(vsIndex)) {
                console.error('Invalid vital sign index');
                return;
            }
            const schedIndex = getNextScheduleIndex(block);
            const tbody = block.querySelector('tbody.schedule-rows');
            const firstRow = block.querySelector('tr.schedule-row');
            if (!tbody) {
                console.error('Schedule rows tbody not found');
                return;
            }
            if (!firstRow) {
                console.error('First schedule row not found');
                return;
            }
            const newRow = firstRow.cloneNode(true);
            stripSelect2FromElement(newRow);
            newRow.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
            newRow.querySelectorAll('input').forEach(function(el) {
                if (el.name) {
                    el.name = el.name.replace(/vital_signs\[\d+\]\[schedules\]\[\d+\]/, 'vital_signs[' + vsIndex + '][schedules][' + schedIndex + ']');
                }
            });
            tbody.appendChild(newRow);
            reindexAllBlocks(); // Reindex to ensure proper numbering
            initSelect2InContainer(newRow);
            initPersianDatepickerInContainer(newRow);
        } catch (err) {
            console.error('Error in addScheduleRow:', err);
            alert('Error adding schedule row. Please check console for details.');
        }
    }

    function initPersianDatepickerInContainer(container) {
        if (typeof $ === 'undefined' || typeof $.fn.persianDatepicker !== 'function') return;
        var $container = $(container);
        $container.find('input.schedule-date, input.datepicker_dari').each(function() {
            var $el = $(this);
            if ($el.data('datepicker') || $el.hasClass('pdp-el')) return;
            $el.persianDatepicker({
                months: ["حمل", "ثور", "جوزا", "سرطان", "اسد", "سنبله", "میزان", "عقرب", "قوس", "جدی", "دلو", "حوت"],
                dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
                shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
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
                alwaysShow: false
            });
        });
    }

    function initPageSelect2() {
        var container = document.getElementById('vital-signs-container');
        if (container) {
            // Check if vital sign types are loaded
            var firstSelect = container.querySelector('select.vital-sign-type-select');
            if (firstSelect && firstSelect.options.length <= 1) {
                console.warn('Vital sign types not loaded in select. Options count:', firstSelect.options.length);
            }
            initSelect2InContainer(container);
        }
    }

    function attachListeners() {
        // Direct listener for "Add Another Vital Sign" button
        var addVsBtn = document.getElementById('add-vital-sign-btn');
        if (addVsBtn) {
            addVsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                try {
                    addVitalSignBlock();
                } catch (err) {
                    console.error('Error adding vital sign block:', err);
                }
            });
        }

        // Event delegation for dynamically created buttons
        var container = document.getElementById('vital-signs-container');
        if (container) {
            container.addEventListener('click', function(e) {
                var target = e.target;
                
                // Handle "Add Schedule Row" button clicks
                var addScheduleBtn = target.closest('.add-schedule-row');
                if (addScheduleBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    try {
                        addScheduleRow(addScheduleBtn);
                    } catch (err) {
                        console.error('Error adding schedule row:', err);
                    }
                    return false;
                }
                
                // Handle "Remove Vital Sign" button clicks
                var removeVsBtn = target.closest('.remove-vital-sign');
                if (removeVsBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const block = removeVsBtn.closest('.vital-sign-block');
                    if (block && document.querySelectorAll('.vital-sign-block').length > 1) {
                        block.remove();
                        reindexAllBlocks();
                    }
                    return false;
                }
                
                // Handle "Remove Schedule Row" button clicks
                var removeScheduleBtn = target.closest('.remove-schedule-row');
                if (removeScheduleBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = removeScheduleBtn.closest('tr.schedule-row');
                    if (row) {
                        const tbody = row.closest('tbody.schedule-rows');
                        if (tbody && tbody.querySelectorAll('tr.schedule-row').length > 1) {
                            row.remove();
                            reindexAllBlocks();
                        }
                    }
                    return false;
                }
            });
        }

    }

    // Wait for DOM and jQuery to be ready
    function initAll() {
        console.log('Initializing vital signs form...');
        console.log('jQuery available:', typeof $ !== 'undefined');
        console.log('Select2 available:', typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined');
        
        // Attach listeners first (they don't need jQuery)
        attachListeners();
        console.log('Event listeners attached');
        
        // Initialize Persian datepicker on schedule date inputs (multi form)
        if (typeof $ !== 'undefined' && typeof $.fn.persianDatepicker === 'function') {
            var vsContainer = document.getElementById('vital-signs-container');
            if (vsContainer) initPersianDatepickerInContainer(vsContainer);
        }
        // Initialize Select2 if available
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            // Small delay to ensure DOM is fully rendered
            setTimeout(function() {
                initPageSelect2();
                console.log('Select2 initialized');
            }, 100);
        } else {
            // Retry Select2 initialization after a delay
            var retries = 0;
            var maxRetries = 30; // 3 seconds max
            var checkSelect2 = setInterval(function() {
                retries++;
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    clearInterval(checkSelect2);
                    setTimeout(function() {
                        initPageSelect2();
                        console.log('Select2 initialized after delay');
                    }, 100);
                } else if (retries >= maxRetries) {
                    clearInterval(checkSelect2);
                    console.warn('Select2 not available after retries');
                }
            }, 100);
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded fired');
            initAll();
        });
    } else {
        console.log('DOM already loaded');
        // Use setTimeout to ensure scripts are loaded
        setTimeout(initAll, 100);
    }
})();
</script>
@endpush
@endif
@endsection
