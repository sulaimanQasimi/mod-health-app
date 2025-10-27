@extends('layouts.master')

@section('content')
    <!-- Vue.js Prescription Show App -->
    <div id="prescription-show-app" 
         data-prescription-id="{{ $prescription->id }}"
         data-localize="{{ json_encode([
             'global.prescription_details' => localize('global.prescription_details'),
             'global.number' => localize('global.number'),
             'global.patient_name' => localize('global.patient_name'),
             'global.status' => localize('global.status'),
             'global.actions' => localize('global.actions'),
             'global.not_delivered' => localize('global.not_delivered'),
             'global.delivered' => localize('global.delivered'),
             'global.complete_prescription' => localize('global.complete_prescription'),
             'global.thermal_print' => localize('global.thermal_print'),
             'global.back' => localize('global.back'),
             'global.type' => localize('global.type'),
             'global.name' => localize('global.name'),
             'global.usage_type' => localize('global.usage_type'),
             'global.dosage' => localize('global.dosage'),
             'global.frequency' => localize('global.frequency'),
             'global.amount' => localize('global.amount'),
             'global.alternatives' => localize('global.alternatives'),
             'global.original' => localize('global.original'),
             'global.not_used' => localize('global.not_used'),
             'global.selected_alternative' => localize('global.selected_alternative'),
             'global.deselect_alternative' => localize('global.deselect_alternative'),
             'global.alternatives_for' => localize('global.alternatives_for'),
             'global.add_alternative' => localize('global.add_alternative'),
             'global.medicine' => localize('global.medicine'),
             'global.select_medicine' => localize('global.select_medicine'),
             'global.medicine_type' => localize('global.medicine_type'),
             'global.select_type' => localize('global.select_type'),
             'global.select_usage_type' => localize('global.select_usage_type'),
             'global.notes' => localize('global.notes'),
             'global.existing_alternatives' => localize('global.existing_alternatives'),
             'global.selected' => localize('global.selected'),
             'global.select_alternative' => localize('global.select_alternative'),
             'global.mark_not_delivered' => localize('global.mark_not_delivered'),
             'global.mark_delivered' => localize('global.mark_delivered'),
             'global.delete_alternative' => localize('global.delete_alternative'),
             'global.no_alternatives_found' => localize('global.no_alternatives_found'),
             'global.close' => localize('global.close'),
             'global.loading' => localize('global.loading')
         ]) }}">
    </div>
@endsection

@push('custom-js')
<script>
// Global localization function for Vue components
window.localize = function(key) {
    const data = document.getElementById('prescription-show-app').dataset.localize;
    const translations = JSON.parse(data);
    return translations[key] || key;
};
</script>
@endpush

@vite(['public/assets/js/vue/prescription-show-app.js'])

