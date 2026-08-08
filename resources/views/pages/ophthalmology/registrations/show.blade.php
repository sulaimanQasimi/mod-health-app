@extends('layouts.master')

@section('content')
@php
    $patient = $registration->appointment?->patient;
    $patientName = trim(($patient?->name ?? '') . ' ' . ($patient?->last_name ?? ''));
    $visual = old('visual_examination', $registration->visual_examination ?? []);
    $refraction = old('refraction', $registration->refraction ?? []);
    $history = old('medical_history', $registration->medical_history ?? []);
    $slit = old('slit_lamp_examination', $registration->slit_lamp_examination ?? []);
    $fundus = old('fundus_examination', $registration->fundus_examination ?? []);
    $val = function ($data, ...$keys) {
        $cursor = $data;
        foreach ($keys as $key) {
            $cursor = is_array($cursor) ? ($cursor[$key] ?? null) : null;
        }
        return $cursor ?? '';
    };
    $statusClass = match ($registration->status) {
        'pending' => 'warning',
        'in_progress' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary',
    };
    $visualFields = [
        ['visual_acuity', 'حدت بینایی'],
        ['pinhole_vision', 'دید با سوراخ سوزنی'],
        ['vision_with_glasses', 'دید با عینک'],
        ['near_vision', 'دید نزدیک'],
        ['intraocular_pressure', 'فشار داخل چشم'],
    ];
    $refractionFields = [
        ['sphere', 'اسفیر (SPH)'],
        ['cylinder', 'سیلندر (CYL)'],
        ['axis', 'محور (Axis)'],
        ['distance_vision', 'دید دور'],
        ['near_vision', 'دید نزدیک'],
        ['present_glasses', 'عینک فعلی'],
        ['recommended_prescription', 'نسخه پیشنهادی'],
    ];
    $historyFields = [
        ['diabetes', 'دیابت'],
        ['cardiac_disease', 'بیماری قلبی'],
        ['arthritis', 'التهاب مفاصل'],
        ['pregnancy', 'حاملگی'],
        ['asthma', 'آسم'],
        ['thyroid_disease', 'بیماری تیروئید'],
        ['hypertension', 'فشار خون بلند'],
        ['allergies', 'حساسیت‌ها'],
    ];
    $slitFields = [
        ['lids', 'پلک‌ها'],
        ['conjunctiva', 'ملتحمه'],
        ['cornea', 'قرنیه'],
        ['sclera', 'صلبیه'],
        ['anterior_chamber', 'اتاق قدامی'],
        ['iris', 'عنبیه'],
        ['pupil', 'مردمک'],
        ['lens', 'عدسی'],
        ['gonioscopy', 'گونیوسکوپی'],
        ['extraocular_movement', 'حرکت خارج چشمی'],
    ];
    $canSave = $canEdit || $canChangeStatus || $canUpload;
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif

        <div class="card mb-4 border-primary border-opacity-25">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-show fs-3"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ localize('global.ophthalmology_examination') }}</h4>
                            <div class="text-muted small">
                                {{ $registration->ref_no }} · {{ $patientName }}
                                <span class="badge bg-{{ $statusClass }} ms-2">{{ localize('global.status_' . $registration->status) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('ophthalmology-registrations.print', $registration) }}" target="_blank" class="btn btn-outline-secondary">
                            <i class="bx bx-printer me-1"></i>{{ localize('global.print_eye_examination') }}
                        </a>
                        <a href="{{ route('ophthalmology-registrations.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                        </a>
                        @if ($registration->appointment_id)
                            <a href="{{ route('appointments.show', $registration->appointment_id) }}" class="btn btn-outline-primary">
                                <i class="bx bx-calendar me-1"></i>{{ localize('global.appointment') }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="row g-2">
                    @foreach ([
                        [localize('global.patient_name'), $patientName],
                        [localize('global.father_name'), $patient?->father_name],
                        [localize('global.id_card'), $patient?->id_card],
                        [localize('global.age'), $patient?->age],
                        [localize('global.phone'), $patient?->phone],
                        [localize('global.examiner'), $registration->examiner?->name],
                    ] as [$label, $value])
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="border rounded-3 px-3 py-2 h-100 bg-light">
                                <div class="small text-muted">{{ $label }}</div>
                                <div class="fw-semibold text-truncate">{{ $value ?: '—' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('ophthalmology-registrations.update', $registration) }}" enctype="multipart/form-data" id="ophthalmology-exam-form">
            @csrf
            @method('PUT')

            {{-- Registration & history --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-clipboard me-2 text-primary"></i>{{ localize('global.registration_and_history') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.examiner') }}</label>
                            <select name="examiner_id" class="form-select" @disabled(! $canEdit)>
                                <option value="">—</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected(old('examiner_id', $registration->examiner_id) == $doctor->id)>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.registration_date') }}</label>
                            <input type="text" name="registration_date" class="form-control datepicker_dari"
                                   value="{{ old('registration_date', $registration->registration_date ? verta($registration->registration_date)->format('Y/m/d') : '') }}"
                                   @disabled(! $canEdit) autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.status') }}</label>
                            <select name="status" class="form-select" @disabled(! $canChangeStatus)>
                                @foreach (['pending', 'in_progress', 'completed', 'cancelled'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $registration->status) === $status)>
                                        {{ localize('global.status_' . $status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ localize('global.chief_complaint') }}</label>
                            <textarea name="chief_complaint" rows="3" class="form-control" @disabled(! $canEdit)>{{ old('chief_complaint', $registration->chief_complaint) }}</textarea>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">تاریخچه طبی</h6>
                    <div class="row g-3">
                        @foreach ($historyFields as [$key, $label])
                            @php $historyValue = $val($history, $key, 'value'); @endphp
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                                        <span class="fw-semibold">{{ $label }}</span>
                                        <input type="hidden" name="medical_history[{{ $key }}][value]" value="{{ $historyValue }}" class="mh-value-input" data-mh-key="{{ $key }}">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="{{ $label }}">
                                            <button type="button"
                                                    class="btn btn-outline-success mh-toggle {{ $historyValue === 'yes' ? 'active' : '' }}"
                                                    data-mh-key="{{ $key }}"
                                                    data-value="yes"
                                                    title="بلی"
                                                    @disabled(! $canEdit)>
                                                <i class="bx bx-check"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-outline-danger mh-toggle {{ $historyValue === 'no' ? 'active' : '' }}"
                                                    data-mh-key="{{ $key }}"
                                                    data-value="no"
                                                    title="نخیر"
                                                    @disabled(! $canEdit)>
                                                <i class="bx bx-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="text" name="medical_history[{{ $key }}][notes]" class="form-control form-control-sm"
                                           placeholder="{{ localize('global.notes') }}"
                                           value="{{ $val($history, $key, 'notes') }}" @disabled(! $canEdit)>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Visual examination --}}
            <div class="card mb-4" id="visual-exam">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-show me-2 text-primary"></i>{{ localize('global.visual_examination') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>اندازه‌گیری</th>
                                    <th class="table-info">OD (چشم راست)</th>
                                    <th class="table-primary">OS (چشم چپ)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($visualFields as [$key, $label])
                                    <tr>
                                        <td class="fw-semibold">{{ $label }}</td>
                                        @foreach (['od', 'os'] as $eye)
                                            <td>
                                                <input type="text"
                                                       class="form-control eye-live-input"
                                                       name="visual_examination[{{ $eye }}][{{ $key }}]"
                                                       data-eye-target="{{ $eye }}.{{ $key }}"
                                                       value="{{ $val($visual, $eye, $key) }}"
                                                       @disabled(! $canEdit)>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-3 mb-4">
                        @foreach ([
                            ['squint_assessment', 'ارزیابی انحراف چشم'],
                            ['blood_pressure', 'فشار خون'],
                            ['color_vision', 'دید رنگی'],
                        ] as [$key, $label])
                            <div class="col-md-4">
                                <label class="form-label">{{ $label }}</label>
                                <input type="text" class="form-control eye-live-input"
                                       name="visual_examination[{{ $key }}]"
                                       data-eye-target="{{ $key }}"
                                       value="{{ $val($visual, $key) }}"
                                       @disabled(! $canEdit)>
                            </div>
                        @endforeach
                    </div>

                    <div class="border border-dashed rounded-3 p-3 bg-label-primary bg-opacity-10">
                        <h6 class="mb-3">
                            <i class="bx bx-show me-1"></i>{{ localize('global.eye_diagram') }}
                        </h6>
                        @include('pages.ophthalmology.registrations.partials.eye_diagram', [
                            'visualExamination' => $visual,
                            'refraction' => $refraction,
                        ])
                    </div>
                </div>
            </div>

            {{-- Refraction --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-glasses me-2 text-primary"></i>{{ localize('global.refraction') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>اندازه‌گیری</th>
                                    <th class="table-info">OD</th>
                                    <th class="table-primary">OS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($refractionFields as [$key, $label])
                                    <tr>
                                        <td class="fw-semibold">{{ $label }}</td>
                                        @foreach (['od', 'os'] as $eye)
                                            <td>
                                                <input type="text"
                                                       class="form-control eye-live-input"
                                                       name="refraction[{{ $eye }}][{{ $key }}]"
                                                       data-eye-target="refraction.{{ $eye }}.{{ $key }}"
                                                       value="{{ $val($refraction, $eye, $key) }}"
                                                       @disabled(! $canEdit)>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">IPD</label>
                            <input type="text" class="form-control" name="refraction[ipd]" value="{{ $val($refraction, 'ipd') }}" @disabled(! $canEdit)>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">{{ localize('global.notes') }}</label>
                            <input type="text" class="form-control" name="refraction[notes]" value="{{ $val($refraction, 'notes') }}" @disabled(! $canEdit)>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slit lamp --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-bulb me-2 text-primary"></i>{{ localize('global.slit_lamp_examination') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>یافته</th>
                                    <th>وضعیت OD</th>
                                    <th>یادداشت OD</th>
                                    <th>وضعیت OS</th>
                                    <th>یادداشت OS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($slitFields as [$key, $label])
                                    <tr>
                                        <td class="fw-semibold">{{ $label }}</td>
                                        @foreach (['od', 'os'] as $eye)
                                            <td>
                                                <select class="form-select" name="slit_lamp_examination[{{ $eye }}][{{ $key }}][status]" @disabled(! $canEdit)>
                                                    <option value="">—</option>
                                                    <option value="normal" @selected($val($slit, $eye, $key, 'status') === 'normal')>طبیعی</option>
                                                    <option value="abnormal" @selected($val($slit, $eye, $key, 'status') === 'abnormal')>غیرطبیعی</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control"
                                                       name="slit_lamp_examination[{{ $eye }}][{{ $key }}][notes]"
                                                       value="{{ $val($slit, $eye, $key, 'notes') }}" @disabled(! $canEdit)>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Fundus --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-camera me-2 text-primary"></i>{{ localize('global.fundus_examination') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">یافته‌های OD</label>
                            <textarea name="fundus_examination[od_findings]" rows="4" class="form-control" @disabled(! $canEdit)>{{ $val($fundus, 'od_findings') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">یافته‌های OS</label>
                            <textarea name="fundus_examination[os_findings]" rows="4" class="form-control" @disabled(! $canEdit)>{{ $val($fundus, 'os_findings') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">وضعیت گشادسازی مردمک</label>
                            <select name="fundus_examination[dilation_status]" class="form-select" @disabled(! $canEdit)>
                                <option value="">—</option>
                                <option value="not_dilated" @selected($val($fundus, 'dilation_status') === 'not_dilated')>گشاد نشده</option>
                                <option value="dilated" @selected($val($fundus, 'dilation_status') === 'dilated')>گشاد شده</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">زمان گشادسازی</label>
                            <input type="time" name="fundus_examination[dilation_time]" class="form-control"
                                   value="{{ $val($fundus, 'dilation_time') }}" @disabled(! $canEdit)>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">تصویر یا گزارش فوندوس</label>
                            <input type="file" name="fundus_image" class="form-control" accept="image/*,.pdf" @disabled(! $canUpload)>
                            @if ($registration->fundus_image_path)
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($registration->fundus_image_path) }}" target="_blank" class="small d-inline-block mt-1">
                                    مشاهده ضمیمه فعلی
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assessment --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-notepad me-2 text-primary"></i>{{ localize('global.assessment_and_plan') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ localize('global.diagnosis') }}</label>
                            <textarea name="diagnosis" rows="4" class="form-control" @disabled(! $canEdit)>{{ old('diagnosis', $registration->diagnosis) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ localize('global.treatment_plan') }}</label>
                            <textarea name="treatment_plan" rows="4" class="form-control" @disabled(! $canEdit)>{{ old('treatment_plan', $registration->treatment_plan) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.follow_up_date') }}</label>
                            <input type="text" name="follow_up_date" class="form-control datepicker_dari"
                                   value="{{ old('follow_up_date', $registration->follow_up_date ? verta($registration->follow_up_date)->format('Y/m/d') : '') }}"
                                   @disabled(! $canEdit) autocomplete="off">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ localize('global.notes') }}</label>
                            <textarea name="notes" rows="2" class="form-control" @disabled(! $canEdit)>{{ old('notes', $registration->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-2 mb-4">
                <a href="{{ route('ophthalmology-registrations.print', $registration) }}" target="_blank" class="btn btn-outline-secondary btn-lg">
                    <i class="bx bx-printer me-1"></i>{{ localize('global.print_eye_examination') }}
                </a>
                @if ($canSave)
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bx bx-save me-1"></i>{{ localize('global.save_ophthalmology_examination') }}
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function displayValue(value) {
        return value && String(value).trim() !== '' ? String(value) : '—';
    }

    function syncEyeDiagram() {
        document.querySelectorAll('.eye-live-input').forEach(function (input) {
            var target = input.getAttribute('data-eye-target');
            if (!target) return;
            var nodes = document.querySelectorAll('[data-eye-metric="' + target + '"]');
            nodes.forEach(function (node) {
                node.textContent = displayValue(input.value);
            });
        });

        ['od', 'os'].forEach(function (side) {
            var row = document.querySelector('[data-refraction-row="' + side + '"]');
            if (!row) return;
            var keys = ['sphere', 'cylinder', 'axis'];
            var hasValue = keys.some(function (key) {
                var input = document.querySelector('[data-eye-target="refraction.' + side + '.' + key + '"]');
                return input && String(input.value || '').trim() !== '';
            });
            row.classList.toggle('d-none', !hasValue);
        });
    }

    document.querySelectorAll('.eye-live-input').forEach(function (input) {
        input.addEventListener('input', syncEyeDiagram);
        input.addEventListener('change', syncEyeDiagram);
    });

    document.querySelectorAll('.mh-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.disabled) return;

            var key = button.getAttribute('data-mh-key');
            var value = button.getAttribute('data-value');
            var input = document.querySelector('.mh-value-input[data-mh-key="' + key + '"]');
            var group = button.closest('.btn-group');
            if (!input || !group) return;

            var nextValue = input.value === value ? '' : value;
            input.value = nextValue;

            group.querySelectorAll('.mh-toggle').forEach(function (btn) {
                btn.classList.toggle('active', btn.getAttribute('data-value') === nextValue);
            });
        });
    });

    syncEyeDiagram();
});
</script>
@endsection
