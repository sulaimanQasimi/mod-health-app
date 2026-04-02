@extends('layouts.master')

@section('content')
    @push('custom-css')
        <style>
            /* Ensure Add Blood modal always scrolls vertically on small/short screens */
            #bloodInventoryAddModal .modal-dialog {
                max-height: calc(100vh - 2rem);
            }

            #bloodInventoryAddModal .modal-content {
                max-height: calc(100vh - 2rem);
            }

            #bloodInventoryAddModal form {
                min-height: 0;
            }

            #bloodInventoryAddModal .modal-body {
                min-height: 0;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            /* Larger, easier-to-read filter panel */
            .blood-inventory-filter-card .card-body {
                padding: 1.25rem;
            }

            .blood-inventory-filter-card .form-label {
                font-size: 0.95rem;
                font-weight: 600;
            }

            .blood-inventory-filter-card .form-select-sm,
            .blood-inventory-filter-card .form-control-sm,
            .blood-inventory-filter-card .btn-sm {
                font-size: 0.95rem;
                min-height: 2.5rem;
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }
        </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.blood_inventory') }}</h4>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    @canany(['receive-blood-units', 'manage-blood-inventory'])
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#bloodInventoryAddModal">
                            <i class="bx bx-plus me-1"></i>{{ localize('global.add_blood_manually') }}
                        </button>
                    @endcanany
                    <a href="{{ route('blood_banks.dashboard') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-grid-alt me-1"></i>{{ localize('global.blood_bank_dashboard') }}
                    </a>
                    <a href="{{ route('blood_banks.movements') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-list-ul me-1"></i>{{ localize('global.stock_movement_audit') }}
                    </a>
                </div>
            </div>

            @canany(['receive-blood-units', 'manage-blood-inventory'])
                <div class="modal fade" id="bloodInventoryAddModal" tabindex="-1"
                    aria-labelledby="bloodInventoryAddModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form action="{{ route('blood_banks.inventory.store') }}" method="POST" class="h-100 d-flex flex-column">
                                @csrf
                                <div class="modal-header">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="{{ localize('global.close') }}"></button>
                                </div>
                                <div class="modal-body overflow-auto">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="row g-3">
                            {{-- Temporarily hidden: link blood donor to patient --}}
                            {{--
                            <div class="col-md-6">
                                <label class="form-label">{{ localize('global.blood_donor_link_patient') }}</label>
                                <select name="patient_id" id="bloodDonorPatientId"
                                    class="form-select @error('patient_id') is-invalid @enderror">
                                    <option value="">{{ localize('global.none') }}</option>
                                    @foreach ($patientsForDonor ?? [] as $p)
                                        <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>
                                            {{ trim($p->name.' '.($p->last_name ?? '')) }}
                                            @if ($p->phone)
                                                — {{ $p->phone }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            --}}
                            <div class="col-12" id="bloodDonorDeptToggleRow">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="donor_record_department"
                                        id="donorRecordDepartment" value="1"
                                        @checked(old('donor_record_department'))>
                                    <label class="form-check-label" for="donorRecordDepartment">
                                        {{ localize('global.blood_donor_record_department') }}
                                    </label>
                                </div>
                                <p class="small text-muted mb-0 mt-1">{{ localize('global.blood_donor_no_patient_department_hint') }}</p>
                            </div>
                            <div class="col-md-6" id="bloodDonorDeptSelectWrap" style="display: none;">
                                <label class="form-label">{{ localize('global.department') }}</label>
                                <select name="department_id" id="bloodDonorDepartmentId"
                                    class="form-select @error('department_id') is-invalid @enderror">
                                    <option value="">{{ localize('global.select') }}</option>
                                    @foreach ($departments ?? [] as $d)
                                        <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 @if (! old('department_id')) d-none @endif" id="bloodDonorDetailsWrap">
                                <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.donor_name') }}</label>
                                <input type="text" name="donor_name" value="{{ old('donor_name') }}"
                                    class="form-control @error('donor_name') is-invalid @enderror" maxlength="255"
                                    autocomplete="name" placeholder="{{ localize('global.optional') }}">
                                @error('donor_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.father_name') }}</label>
                                <input type="text" name="donor_father_name" value="{{ old('donor_father_name') }}"
                                    class="form-control @error('donor_father_name') is-invalid @enderror" maxlength="255"
                                    placeholder="{{ localize('global.optional') }}">
                                @error('donor_father_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.age') }}</label>
                                <input type="number" name="donor_age" value="{{ old('donor_age') }}"
                                    class="form-control @error('donor_age') is-invalid @enderror" min="0" max="130">
                                @error('donor_age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.gender') }}</label>
                                <select name="donor_gender" class="form-select @error('donor_gender') is-invalid @enderror">
                                    <option value="">{{ localize('global.select') }}</option>
                                    <option value="male" @selected(old('donor_gender') === 'male')>{{ localize('global.male') }}</option>
                                    <option value="female" @selected(old('donor_gender') === 'female')>{{ localize('global.female') }}</option>
                                </select>
                                @error('donor_gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.phone') }}</label>
                                <input type="text" name="donor_phone" value="{{ old('donor_phone') }}"
                                    class="form-control @error('donor_phone') is-invalid @enderror" maxlength="50"
                                    autocomplete="tel">
                                @error('donor_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.national_id') }}</label>
                                <input type="text" name="donor_national_id" value="{{ old('donor_national_id') }}"
                                    class="form-control @error('donor_national_id') is-invalid @enderror" maxlength="50">
                                @error('donor_national_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.blood_pressure') }}</label>
                                <input type="text" name="donor_blood_pressure" value="{{ old('donor_blood_pressure') }}"
                                    class="form-control @error('donor_blood_pressure') is-invalid @enderror" maxlength="50"
                                    placeholder="120/80">
                                @error('donor_blood_pressure')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.donor_type') }}</label>
                                <select name="donor_type" id="bloodDonorType"
                                    class="form-select @error('donor_type') is-invalid @enderror">
                                    <option value="civilian" @selected(old('donor_type', 'civilian') === 'civilian')>{{ localize('global.civilian') }}</option>
                                    <option value="military" @selected(old('donor_type') === 'military')>{{ localize('global.military') }}</option>
                                </select>
                                @error('donor_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ localize('global.comorbidities') }}</label>
                                <textarea name="donor_comorbidities" class="form-control @error('donor_comorbidities') is-invalid @enderror" rows="2">{{ old('donor_comorbidities') }}</textarea>
                                @error('donor_comorbidities')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ localize('global.phlebotomy_at') }}</label>
                                <input type="datetime-local" name="phlebotomy_at" value="{{ old('phlebotomy_at') }}"
                                    class="form-control @error('phlebotomy_at') is-invalid @enderror">
                                <small class="text-muted">{{ localize('global.phlebotomy_at_hint') }}</small>
                                @error('phlebotomy_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="mb-2 text-body-secondary">{{ localize('global.unit_details') }}</h6>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ localize('global.blood_group') }}</label>
                                <select name="blood_group" class="form-select @error('blood_group') is-invalid @enderror" required>
                                    @foreach (['A', 'B', 'AB', 'O'] as $g)
                                        <option value="{{ $g }}" @selected(old('blood_group', 'A') === $g)>{{ $g }}</option>
                                    @endforeach
                                </select>
                                @error('blood_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ localize('global.blood_rh') }}</label>
                                <select name="rh" class="form-select @error('rh') is-invalid @enderror" required>
                                    <option value="+" @selected(old('rh', '+') === '+')>+</option>
                                    <option value="-" @selected(old('rh') === '-')>-</option>
                                </select>
                                @error('rh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.component_type') }}</label>
                                <select name="component_type" class="form-select @error('component_type') is-invalid @enderror" required>
                                    @foreach (\App\Models\BloodUnit::COMPONENT_TYPES as $t)
                                        <option value="{{ $t }}" @selected(old('component_type', 'Fresh') === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                                @error('component_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.bag_number') }}</label>
                                <input type="text" name="bag_number" value="{{ old('bag_number') }}"
                                    class="form-control @error('bag_number') is-invalid @enderror" required maxlength="255"
                                    autocomplete="off">
                                @error('bag_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ localize('global.volume_ml') }}</label>
                                <input type="number" name="volume_ml" value="{{ old('volume_ml') }}"
                                    class="form-control @error('volume_ml') is-invalid @enderror" min="1"
                                    placeholder="ml">
                                @error('volume_ml')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.collected_at') }}</label>
                                <input type="date" name="collected_at" value="{{ old('collected_at') }}"
                                    class="form-control @error('collected_at') is-invalid @enderror">
                                @error('collected_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.expires_date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="expires_date" value="{{ old('expires_date') }}"
                                    class="form-control @error('expires_date') is-invalid @enderror" required>
                                @error('expires_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.expires_time') }}</label>
                                <input type="time" name="expires_time" value="{{ old('expires_time', '23:59') }}"
                                    class="form-control @error('expires_time') is-invalid @enderror" step="60">
                                <small class="text-muted">{{ localize('global.expires_time_default_hint') }}</small>
                                @error('expires_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ localize('global.notes') }}</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                        {{ localize('global.cancel') }}</button>
                                    <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcanany

            <div class="card mb-3 blood-inventory-filter-card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-filter-alt me-1"></i>{{ localize('global.filters') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small mb-0">{{ localize('global.status') }}</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach (\App\Models\BloodUnit::STATUSES as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                        {{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-0">{{ localize('global.expires_within_days') }}</label>
                            <select name="expires_within" class="form-select form-select-sm">
                                <option value="">{{ localize('global.all') }}</option>
                                <option value="3" {{ request('expires_within') == '3' ? 'selected' : '' }}>3</option>
                                <option value="7" {{ request('expires_within') == '7' ? 'selected' : '' }}>7</option>
                                <option value="14" {{ request('expires_within') == '14' ? 'selected' : '' }}>14</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-0">{{ localize('global.sort') }}</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="created_at" {{ request('sort', 'created_at') === 'created_at' ? 'selected' : '' }}>
                                    {{ localize('global.recent_first') }}</option>
                                <option value="expires_at" {{ request('sort') === 'expires_at' ? 'selected' : '' }}>
                                    {{ localize('global.expiry_earliest_first') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-0">{{ localize('global.bag_number') }}</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                                placeholder="{{ localize('global.search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.filter') }}</button>
                            <a href="{{ route('blood_banks.inventory') }}" class="btn btn-sm btn-outline-secondary">{{ localize('global.reset') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.blood_inventory') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.bag_number') }}</th>
                                    <th>{{ localize('global.donor') }}</th>
                                    <th>{{ localize('global.department') }}</th>
                                    <th>{{ localize('global.blood_group') }}</th>
                                    <th>{{ localize('global.rh') }}</th>
                                    <th>{{ localize('global.component_type') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.collected_at') }}</th>
                                    <th>{{ localize('global.expires_at') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($units as $unit)
                                    <tr>
                                        <td>
                                            <a href="{{ route('blood_banks.inventory.show', $unit) }}">{{ $unit->bag_number }}</a>
                                        </td>
                                        <td>{{ $unit->donation?->donor?->name ?? '—' }}</td>
                                        <td>{{ $unit->donation?->donor?->department?->name ?? '—' }}</td>
                                        <td>{{ $unit->blood_group }}</td>
                                        <td>{{ $unit->rh }}</td>
                                        <td>{{ $unit->component_type }}</td>
                                        <td>{{ $unit->status }}</td>
                                        <td dir="ltr">{{ $unit->collected_at?->format('Y-m-d') ?? '—' }}</td>
                                        <td dir="ltr">{{ $unit->expires_at?->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('blood_banks.inventory.show', $unit) }}"
                                                class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            {{ localize('global.no_item_is_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $units->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    @canany(['receive-blood-units', 'manage-blood-inventory'])
        @push('scripts')
            <script>
                function syncBloodDonorDeptFields() {
                    var patientSel = document.getElementById('bloodDonorPatientId');
                    var toggleRow = document.getElementById('bloodDonorDeptToggleRow');
                    var deptWrap = document.getElementById('bloodDonorDeptSelectWrap');
                    var chk = document.getElementById('donorRecordDepartment');
                    var deptSel = document.getElementById('bloodDonorDepartmentId');
                    var donorTypeSel = document.getElementById('bloodDonorType');
                    if (!toggleRow || !deptWrap) {
                        syncBloodDonorDetailsVisibility();
                        return;
                    }
                    var hasPatient = patientSel && patientSel.value && patientSel.value !== '';
                    var isMilitary = donorTypeSel && donorTypeSel.value === 'military';
                    if (hasPatient) {
                        if (isMilitary) {
                            toggleRow.style.display = 'none';
                            deptWrap.style.display = '';
                        } else {
                            toggleRow.style.display = 'none';
                            deptWrap.style.display = 'none';
                            if (chk) {
                                chk.checked = false;
                            }
                            if (deptSel) {
                                deptSel.value = '';
                            }
                        }
                    } else {
                        toggleRow.style.display = '';
                        if (isMilitary) {
                            if (chk) {
                                chk.checked = true;
                            }
                            deptWrap.style.display = '';
                        } else {
                            deptWrap.style.display = (chk && chk.checked) ? '' : 'none';
                        }
                    }
                    syncBloodDonorDetailsVisibility();
                }

                function syncBloodDonorDetailsVisibility() {
                    var detailsWrap = document.getElementById('bloodDonorDetailsWrap');
                    var deptSelectWrap = document.getElementById('bloodDonorDeptSelectWrap');
                    var deptSel = document.getElementById('bloodDonorDepartmentId');
                    if (!detailsWrap) {
                        return;
                    }
                    var deptWrapVisible = deptSelectWrap &&
                        window.getComputedStyle(deptSelectWrap).display !== 'none';
                    var hasDepartment = deptSel && deptSel.value && deptSel.value !== '';
                    // Show donor fields whenever department dropdown is hidden (toggle off / not in record-dept flow).
                    // When department dropdown is visible (toggle on or military), hide donor fields until a department is chosen.
                    var show = !deptWrapVisible || hasDepartment;
                    detailsWrap.classList.toggle('d-none', !show);
                    detailsWrap.querySelectorAll('input, select, textarea').forEach(function(el) {
                        if (show) {
                            el.removeAttribute('disabled');
                        } else {
                            el.setAttribute('disabled', 'disabled');
                        }
                    });
                }

                document.addEventListener('DOMContentLoaded', function() {
                    var patientSel = document.getElementById('bloodDonorPatientId');
                    var chk = document.getElementById('donorRecordDepartment');
                    var donorTypeSel = document.getElementById('bloodDonorType');
                    if (patientSel) {
                        patientSel.addEventListener('change', syncBloodDonorDeptFields);
                    }
                    if (chk) {
                        chk.addEventListener('change', syncBloodDonorDeptFields);
                    }
                    if (donorTypeSel) {
                        donorTypeSel.addEventListener('change', syncBloodDonorDeptFields);
                    }
                    var deptSel = document.getElementById('bloodDonorDepartmentId');
                    if (deptSel) {
                        deptSel.addEventListener('change', syncBloodDonorDetailsVisibility);
                    }
                    syncBloodDonorDeptFields();
                    syncBloodDonorDetailsVisibility();
                    @if ($errors->any())
                        var _bloodAddModalEl = document.getElementById('bloodInventoryAddModal');
                        if (_bloodAddModalEl && typeof bootstrap !== 'undefined') {
                            new bootstrap.Modal(_bloodAddModalEl).show();
                        }
                    @endif
                });
            </script>
        @endpush
    @endcanany
@endsection
