<div class="summary-report">
    <h6>{{ localize('global.summary_statistics') }}</h6>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $data['summary']['total_procedures'] ?? 0 }}</h4>
                    <p class="mb-0">{{ localize('global.total_procedures') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $data['summary']['completed_procedures'] ?? 0 }}</h4>
                    <p class="mb-0">{{ localize('global.completed') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $data['summary']['in_progress_procedures'] ?? 0 }}</h4>
                    <p class="mb-0">{{ localize('global.in_progress') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h4>{{ $data['summary']['pending_procedures'] ?? 0 }}</h4>
                    <p class="mb-0">{{ localize('global.pending') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ localize('global.completion_rate') }}</h6>
                    <div class="progress mb-2" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $data['summary']['completion_rate'] ?? 0 }}%">
                            {{ number_format($data['summary']['completion_rate'] ?? 0, 1) }}%
                        </div>
                    </div>
                    <small class="text-muted">{{ $data['summary']['completed_procedures'] ?? 0 }} {{ localize('global.out_of') }} {{ $data['summary']['total_procedures'] ?? 0 }} {{ localize('global.procedures_completed') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ localize('global.duration_statistics') }}</h6>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>{{ localize('global.total_duration') }}:</strong></td>
                            <td>{{ $data['summary']['total_duration'] ?? 0 }} {{ localize('global.minutes') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ localize('global.average_duration') }}:</strong></td>
                            <td>{{ $data['summary']['average_duration'] ?? 0 }} {{ localize('global.minutes') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(isset($data['summary']['procedures']) && $data['summary']['procedures']->count() > 0)
    <div class="mt-4">
        <h6>{{ localize('global.recent_procedures') }}</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ localize('global.id') }}</th>
                        <th>{{ localize('global.patient') }}</th>
                        <th>{{ localize('global.physiotherapy_type') }}</th>
                        <th>{{ localize('global.status') }}</th>
                        <th>{{ localize('global.progress') }}</th>
                        <th>{{ localize('global.start_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['summary']['procedures']->take(10) as $procedure)
                    <tr>
                        <td>{{ $procedure->id }}</td>
                        <td>{{ $procedure->appointment->patient->name ?? 'N/A' }}</td>
                        <td>{{ $procedure->physiotherapyType->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $procedure->status == 'completed' ? 'success' : ($procedure->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                {{ localize('global.' . $procedure->status) }}
                            </span>
                        </td>
                        <td>{{ $procedure->counter }}/{{ $procedure->days_count }}</td>
                        <td>{{ $procedure->start_date ? $procedure->start_date->format('Y-m-d') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
