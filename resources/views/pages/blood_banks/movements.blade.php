@extends('layouts.master')

@section('content')
    @push('custom-css')
        <style>
            .blood-movements-filter-card .card-body {
                padding: 1.25rem;
            }

            .blood-movements-filter-card .form-label {
                font-size: 0.95rem;
                font-weight: 600;
            }

            .blood-movements-filter-card .form-select-sm,
            .blood-movements-filter-card .form-control-sm,
            .blood-movements-filter-card .btn-sm {
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
                <h4 class="mb-0">{{ localize('global.stock_movement_audit') }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('blood_banks.dashboard') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-grid-alt me-1"></i>{{ localize('global.blood_bank_dashboard') }}
                    </a>
                    <a href="{{ route('blood_banks.inventory') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-package me-1"></i>{{ localize('global.blood_inventory') }}
                    </a>
                </div>
            </div>

            <div class="card mb-3 blood-movements-filter-card">
                <div class="card-body">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.movement_type') }}</label>
                            <select name="movement_type" class="form-select form-select-sm">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach (['received', 'issued', 'adjusted', 'discarded', 'transferred'] as $mt)
                                    <option value="{{ $mt }}" {{ request('movement_type') === $mt ? 'selected' : '' }}>
                                        {{ $mt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.from') }}</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.to') }}</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">{{ localize('global.bag_number') }}</label>
                            <input type="text" name="bag_number" value="{{ request('bag_number') }}"
                                class="form-control form-control-sm" placeholder="{{ localize('global.search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.filter') }}</button>
                            <a href="{{ route('blood_banks.movements') }}" class="btn btn-sm btn-outline-secondary">{{ localize('global.reset') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.date') }}</th>
                                    <th>{{ localize('global.bag_number') }}</th>
                                    <th>{{ localize('global.movement_type') }}</th>
                                    <th>{{ localize('global.user') }}</th>
                                    <th>{{ localize('global.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movements as $m)
                                    <tr>
                                        <td dir="ltr">{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if ($m->bloodUnit)
                                                <a
                                                    href="{{ route('blood_banks.inventory.show', $m->bloodUnit) }}">{{ $m->bloodUnit->bag_number }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td><span class="badge bg-label-secondary">{{ $m->movement_type }}</span></td>
                                        <td>{{ $m->user?->name ?? '—' }}</td>
                                        <td class="small">{{ \Illuminate\Support\Str::limit($m->notes ?? '—', 80) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">{{ localize('global.no_item_is_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">{{ $movements->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
