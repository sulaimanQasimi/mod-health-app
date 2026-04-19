<div class="detailed-report">
    <h6>{{ localize('global.detailed_procedure_list') }}</h6>
    
    @if(isset($data['detailed']) && $data['detailed']->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ localize('global.id') }}</th>
                    <th>{{ localize('global.patient') }}</th>
                    <th>{{ localize('global.physiotherapy_type') }}</th>
                    <th>{{ localize('global.physiotherapist') }}</th>
                    <th>{{ localize('global.type') }}</th>
                    <th>{{ localize('global.duration') }}</th>
                    <th>{{ localize('global.progress') }}</th>
                    <th>{{ localize('global.status') }}</th>
                    <th>{{ localize('global.start_date') }}</th>
                    <th>{{ localize('global.end_date') }}</th>
                    <th>{{ localize('global.created_at') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['detailed'] as $procedure)
                <tr>
                    <td>{{ $procedure->id }}</td>
                    <td>{{ $procedure->appointment->patient->name ?? 'N/A' }}</td>
                    <td>{{ $procedure->physiotherapyType->name ?? 'N/A' }}</td>
                    <td>{{ $procedure->doctor->name ?? 'N/A' }}</td>
                    <td>{{ $procedure->type }}</td>
                    <td>{{ $procedure->duration }} {{ localize('global.minutes') }}</td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            @php
                                $percentage = $procedure->days_count > 0 ? ($procedure->counter / $procedure->days_count) * 100 : 0;
                            @endphp
                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%">
                                {{ $procedure->counter }}/{{ $procedure->days_count }}
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $procedure->status == 'completed' ? 'success' : ($procedure->status == 'in_progress' ? 'warning' : ($procedure->status == 'cancelled' ? 'danger' : 'secondary')) }}">
                            {{ localize('global.' . $procedure->status) }}
                        </span>
                    </td>
                    <td>{{ $procedure->start_date ? $procedure->start_date->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $procedure->end_date ? $procedure->end_date->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $procedure->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <small class="text-muted">{{ localize('global.showing_all_procedures') }} ({{ $data['detailed']->count() }} {{ localize('global.total') }})</small>
    </div>
    @else
    <div class="text-center py-4">
        <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-2">{{ localize('global.no_procedures_found_for_selected_period') }}</p>
    </div>
    @endif
</div>
