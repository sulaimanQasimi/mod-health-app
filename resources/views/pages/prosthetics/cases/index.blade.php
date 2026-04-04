@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.prosthetics_cases') }}</h4>
                <a href="{{ route('prosthetics.cases.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i>{{ localize('global.prosthetics_new_case') }}
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-filter-alt me-1"></i>{{ localize('global.filters') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="get" action="{{ route('prosthetics.cases.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-0">{{ localize('global.search') }}</label>
                            <input type="text"
                                   name="q"
                                   value="{{ request('q') }}"
                                   class="form-control form-control-sm"
                                   placeholder="{{ localize('global.prosthetics_case_number') }} / {{ localize('global.patient_name') }} / {{ localize('global.phone') }} / {{ localize('global.nid') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-0">{{ localize('global.status') }}</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach (\App\Models\ProstheticCase::statusList() as $st)
                                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 d-flex flex-wrap gap-2 align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bx bx-search me-1"></i>{{ localize('global.filter') }}
                            </button>
                            <a href="{{ route('prosthetics.cases.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-reset me-1"></i>{{ localize('global.reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">{{ localize('global.prosthetics_cases') }}</h5>
                    <div class="text-muted small">
                        {{ $cases->firstItem() ?? 0 }} - {{ $cases->lastItem() ?? 0 }} / {{ $cases->total() }}
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.prosthetics_case_number') }}</th>
                                    <th>{{ localize('global.patient_name') ?? 'Patient' }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th class="text-end">{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cases as $c)
                                    <tr>
                                        <td><code>{{ $c->case_number }}</code></td>
                                        <td>{{ $c->patient->name ?? '—' }}</td>
                                        <td><span class="badge bg-label-secondary">{{ $c->status }}</span></td>
                                        <td class="text-end">
                                            <a href="{{ route('prosthetics.cases.show', $c) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-expand me-1"></i>{{ localize('global.show') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            {{ localize('global.no_item_is_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $cases->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
