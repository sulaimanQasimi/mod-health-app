@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            @php
                $backRoute = match ($bloodBank->status) {
                    'approved' => 'blood_banks.blood_typing.approved',
                    'rejected' => 'blood_banks.blood_typing.rejected',
                    'delivered' => 'blood_banks.blood_typing.delivered',
                    default => 'blood_banks.blood_typing.new',
                };
                $patientName = trim(($bloodBank->patient?->name ?? '') . ' ' . ($bloodBank->patient?->last_name ?? ''));
                $statusBadge = match ($bloodBank->status) {
                    'approved' => 'bg-label-success',
                    'delivered' => 'bg-label-primary',
                    'rejected' => 'bg-label-danger',
                    default => 'bg-label-warning',
                };
            @endphp

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ localize('global.blood_typing_details') }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <small class="text-muted">#{{ $bloodBank->id }}</small>
                        <span class="badge {{ $statusBadge }}">{{ localize('global.' . $bloodBank->status) }}</span>
                    </div>
                </div>
                <a href="{{ route($backRoute) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">{{ localize('global.blood_typing_request_summary') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.patient_name') }}</h6>
                            <div class="fw-semibold">{{ $patientName !== '' ? $patientName : '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.card_number') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->patient?->id_card ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.father_name') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->patient?->father_name ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.requested_department') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->department?->name ?? '—' }}</div>
                        </div>

                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.blood_group') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->group ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.blood_rh') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->rh ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.status') }}</h6>
                            <div>
                                <span class="badge {{ $statusBadge }}">{{ localize('global.' . $bloodBank->status) }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.created_at') }}</h6>
                            <div class="fw-semibold" dir="ltr">
                                {{ \Hekmatinasser\Verta\Verta::instance($bloodBank->created_at)->format('Y/n/j H:i') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.created_by') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->createdBy?->name ?? '—' }}</div>
                        </div>
                        @if ($bloodBank->factor)
                            <div class="col-12">
                                <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.notes') }}</h6>
                                <div class="text-start border rounded p-3">{{ $bloodBank->factor }}</div>
                            </div>
                        @endif
                        @if ($bloodBank->reject_reason)
                            <div class="col-12">
                                <h6 class="mb-2 bg-label-danger p-2 rounded">{{ localize('global.reject_reason') }}</h6>
                                <div class="text-start border rounded p-3 bg-label-danger">{{ $bloodBank->reject_reason }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row g-4">
                @if ($bloodBank->status === 'new')
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ localize('global.actions') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                            <h6 class="mb-3">{{ localize('global.approve') }}</h6>
                                            <form method="POST" action="{{ route('blood_banks.blood_typing.approve', $bloodBank) }}" class="mb-0">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="bx bx-check me-1"></i>{{ localize('global.approve') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="mb-3">{{ localize('global.reject') }}</h6>
                                            <form method="POST" action="{{ route('blood_banks.blood_typing.reject', $bloodBank) }}" class="mb-0">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-3">
                                                    <label class="form-label" for="reject_reason">{{ localize('global.reject_reason') }}</label>
                                                    <textarea name="reject_reason" id="reject_reason" class="form-control" rows="3" required>{{ old('reject_reason') }}</textarea>
                                                </div>
                                                <button type="submit" class="btn btn-danger w-100">
                                                    <i class="bx bx-x me-1"></i>{{ localize('global.reject') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($bloodBank->status === 'approved')
                    <div class="col-12">
                        @canany(['receive-blood-units', 'manage-blood-inventory'])
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ localize('global.blood_typing_record_result') }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">{{ localize('global.blood_typing_record_result_hint') }}</p>
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0 ps-3">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('blood_banks.blood_typing.complete', $bloodBank) }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">{{ localize('global.blood_group') }} <span class="text-danger">*</span></label>
                                            <div class="btn-group w-100" role="group">
                                                @foreach (['A', 'B', 'AB', 'O'] as $group)
                                                    <input type="radio" class="btn-check" name="group"
                                                           id="typingGroup_{{ $group }}" value="{{ $group }}"
                                                           autocomplete="off" @checked(old('group') === $group) required>
                                                    <label class="btn btn-outline-danger" for="typingGroup_{{ $group }}">{{ $group }}</label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ localize('global.blood_rh') }} <span class="text-danger">*</span></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="rh" id="typingRh_pos" value="+"
                                                       autocomplete="off" @checked(old('rh') === '+') required>
                                                <label class="btn btn-outline-danger" for="typingRh_pos">Rh+</label>
                                                <input type="radio" class="btn-check" name="rh" id="typingRh_neg" value="-"
                                                       autocomplete="off" @checked(old('rh') === '-') required>
                                                <label class="btn btn-outline-danger" for="typingRh_neg">Rh−</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="typing_notes">{{ localize('global.notes') }}</label>
                                            <textarea name="notes" id="typing_notes" class="form-control" rows="2">{{ old('notes', $bloodBank->factor) }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-check-double me-1"></i>{{ localize('global.blood_typing_complete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                {{ localize('global.blood_typing_waiting_lab_permission') }}
                            </div>
                        @endcanany
                    </div>
                @elseif ($bloodBank->status === 'delivered')
                    <div class="col-12">
                        <div class="alert alert-success mb-0">
                            {{ localize('global.blood_typing_completed_status') }}
                        </div>
                    </div>
                @elseif ($bloodBank->status === 'rejected')
                    <div class="col-12">
                        <div class="alert alert-secondary mb-0">
                            {{ localize('global.blood_typing_rejected_status') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
