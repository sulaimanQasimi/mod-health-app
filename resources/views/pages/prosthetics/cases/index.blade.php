@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.prosthetics_cases') }}</h4>
                <a href="{{ route('prosthetics.cases.create') }}" class="btn btn-primary btn-sm">{{ localize('global.prosthetics_new_case') }}</a>
            </div>

            <form method="get" class="row g-2 mb-3">
                <div class="col-auto">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ localize('global.search') ?? 'Search' }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ localize('global.search') }}</button>
                </div>
            </form>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>{{ localize('global.prosthetics_case_number') }}</th>
                                <th>{{ localize('global.patient_name') ?? 'Patient' }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cases as $c)
                                <tr>
                                    <td><code>{{ $c->case_number }}</code></td>
                                    <td>{{ $c->patient->name ?? '—' }}</td>
                                    <td><span class="badge bg-label-secondary">{{ $c->status }}</span></td>
                                    <td>
                                        <a href="{{ route('prosthetics.cases.show', $c) }}" class="btn btn-sm btn-outline-primary">{{ localize('global.show') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $cases->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
