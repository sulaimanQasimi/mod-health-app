<div class="col-md-12 mt-4">
    <h5 class="mb-4 p-3 bg-label-primary">
        <i class="bx bx-bar-chart p-1"></i>{{ localize('global.diabetes_charts') }}
    </h5>
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('diabetes-charts.print', ['chartable_type' => $morphableType, 'chartable_id' => $morphableId]) }}"
            class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> {{ localize('global.print_chart') }}
        </a>
        <a href="{{ route('diabetes-charts.create', ['chartable_type' => $morphableType, 'chartable_id' => $morphableId]) }}"
            class="btn btn-success">
            <i class="bx bx-plus"></i> {{ localize('global.add_diabetes_chart') }}
        </a>
    </div>

    @if($diabetesCharts->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ localize('global.date') }}</th>
                        <th>{{ localize('global.time') }}</th>
                        <th>{{ localize('global.rbs') }}</th>
                        <th>{{ localize('global.fbs') }}</th>
                        <th>{{ localize('global.insulin_dose') }}</th>
                        <th>{{ localize('global.unit') }}</th>
                        <th>{{ localize('global.nurse') }}</th>
                        <th>{{ localize('global.medicine') }}</th>
                        <th>{{ localize('global.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($diabetesCharts as $chart)
                        <tr>
                            <td>{{ $chart->id }}</td>
                            <td>
                                @if($chart->date)
                                    <span class="badge bg-info">{{ $chart->date->format('Y-m-d') }}</span>
                                @else
                                    <span class="text-muted">{{ localize('global.not_set') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($chart->time)
                                    <span class="badge bg-secondary">{{ $chart->formatted_time }}</span>
                                @else
                                    <span class="text-muted">{{ localize('global.not_set') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($chart->rbs)
                                    <span class="badge bg-warning">{{ $chart->rbs }}
                                        {{ $chart->unit }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($chart->fbs)
                                    <span class="badge bg-success">{{ $chart->fbs }}
                                        {{ $chart->unit }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($chart->insulin_dose)
                                    <span class="badge bg-primary">{{ $chart->insulin_dose }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($chart->unit)
                                    <small>{{ $chart->unit }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($chart->nurse)
                                    <span class="badge bg-info">{{ $chart->nurse->full_name }}</span>
                                @else
                                    <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($chart->medicine)
                                    <span class="badge bg-secondary">{{ $chart->medicine->name }}</span>
                                @else
                                    <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('diabetes-charts.show', $chart) }}"
                                        class="btn btn-sm btn-info"
                                        title="{{ localize('global.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('diabetes-charts.edit', $chart) }}"
                                        class="btn btn-sm btn-warning"
                                        title="{{ localize('global.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('diabetes-charts.destroy', $chart) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            title="{{ localize('global.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4">
            <div class="mb-3">
                <i class="bx bx-clipboard bx-lg text-muted"></i>
            </div>
            <h5 class="text-muted">{{ localize('global.no_diabetes_charts_found') }}</h5>
            <p class="text-muted">{{ localize('global.add_first_diabetes_chart') }}</p>
            <a href="{{ route('diabetes-charts.create', ['chartable_type' => $morphableType, 'chartable_id' => $morphableId]) }}"
                class="btn btn-primary">
                <i class="bx bx-plus"></i> {{ localize('global.add_diabetes_chart') }}
            </a>
        </div>
    @endif
</div>
