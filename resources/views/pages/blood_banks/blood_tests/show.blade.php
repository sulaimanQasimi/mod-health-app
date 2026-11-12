@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            @php
                $backRoute = match ($bloodBank->status) {
                    'approved' => 'blood_banks.blood_tests.approved',
                    'rejected' => 'blood_banks.blood_tests.rejected',
                    'delivered' => 'blood_banks.blood_tests.delivered',
                    default => 'blood_banks.blood_tests.new',
                };
                $patientName = trim(($bloodBank->patient?->name ?? '') . ' ' . ($bloodBank->patient?->last_name ?? ''));
                $statusBadge = match ($bloodBank->status) {
                    'approved' => 'bg-label-success',
                    'delivered' => 'bg-label-primary',
                    'rejected' => 'bg-label-danger',
                    default => 'bg-label-warning',
                };
                $filledTests = $bloodBank->tests->filter(fn ($t) => $t->result !== null);
                $pendingTests = $bloodBank->tests->filter(fn ($t) => $t->result === null);
                $allTestsFilled = $bloodBank->tests->isNotEmpty() && $pendingTests->isEmpty();
            @endphp

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ localize('global.blood_test_details') }}</h4>
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
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.blood_test_request_summary') }}</h5>
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
                        <div class="col-6 col-md-4">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.status') }}</h6>
                            <div><span class="badge {{ $statusBadge }}">{{ localize('global.' . $bloodBank->status) }}</span></div>
                        </div>
                        <div class="col-6 col-md-4">
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

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-test-tube me-1"></i>{{ localize('global.blood_bank_requested_tests') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if ($bloodBank->tests->isEmpty())
                        <p class="text-muted mb-0">{{ localize('global.blood_bank_tests_empty') }}</p>
                    @else
                        @if ($filledTests->isNotEmpty())
                            <div class="table-responsive mb-3">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.test_name') }}</th>
                                            <th>{{ localize('global.result') }}</th>
                                            <th>{{ localize('global.notes') }}</th>
                                            <th>{{ localize('global.filled_test_by') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($filledTests as $test)
                                            <tr>
                                                <td>{{ $test->test_name }}</td>
                                                <td>
                                                    <span class="badge {{ $test->result === 'positive' ? 'bg-danger' : 'bg-success' }}">
                                                        {{ localize('global.'.$test->result) }}
                                                    </span>
                                                </td>
                                                <td>{{ $test->notes ?? '—' }}</td>
                                                <td>{{ $test->filledTestBy?->name ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if ($bloodBank->status === 'approved' && $pendingTests->isNotEmpty())
                            @canany(['receive-blood-units', 'manage-blood-inventory'])
                                @foreach ($pendingTests as $test)
                                    <form action="{{ route('blood_banks.blood_tests.fill', [$bloodBank, $test]) }}"
                                          method="POST" class="border rounded p-3 mb-2">
                                        @csrf
                                        <div class="fw-semibold mb-2">{{ $test->test_name }}</div>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label">{{ localize('global.result') }}</label>
                                                <select name="result" class="form-select" required>
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    <option value="positive">{{ localize('global.positive') }}</option>
                                                    <option value="negative">{{ localize('global.negative') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ localize('global.notes') }}</label>
                                                <input type="text" name="notes" class="form-control" maxlength="2000">
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    {{ localize('global.fill_test') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @endforeach
                            @else
                                <div class="alert alert-info mb-0">
                                    {{ localize('global.blood_test_waiting_lab_permission') }}
                                </div>
                            @endcanany
                        @elseif ($pendingTests->isNotEmpty())
                            <ul class="mb-0">
                                @foreach ($pendingTests as $test)
                                    <li>{{ $test->test_name }} <span class="text-muted">({{ localize('global.pending') }})</span></li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
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
                                            <form method="POST" action="{{ route('blood_banks.blood_tests.approve', $bloodBank) }}" class="mb-0">
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
                                            <form method="POST" action="{{ route('blood_banks.blood_tests.reject', $bloodBank) }}" class="mb-0">
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
                @elseif ($bloodBank->status === 'approved' && $allTestsFilled)
                    <div class="col-12">
                        @canany(['receive-blood-units', 'manage-blood-inventory'])
                            <div class="card">
                                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <h6 class="mb-1">{{ localize('global.blood_test_complete') }}</h6>
                                        <p class="text-muted small mb-0">{{ localize('global.blood_test_complete_hint') }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('blood_banks.blood_tests.complete', $bloodBank) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-check-double me-1"></i>{{ localize('global.blood_test_complete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endcanany
                    </div>
                @elseif ($bloodBank->status === 'delivered')
                    <div class="col-12">
                        <div class="alert alert-success mb-0">
                            {{ localize('global.blood_test_completed_status') }}
                        </div>
                    </div>
                @elseif ($bloodBank->status === 'rejected')
                    <div class="col-12">
                        <div class="alert alert-secondary mb-0">
                            {{ localize('global.blood_test_rejected_status') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
