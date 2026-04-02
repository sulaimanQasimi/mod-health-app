@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">Prosthetics Reports</h4>
                <a href="{{ route('prosthetics.dashboard') }}" class="btn btn-sm btn-outline-secondary">Back to dashboard</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4 col-6">
                    <div class="card h-100 bg-label-info">
                        <div class="card-body">
                            <div class="text-muted small">Average turnaround (days)</div>
                            <h3 class="mb-0 mt-1">{{ $avgDays ?? '-' }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card h-100 bg-label-primary">
                        <div class="card-body">
                            <div class="text-muted small">Delivered cases</div>
                            <h3 class="mb-0 mt-1">{{ $deliveredCount }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card h-100 bg-label-secondary">
                        <div class="card-body">
                            <div class="text-muted small">Total cases in table</div>
                            <h3 class="mb-0 mt-1">{{ $cases->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <form method="get" class="row g-2 mb-4">
                <div class="col-md-4">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Models\ProstheticCase::statusList() as $st)
                            <option value="{{ $st }}" @selected($status === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-sm btn-primary w-100">Filter</button>
                </div>
            </form>

            <div class="card mb-4">
                <div class="card-header">Cases by Status</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-end">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statusCounts as $st => $count)
                                    <tr>
                                        <td>{{ $st }}</td>
                                        <td class="text-end"><span class="badge bg-label-secondary">{{ $count }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Cases List</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Case No</th>
                                    <th>Patient</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Delivered</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cases as $c)
                                    @php $latestDelivery = $c->deliveries->first(); @endphp
                                    <tr>
                                        <td><code>{{ $c->case_number }}</code></td>
                                        <td>{{ $c->patient->name ?? '-' }}</td>
                                        <td><span class="badge bg-label-secondary">{{ $c->status }}</span></td>
                                        <td>{{ $c->created_at?->format('Y-m-d') }}</td>
                                        <td>{{ $latestDelivery?->delivered_at?->format('Y-m-d') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('prosthetics.cases.show', $c) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @endforeach
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

