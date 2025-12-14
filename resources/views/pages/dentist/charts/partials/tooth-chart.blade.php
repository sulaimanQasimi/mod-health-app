@php
    // Transform allTeeth array to keyed object for Vue component
    $teethDataKeyed = [];
    foreach ($allTeeth as $toothNumber => $tooth) {
        if ($tooth && isset($tooth->tooth_number)) {
            $teethDataKeyed[$tooth->tooth_number] = [
                'id' => $tooth->id,
                'tooth_number' => $tooth->tooth_number,
                'tooth_condition' => $tooth->tooth_condition,
                'gum_health' => $tooth->gum_health,
                'oral_hygiene_score' => $tooth->oral_hygiene_score,
                'pocket_depth' => $tooth->pocket_depth,
                'bleeding' => $tooth->bleeding,
                'mobility' => $tooth->mobility,
                'treatment_history' => $tooth->treatment_history,
                'chart_date' => $tooth->chart_date ? $tooth->chart_date->format('Y-m-d') : null,
                'notes' => $tooth->notes,
            ];
        }
    }
@endphp

<div id="dental-chart-vue-container" 
     data-dentist-registration-id="{{ $dentistRegistration->id }}"
     data-teeth-data="{{ htmlspecialchars(json_encode($teethDataKeyed), ENT_QUOTES, 'UTF-8') }}">
    <!-- Vue component will mount here -->
    <div class="text-center p-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading chart...</span>
        </div>
    </div>
</div>

<!-- Tooth Modal -->
<div class="modal fade" id="toothModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ localize('global.tooth') }} <span id="modalToothNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="toothModalBody">
                <!-- Content will be loaded via AJAX or form -->
            </div>
        </div>
    </div>
</div>
