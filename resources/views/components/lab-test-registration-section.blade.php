@props([
    'entity' => null,
    'entityType' => 'appointment',
    'entityId' => null,
    'canAddTestRegistration' => false,
    'appointmentCompleted' => false,
    'accordionId' => 'labTestRegistrationsAccordion',
    'collapseId' => 'labTestRegistrationsCollapse',
    'headerId' => 'labTestRegistrationsHeader'
])

<!-- Lab Test Registrations Accordion -->
<div class="accordion mt-2" id="{{ $accordionId }}">
    <div class="accordion-item">
        <h2 class="accordion-header" id="{{ $headerId }}">
            <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false"
                aria-controls="{{ $collapseId }}">
                <i class="bx bx-test-tube me-2"></i>
                {{ localize('global.lab_test_registrations') }}
                <span class="badge bg-primary ms-2">{{ count($entity->patientTestRegistrations ?? []) }}</span>
            </button>
        </h2>
        <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headerId }}"
            data-bs-parent="#{{ $accordionId }}">
            <div class="accordion-body">
                <!-- Lab Test Registration Section Vue Component -->
                <div id="lab-test-registration-section-container" 
                     data-entity='@json($entity)'
                     data-entity-type="{{ $entityType }}"
                     data-entity-id="{{ $entityId }}"
                     data-permissions='@json([
                         "canAddTestRegistration" => $canAddTestRegistration
                     ])'
                     data-appointment-completed="{{ $appointmentCompleted ? 'true' : 'false' }}">
                    <!-- Fallback content while Vue loads -->
                    <div class="text-center py-4" id="lab-test-registration-loading-fallback">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">{{ localize('global.loading_lab_test_registration_section') }}</p>
                    </div>
                </div>
                
                <!-- Fallback content if Vue fails to load -->
                <div id="lab-test-registration-fallback-content" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ localize('global.lab_test_registration_section_load_error') }}
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="location.reload()">
                            {{ localize('global.reload') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Vite script for Lab Test Registration Section -->
<script>
console.log("Lab Test Registration Section: Script block loaded");
</script>
@vite(['public/assets/js/vue/lab-test-registration-section.js'])
