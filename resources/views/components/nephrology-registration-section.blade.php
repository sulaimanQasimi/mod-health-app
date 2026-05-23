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
                     data-open-url="{{ route('nephrology-registrations.open', $entityId) }}"
                     data-permissions='@json([
                         "canOpenNephrology" => $canOpenNephrology
                     ])'
                     data-appointment-completed="{{ $appointmentCompleted ? 'true' : 'false' }}">
                    <div class="text-center py-4" id="nephrology-registration-loading-fallback">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
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
