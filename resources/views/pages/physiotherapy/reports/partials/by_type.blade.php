<div class="by-type-report">
    <h6>{{ localize('global.procedures_by_type') }}</h6>
    
    @if(isset($data['by_type']) && $data['by_type']->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ localize('global.physiotherapy_type') }}</th>
                    <th>{{ localize('global.total_procedures') }}</th>
                    <th>{{ localize('global.completed') }}</th>
                    <th>{{ localize('global.in_progress') }}</th>
                    <th>{{ localize('global.pending') }}</th>
                    <th>{{ localize('global.completion_rate') }}</th>
                    <th>{{ localize('global.total_duration') }}</th>
                    <th>{{ localize('global.average_duration') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['by_type'] as $type)
                <tr>
                    <td>
                        <strong>{{ $type['type']->name ?? 'N/A' }}</strong>
                        @if(isset($type['type']->description) && $type['type']->description)
                            <br><small class="text-muted">{{ Str::limit($type['type']->description, 50) }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">{{ $type['total_procedures'] ?? 0 }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success">{{ $type['completed_procedures'] ?? 0 }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning">{{ $type['in_progress_procedures'] ?? 0 }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $type['pending_procedures'] ?? 0 }}</span>
                    </td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $type['completion_rate'] ?? 0 }}%">
                                {{ number_format($type['completion_rate'] ?? 0, 1) }}%
                            </div>
                        </div>
                    </td>
                    <td>{{ $type['total_duration'] ?? 0 }} {{ localize('global.minutes') }}</td>
                    <td>{{ $type['average_duration'] ?? 0 }} {{ localize('global.minutes') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ localize('global.type_distribution') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            @foreach($data['by_type']->take(5) as $type)
                            <tr>
                                <td>{{ $type['type']->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ $type['total_procedures'] ?? 0 }}</td>
                                <td class="text-end">{{ number_format(($type['total_procedures'] ?? 0) / ($data['by_type']->sum('total_procedures') ?: 1) * 100, 1) }}%</td>
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
                    <h6 class="card-title">{{ localize('global.completion_by_type') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            @foreach($data['by_type']->sortByDesc('completion_rate')->take(5) as $type)
                            <tr>
                                <td>{{ $type['type']->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($type['completion_rate'] ?? 0, 1) }}%</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-4">
        <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-2">{{ localize('global.no_procedures_found_for_selected_period') }}</p>
    </div>
    @endif
</div>
