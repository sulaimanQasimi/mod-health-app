@extends('layouts.master')

@section('content')
    <!-- Vue.js Prescription Show App -->
    <div id="prescription-show-app" 
         data-prescription-id="{{ $prescription->id }}"
         data-localize="{{ json_encode([
         'global.auto_filled' => localize('global.auto_filled'),
         'global.select' => localize('global.select'),
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
             'global.loading' => localize('global.loading'),
             'global.auto-filled' => localize('global.auto-filled'),
             'global.select_medicine_to_auto_fill' => localize('global.select_medicine_to_auto_fill'),
             'global.deselect' => localize('global.deselect'),
             'global.in_progress' => localize('global.in_progress'),
             'global.completed' => localize('global.completed'),
             'global.reject_prescription' => localize('global.reject_prescription'),
             'global.confirm_reject_prescription' => localize('global.confirm_reject_prescription'),
             'global.prescription_rejected_successfully' => localize('global.prescription_rejected_successfully'),
             'global.failed_to_reject_prescription' => localize('global.failed_to_reject_prescription'),
             'global.prescription_marked_as_delivered' => localize('global.prescription_marked_as_delivered'),
             'global.failed_to_mark_delivered' => localize('global.failed_to_mark_delivered'),
             'global.no_items_to_mark_delivered' => localize('global.no_items_to_mark_delivered'),
             'global.alternative_added_and_selected_successfully' => localize('global.alternative_added_and_selected_successfully'),
            'global.edit_amount' => localize('global.edit_amount'),
            'global.enter_amount' => localize('global.enter_amount'),
            'global.amount_updated_successfully' => localize('global.amount_updated_successfully'),
            'global.failed_to_update_amount' => localize('global.failed_to_update_amount'),
            'global.please_enter_amount' => localize('global.please_enter_amount'),
            'global.validation_errors' => localize('global.validation_errors'),
            'global.cancel' => localize('global.cancel'),
            'global.save' => localize('global.save'),
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

