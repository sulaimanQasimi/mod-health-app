@props([
    'entity' => null,
    'entityType' => 'appointment',
    'entityId' => null,
    'canOpenNephrology' => true,
    'appointmentCompleted' => false,
    'accordionId' => 'nephrologyRegistrationAccordion',
    'collapseId' => 'nephrologyRegistrationCollapse',
    'headerId' => 'nephrologyRegistrationHeader'
])

@php
    $todayVisitDate = verta()->format('Y-m-d');
    $todayVisitDateDisplay = verta()->format('Y/m/d');
    $defaultDoctorId = $entity->doctor_id ?? '';

    $vueTranslations = [
        'global.nephrology_registrations' => localize('global.nephrology_registrations'),
        'global.start_nephrology_visit' => localize('global.start_nephrology_visit'),
        'global.ref_no' => localize('global.ref_no'),
        'global.patient' => localize('global.patient'),
        'global.doctor' => localize('global.doctor'),
        'global.select_doctor' => localize('global.select_doctor'),
        'global.visit_date' => localize('global.visit_date'),
        'global.notes' => localize('global.notes'),
        'global.cancel' => localize('global.cancel'),
        'global.create_and_continue' => localize('global.create_and_continue'),
        'global.status' => localize('global.status'),
        'global.diagnosis' => localize('global.diagnosis'),
        'global.actions' => localize('global.actions'),
        'global.no_nephrology_registrations_found' => localize('global.no_nephrology_registrations_found'),
        'global.failed_to_load_registrations' => localize('global.failed_to_load_registrations'),
        'global.failed_to_create_registration' => localize('global.failed_to_create_registration'),
        'global.please_select_visit_date' => localize('global.please_select_visit_date'),
        'global.nephrology_registration_created_successfully' => localize('global.nephrology_registration_created_successfully'),
        'global.success' => localize('global.success'),
        'global.status_pending' => localize('global.status_pending'),
        'global.status_in_progress' => localize('global.status_in_progress'),
        'global.status_completed' => localize('global.status_completed'),
        'global.status_cancelled' => localize('global.status_cancelled'),
    ];
@endphp

<div class="accordion mt-2" id="{{ $accordionId }}">
    <div class="accordion-item">
        <h2 class="accordion-header" id="{{ $headerId }}">
            <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false"
                aria-controls="{{ $collapseId }}">
                <i class="bx bx-droplet me-2 text-primary"></i>
                {{ localize('global.nephrology') }}
                <span class="badge bg-primary ms-2">{{ count($entity->nephrologyRegistrations ?? []) }}</span>
            </button>
        </h2>
        <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headerId }}"
            data-bs-parent="#{{ $accordionId }}">
            <div class="accordion-body">
                <div id="nephrology-registration-section-container"
                     data-entity='@json($entity)'
                     data-entity-type="{{ $entityType }}"
                     data-entity-id="{{ $entityId }}"
                     data-store-url="{{ route('nephrology-registrations.store', $entityId) }}"
                     data-today-visit-date="{{ $todayVisitDate }}"
                     data-today-visit-date-display="{{ $todayVisitDateDisplay }}"
                     data-default-doctor-id="{{ $defaultDoctorId }}"
                     data-translations='@json($vueTranslations)'
                     data-permissions='@json([
                         "canOpenNephrology" => $canOpenNephrology
                     ])'
                     data-appointment-completed="{{ $appointmentCompleted ? 'true' : 'false' }}">
                    <div class="text-center py-4" id="nephrology-registration-loading-fallback">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ localize('global.loading') }}</span>
                        </div>
                        <p class="mt-2">{{ localize('global.loading_nephrology_section') }}</p>
                    </div>
                </div>
                <div id="nephrology-registration-fallback-content" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ localize('global.nephrology_section_load_error') }}
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="location.reload()">
                            {{ localize('global.reload') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@vite(['public/assets/js/vue/nephrology-registration-section.js'])
