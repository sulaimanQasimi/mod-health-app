@php
    $sectionId = $sectionId ?? 'phlebotomy';
    $records = ($records ?? collect())->filter(fn ($item) => $item->isPhlebotomy());
    $canCreate = $canCreate ?? true;
    $requireDepartment = empty($departmentId);
@endphp

<div class="accordion mt-3" id="phlebotomyAccordion{{ $sectionId }}">
    <div class="accordion-item">
        <h2 class="accordion-header" id="phlebotomyHeading{{ $sectionId }}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#phlebotomyCollapse{{ $sectionId }}" aria-expanded="false"
                    aria-controls="phlebotomyCollapse{{ $sectionId }}">
                <i class="bx bx-test-tube me-2 text-primary"></i>
                {{ localize('global.phlebotomy') }}
                @if ($records->count() > 0)
                    <span class="badge bg-primary ms-2">{{ $records->count() }}</span>
                @endif
            </button>
        </h2>
        <div id="phlebotomyCollapse{{ $sectionId }}" class="accordion-collapse collapse"
             aria-labelledby="phlebotomyHeading{{ $sectionId }}"
             data-bs-parent="#phlebotomyAccordion{{ $sectionId }}">
            <div class="accordion-body">
                @if ($canCreate)
                    <button type="button" class="btn btn-sm btn-primary mb-3" data-bs-toggle="modal"
                            data-bs-target="#phlebotomyModal{{ $sectionId }}">
                        <i class="bx bx-plus me-1"></i>{{ localize('global.add') }}
                    </button>

                    <div class="modal fade" id="phlebotomyModal{{ $sectionId }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <form action="{{ route('blood_banks.store') }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ localize('global.phlebotomy') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="{{ localize('global.close') }}"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="feature" value="phlebotomy">
                                        <input type="hidden" name="patient_id" value="{{ $patientId }}">
                                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                                        @if (! empty($appointmentId))
                                            <input type="hidden" name="appointment_id" value="{{ $appointmentId }}">
                                        @endif
                                        @if (! empty($hospitalizationId))
                                            <input type="hidden" name="hospitalization_id" value="{{ $hospitalizationId }}">
                                        @endif
                                        @if (! empty($anesthesiaId))
                                            <input type="hidden" name="anesthesia_id" value="{{ $anesthesiaId }}">
                                        @endif
                                        @if (! empty($operationId))
                                            <input type="hidden" name="operation_id" value="{{ $operationId }}">
                                        @endif
                                        @if (! empty($departmentId))
                                            <input type="hidden" name="department_id" value="{{ $departmentId }}">
                                        @endif

                                        @include('pages.blood_banks.partials.request_form_fields', [
                                            'fieldId' => 'phlebotomy'.$sectionId,
                                            'requireCore' => true,
                                            'hideExtras' => true,
                                            'requireDepartment' => $requireDepartment,
                                            'departments' => $departments ?? [],
                                            'selectedDepartmentId' => $departmentId ?? null,
                                        ])
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            {{ localize('global.cancel') }}
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            {{ localize('global.save') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ localize('global.number') }}</th>
                                <th>{{ localize('global.blood_group') }}</th>
                                <th>{{ localize('global.rh') }}</th>
                                <th>{{ localize('global.blood_type') }}</th>
                                <th>{{ localize('global.quantity') }}</th>
                                <th>{{ localize('global.hemoglobin') }}</th>
                                <th>{{ localize('global.department') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th class="text-center">{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->group ?? '—' }}</td>
                                    <td>{{ $item->rh ?? '—' }}</td>
                                    <td>{{ $item->type ?? '—' }}</td>
                                    <td>{{ $item->quantity ?? '—' }}</td>
                                    <td>{{ $item->hemoglobin ?? '—' }}</td>
                                    <td>{{ $item->department?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ $item->status }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('blood_banks.phlebotomy.show', $item->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="{{ localize('global.view') }}">
                                            <i class="bx bx-expand"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-3">
                                        {{ localize('global.no_phlebotomy_requests') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
