@php
    $sectionId = $sectionId ?? 'blood';
    $records = collect($records ?? []);
    $requestRecords = $records->filter(fn ($item) => $item->isTransfusionRequest())->values();
    $phlebotomyRecords = $records->filter(fn ($item) => $item->isPhlebotomy())->values();
    $canCreate = $canCreate ?? true;
    $requireDepartment = empty($departmentId);
    $activeTab = request('blood_tab', 'request');
    if (! in_array($activeTab, ['request', 'phlebotomy'], true)) {
        $activeTab = 'request';
    }
    $totalCount = $requestRecords->count() + $phlebotomyRecords->count();
@endphp

<div class="accordion mt-4 mb-4" id="bloodClinicalAccordion{{ $sectionId }}">
    <div class="accordion-item border border-danger rounded">
        <h2 class="accordion-header" id="bloodClinicalHeading{{ $sectionId }}">
            <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                    data-bs-toggle="collapse" data-bs-target="#bloodClinicalCollapse{{ $sectionId }}"
                    aria-expanded="false" aria-controls="bloodClinicalCollapse{{ $sectionId }}">
                <i class="bx bx-donate-blood me-2 text-danger"></i>
                {{ localize('global.blood_bank') }}
                @if ($totalCount > 0)
                    <span class="badge bg-danger ms-2">{{ $totalCount }}</span>
                @endif
            </button>
        </h2>
        <div id="bloodClinicalCollapse{{ $sectionId }}" class="accordion-collapse collapse"
             aria-labelledby="bloodClinicalHeading{{ $sectionId }}"
             data-bs-parent="#bloodClinicalAccordion{{ $sectionId }}">
            <div class="accordion-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}"
                                id="bloodRequestTab{{ $sectionId }}"
                                data-bs-toggle="tab"
                                data-bs-target="#bloodRequestPane{{ $sectionId }}"
                                type="button" role="tab"
                                aria-controls="bloodRequestPane{{ $sectionId }}"
                                aria-selected="{{ $activeTab === 'request' ? 'true' : 'false' }}">
                            {{ localize('global.request_blood') }}
                            <span class="badge bg-label-danger ms-1">{{ $requestRecords->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'phlebotomy' ? 'active' : '' }}"
                                id="bloodPhlebotomyTab{{ $sectionId }}"
                                data-bs-toggle="tab"
                                data-bs-target="#bloodPhlebotomyPane{{ $sectionId }}"
                                type="button" role="tab"
                                aria-controls="bloodPhlebotomyPane{{ $sectionId }}"
                                aria-selected="{{ $activeTab === 'phlebotomy' ? 'true' : 'false' }}">
                            {{ localize('global.phlebotomy') }}
                            <span class="badge bg-label-primary ms-1">{{ $phlebotomyRecords->count() }}</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Request tab --}}
                    <div class="tab-pane fade {{ $activeTab === 'request' ? 'show active' : '' }}"
                         id="bloodRequestPane{{ $sectionId }}" role="tabpanel"
                         aria-labelledby="bloodRequestTab{{ $sectionId }}">
                        @if ($canCreate)
                            <button type="button" class="btn btn-sm btn-danger mb-3" data-bs-toggle="modal"
                                    data-bs-target="#bloodRequestModal{{ $sectionId }}">
                                <i class="bx bx-plus me-1"></i>{{ localize('global.add') }}
                            </button>

                            <div class="modal fade" id="bloodRequestModal{{ $sectionId }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <form action="{{ route('blood_banks.store') }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ localize('global.request_blood') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="{{ localize('global.close') }}"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="feature" value="request">
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
                                                @if (! empty($icuId))
                                                    <input type="hidden" name="i_c_u_id" value="{{ $icuId }}">
                                                @endif
                                                @if (! empty($underReviewId))
                                                    <input type="hidden" name="under_review_id" value="{{ $underReviewId }}">
                                                @endif
                                                @if (! empty($departmentId))
                                                    <input type="hidden" name="department_id" value="{{ $departmentId }}">
                                                @endif

                                                @include('pages.blood_banks.partials.request_form_fields', [
                                                    'fieldId' => 'request'.$sectionId,
                                                    'requireCore' => false,
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
                                        <th>{{ localize('global.status') }}</th>
                                        <th class="text-center">{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($requestRecords as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->group ?? '—' }}</td>
                                            <td>{{ $item->rh ?? '—' }}</td>
                                            <td>{{ $item->type ?? '—' }}</td>
                                            <td>{{ $item->quantity ?? '—' }}</td>
                                            <td>{{ $item->hemoglobin ?? '—' }}</td>
                                            <td><span class="badge bg-label-secondary">{{ $item->status }}</span></td>
                                            <td class="text-center">
                                                <a href="{{ route('blood_banks.show', $item->id) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="{{ localize('global.view') }}">
                                                    <i class="bx bx-expand"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-3">
                                                {{ localize('global.not_referred_to_bloodBank') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Phlebotomy tab --}}
                    <div class="tab-pane fade {{ $activeTab === 'phlebotomy' ? 'show active' : '' }}"
                         id="bloodPhlebotomyPane{{ $sectionId }}" role="tabpanel"
                         aria-labelledby="bloodPhlebotomyTab{{ $sectionId }}">
                        @if ($canCreate)
                            <button type="button" class="btn btn-sm btn-primary mb-3" data-bs-toggle="modal"
                                    data-bs-target="#bloodPhlebotomyModal{{ $sectionId }}">
                                <i class="bx bx-plus me-1"></i>{{ localize('global.add') }}
                            </button>

                            <div class="modal fade" id="bloodPhlebotomyModal{{ $sectionId }}" tabindex="-1" aria-hidden="true">
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
                                                @if (! empty($icuId))
                                                    <input type="hidden" name="i_c_u_id" value="{{ $icuId }}">
                                                @endif
                                                @if (! empty($underReviewId))
                                                    <input type="hidden" name="under_review_id" value="{{ $underReviewId }}">
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
                                    @forelse ($phlebotomyRecords as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->group ?? '—' }}</td>
                                            <td>{{ $item->rh ?? '—' }}</td>
                                            <td>{{ $item->type ?? '—' }}</td>
                                            <td>{{ $item->quantity ?? '—' }}</td>
                                            <td>{{ $item->hemoglobin ?? '—' }}</td>
                                            <td>{{ $item->department?->name ?? '—' }}</td>
                                            <td><span class="badge bg-label-secondary">{{ $item->status }}</span></td>
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
    </div>
</div>
