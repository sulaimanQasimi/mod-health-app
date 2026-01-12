<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>{{ localize('global.number') }}</th>
                <th>{{ localize('global.patient_name') }}</th>
                <th>{{ localize('global.father_name') }}</th>
                <th>{{ localize('global.last_name') }}</th>
                <th>{{ localize('global.age') }}</th>
                <th>{{ localize('global.gender') }}</th>
                <th>{{ localize('global.nid') }}</th>
                <th>{{ localize('global.job') }}</th>
                <th>{{ localize('global.appointment_created_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @if($appointments && $appointments->count() > 0)
                @foreach($appointments as $index => $appointment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $appointment->patient->name ?? '—' }}</td>
                        <td>{{ $appointment->patient->father_name ?? '—' }}</td>
                        <td>{{ $appointment->patient->last_name ?? '—' }}</td>
                        <td>{{ $appointment->patient->age ?? '—' }}</td>
                        <td>
                            @if(isset($appointment->patient->gender))
                                @if($appointment->patient->gender == 0 || $appointment->patient->gender == '0')
                                    {{ localize('global.male') }}
                                @elseif($appointment->patient->gender == 1 || $appointment->patient->gender == '1')
                                    {{ localize('global.female') }}
                                @elseif($appointment->patient->gender == 'male')
                                    {{ localize('global.male') }}
                                @elseif($appointment->patient->gender == 'female')
                                    {{ localize('global.female') }}
                                @else
                                    {{ $appointment->patient->gender }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $appointment->patient->nid ?? '—' }}</td>
                        <td>{{ $appointment->patient->job ?? '—' }}</td>
                        <td>{{ $appointment->persian_created_at ?? '—' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        @if(request('department_id'))
                            {{ localize('global.no_appointments_found') }}
                        @else
                            {{ localize('global.please_select_department_and_date_range') }}
                        @endif
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
    
    @if($appointments && $appointments->count() > 0)
        <div class="mt-3 text-muted">
            <small>
                <i class="bx bx-info-circle me-1"></i>
                {{ localize('global.total_records') }}: <strong>{{ $appointments->count() }}</strong>
            </small>
        </div>
    @endif
</div>
