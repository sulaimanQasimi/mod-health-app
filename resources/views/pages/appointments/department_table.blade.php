<div class="table-responsive" id="appointments-table-container">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>{{localize('global.id')}}</th>
                <th>{{localize('global.card_number')}}</th>
                <th>{{localize('global.patient_name')}}</th>
                <th>{{localize('global.father_name')}}</th>
                <th>{{localize('global.department')}}</th>
                <th>{{localize('global.date')}}</th>
                <th>{{localize('global.time')}}</th>
                <th>{{localize('global.status')}}</th>
                <th>{{localize('global.actions')}}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $appointment)
                <tr>
                    <td>{{ $appointment->id }}</td>
                    <td>{{ $appointment->patient->id_card ?? '-' }}</td>
                    <td>{{ $appointment->patient->name ?? '-' }}</td>
                    <td>{{ $appointment->patient->father_name ?? '-' }}</td>
                    <td>{{ $appointment->department->name ?? '-' }}</td>
                    <td>{{ $appointment->jalali_date ?? '-' }}</td>
                    <td>{{ $appointment->time ?? '-' }}</td>
                    <td>
                        @if($appointment->processed_by)
                            <span class="badge bg-success">{{ localize("global.accepted") }}</span>
                        @else
                            <span class="badge bg-warning">{{ localize("global.pending") }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- Change department button -->
                            <button type="button" class="btn btn-sm btn-warning" onclick="openChangeDepartmentModal({{ $appointment->id }}, {{ $appointment->department ? $appointment->department->id : 'null' }})" title="{{ localize('global.change_department') }}">
                                <i class="bx bx-transfer"></i> {{ localize("global.change_department") }}
                            </button>
                            
                            <!-- Accept button only if not processed -->
                            @if(!$appointment->processed_by)
                                <button type="button" class="btn btn-sm btn-success" onclick="acceptAppointment({{ $appointment->id }})">
                                    <i class="bx bx-check"></i> {{ localize("global.accept") }}
                                </button>
                            @endif
                            
                            <!-- Referral remarks if available -->
                            @if($appointment->refferal_remarks)
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $appointment->refferal_remarks }}">
                                    <i class="bx bx-info-circle"></i>
                                </button>
                            @endif
                            
                            <!-- Referring doctor info if available -->
                            @if($appointment->referringDoctor && $appointment->referringDoctor->name)
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ localize('global.introduced_by') }}: {{ $appointment->referringDoctor->name }}">
                                    <i class="bx bx-user"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ localize('global.no_appointments_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Pagination -->
    @if($appointments->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                {{ localize('global.showing') }} 
                <strong>{{ $appointments->firstItem() ?? 0 }}</strong> 
                {{ localize('global.to') }} 
                <strong>{{ $appointments->lastItem() ?? 0 }}</strong> 
                {{ localize('global.of') }} 
                <strong>{{ $appointments->total() }}</strong> 
                {{ localize('global.results') }}
            </div>
            <div>
                {{ $appointments->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

