@props([
    'entity' => null,
    'entityType' => 'appointment',
    'entityId' => null,
    'canAddDentistRegistration' => true,
    'appointmentCompleted' => false,
    'accordionId' => 'dentistRegistrationAccordion',
    'collapseId' => 'dentistRegistrationCollapse',
    'headerId' => 'dentistRegistrationHeader'
])

<!-- Dentist Registrations Accordion -->
<div class="accordion mt-2" id="{{ $accordionId }}">
    <div class="accordion-item">
        <h2 class="accordion-header" id="{{ $headerId }}">
            <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false"
                aria-controls="{{ $collapseId }}">
                <i class="bx bx-plus-medical me-2 text-primary"></i>
                {{ localize('global.dentist_registration') }}
                <span class="badge bg-primary ms-2">{{ count($entity->dentistRegistrations ?? []) }}</span>
            </button>
        </h2>
        <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headerId }}"
            data-bs-parent="#{{ $accordionId }}">
            <div class="accordion-body">
                <!-- Dentist Registration Section Vue Component -->
                <div id="dentist-registration-section-container" 
                     data-entity='@json($entity)'
                     data-entity-type="{{ $entityType }}"
                     data-entity-id="{{ $entityId }}"
                     data-permissions='@json([
                         "canAddDentistRegistration" => $canAddDentistRegistration
                     ])'
                     data-appointment-completed="{{ $appointmentCompleted ? 'true' : 'false' }}">
                    <!-- Fallback content while Vue loads -->
                    <div class="text-center py-4" id="dentist-registration-loading-fallback">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">{{ localize('global.loading_dentist_registration_section') }}</p>
                    </div>
                </div>
                
                <!-- Fallback content if Vue fails to load -->
                <div id="dentist-registration-fallback-content" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ localize('global.dentist_registration_section_load_error') }}
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="location.reload()">
                            {{ localize('global.reload') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Vite script for Dentist Registration Section -->
@vite(['public/assets/js/vue/dentist-registration-section.js'])
