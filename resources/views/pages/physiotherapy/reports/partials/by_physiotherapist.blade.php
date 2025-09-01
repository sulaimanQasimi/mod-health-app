<div class="by-physiotherapist-report">
    <h6>{{ localize('global.procedures_by_physiotherapist') }}</h6>
    
    @if($data['physiotherapists']->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ localize('global.physiotherapist') }}</th>
                    <th>{{ localize('global.total_procedures') }}</th>
                    <th>{{ localize('global.completed') }}</th>
                    <th>{{ localize('global.in_progress') }}</th>
                    <th>{{ localize('global.pending') }}</th>
                    <th>{{ localize('global.completion_rate') }}</th>
                    <th>{{ localize('global.total_duration') }}</th>
                    <th>{{ localize('global.average_duration') }}</th>
                    <th>{{ localize('global.performance_score') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['physiotherapists'] as $physiotherapist)
                <tr>
                    <td>
                        <strong>{{ $physiotherapist->name }}</strong>
                        <br><small class="text-muted">{{ $physiotherapist->email ?? 'N/A' }}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">{{ $physiotherapist->total_procedures }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success">{{ $physiotherapist->completed_procedures }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning">{{ $physiotherapist->in_progress_procedures }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $physiotherapist->pending_procedures }}</span>
                    </td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $physiotherapist->completion_rate }}%">
                                {{ number_format($physiotherapist->completion_rate, 1) }}%
                            </div>
                        </div>
                    </td>
                    <td>{{ $physiotherapist->total_duration }} {{ localize('global.minutes') }}</td>
                    <td>{{ $physiotherapist->average_duration }} {{ localize('global.minutes') }}</td>
                    <td>
                        @php
                            $score = $physiotherapist->performance_score;
                            $scoreClass = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger');
                        @endphp
                        <span class="badge bg-{{ $scoreClass }}">{{ number_format($score, 1) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ localize('global.top_performers') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            @foreach($data['physiotherapists']->sortByDesc('performance_score')->take(5) as $physiotherapist)
                            <tr>
                                <td>{{ $physiotherapist->name }}</td>
                                <td class="text-end">{{ number_format($physiotherapist->performance_score, 1) }}</td>
                                <td class="text-end">{{ $physiotherapist->total_procedures }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ localize('global.completion_by_physiotherapist') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            @foreach($data['physiotherapists']->sortByDesc('completion_rate')->take(5) as $physiotherapist)
                            <tr>
                                <td>{{ $physiotherapist->name }}</td>
                                <td class="text-end">{{ number_format($physiotherapist->completion_rate, 1) }}%</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h6>{{ localize('global.recent_procedures_by_physiotherapist') }}</h6>
        @foreach($data['physiotherapists']->take(3) as $physiotherapist)
            @if($physiotherapist->recent_procedures->count() > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ $physiotherapist->name }} - {{ localize('global.recent_procedures') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.patient') }}</th>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.progress') }}</th>
                                    <th>{{ localize('global.start_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($physiotherapist->recent_procedures->take(5) as $procedure)
                                <tr>
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
            </div>
            @endif
        @endforeach
    </div>
    @else
    <div class="text-center py-4">
        <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-2">{{ localize('global.no_procedures_found_for_selected_period') }}</p>
    </div>
    @endif
</div>
