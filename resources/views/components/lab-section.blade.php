@props([
    'entity' => null,
    'entityType' => 'appointment',
    'entityId' => null,
    'canAddLab' => false,
    'canEditLab' => false,
    'canDeleteLab' => false,
    'appointmentCompleted' => false,
    'accordionId' => 'labTestsAccordion',
    'collapseId' => 'labTestsCollapse',
    'headerId' => 'labTestsHeader'
])

<!-- Lab Tests Accordion -->
<div class="accordion mt-4 border border-warning shadow-sm rounded" id="{{ $accordionId }}">
    <div class="accordion-item">
        <h2 class="accordion-header" id="{{ $headerId }}">
            <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false"
                aria-controls="{{ $collapseId }}">
                <i class="bx bx-test-tube me-2"></i>
                {{ localize('global.checkups') }}
                <span class="badge bg-primary ms-2">{{ count($entity->labs ?? []) }}</span>
            </button>
        </h2>
        <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headerId }}"
            data-bs-parent="#{{ $accordionId }}">
            <div class="accordion-body">
                <!-- Lab Section Vue Component -->
                <div id="lab-section-container" 
                     data-entity='@json($entity)'
                     data-entity-type="{{ $entityType }}"
                     data-entity-id="{{ $entityId }}"
                     data-permissions='@json([
                         "canAddLab" => $canAddLab,
                         "canEditLab" => $canEditLab,
                         "canDeleteLab" => $canDeleteLab
                     ])'
                     data-appointment-completed="{{ $appointmentCompleted ? 'true' : 'false' }}">
                    <!-- Fallback content while Vue loads -->
                    <div class="text-center py-4" id="lab-loading-fallback">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">{{ localize('global.loading_lab_section') }}</p>
                    </div>
                </div>
                
                <!-- Fallback content if Vue fails to load -->
                <div id="lab-fallback-content" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ localize('global.lab_section_load_error') }}
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="location.reload()">
                            {{ localize('global.reload') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Vite script for Lab Section -->
@vite(['public/assets/js/vue/lab-section.js'])
