@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <!-- Vue.js Prescription Index App (Delivered view) -->
            <div id="prescription-index-app" 
                 data-permissions="{{ json_encode([
                     'canView' => auth()->user()->can('view-prescriptions'),
                     'canEdit' => auth()->user()->can('edit-prescriptions'),
                     'canDelete' => auth()->user()->can('delete-prescriptions'),
                     'canExport' => auth()->user()->can('export-prescriptions')
                 ]) }}"
                 data-localize="{{ json_encode([
                     'global.filters' => localize('global.filters'),
                     'global.search' => localize('global.search'),
                     'global.search_by_patient_name' => localize('global.search_by_patient_name'),
                     'global.clear_search' => localize('global.clear_search'),
                    'global.token_id' => localize('global.token_id'),
                    'global.search_by_token_id' => localize('global.search_by_token_id'),
                    'global.doctor_name' => localize('global.doctor_name'),
                    'global.status' => localize('global.status'),
                     'global.all' => localize('global.all'),
                     'global.not_delivered' => localize('global.not_delivered'),
                     'global.delivered' => localize('global.delivered'),
                     'global.date_from' => localize('global.date_from'),
                     'global.date_to' => localize('global.date_to'),
                     'global.clear_filters' => localize('global.clear_filters'),
                     'global.new_prescriptions' => localize('global.new_prescriptions'),
                     'global.export' => localize('global.export'),
                     'global.export_excel' => localize('global.export_excel'),
                     'global.export_pdf' => localize('global.export_pdf'),
                     'global.bulk_actions' => localize('global.bulk_actions'),
                     'global.mark_as_delivered' => localize('global.mark_as_delivered'),
                     'global.mark_as_not_delivered' => localize('global.mark_as_not_delivered'),
                     'global.bulk_delete' => localize('global.bulk_delete'),
                     'global.number' => localize('global.number'),
                     'global.card_number' => localize('global.card_number'),
                     'global.patient_name' => localize('global.patient_name'),
                     'global.father_name' => localize('global.father_name'),
                     'global.referred_to' => localize('global.referred_to'),
                     'global.created_at' => localize('global.created_at'),
                     'global.actions' => localize('global.actions'),
                     'global.view' => localize('global.view'),
                     'global.thermal_print' => localize('global.thermal_print'),
                     'global.no_prescriptions_found' => localize('global.no_prescriptions_found'),
                     'global.loading' => localize('global.loading'),
                     'global.loading_prescriptions' => localize('global.loading_prescriptions'),
                     'global.first' => localize('global.first'),
                     'global.previous' => localize('global.previous'),
                     'global.next' => localize('global.next'),
                     'global.last' => localize('global.last'),
                     'global.confirm_bulk_delete' => localize('global.confirm_bulk_delete'),
                     'global.sorting_by' => localize('global.sorting_by'),
                     'global.sort_ascending' => localize('global.sort_ascending'),
                     'global.sort_descending' => localize('global.sort_descending'),
                     'global.select_all' => localize('global.select_all'),
                     'global.deselect_all' => localize('global.deselect_all'),
                     'global.items_selected' => localize('global.items_selected'),
                     'global.no_items_selected' => localize('global.no_items_selected'),
                     'global.confirm_bulk_action' => localize('global.confirm_bulk_action'),
                     'global.applying_filters' => localize('global.applying_filters'),
                     'global.bulk_print' => localize('global.bulk_print'),
                     'global.bulk_status_updated_successfully' => localize('global.bulk_status_updated_successfully'),
                     'global.failed_to_update_bulk_status' => localize('global.failed_to_update_bulk_status'),
                     'global.bulk_delete_successful' => localize('global.bulk_delete_successful'),
                     'global.failed_to_bulk_delete' => localize('global.failed_to_bulk_delete'),
                     'global.failed_to_export_prescriptions' => localize('global.failed_to_export_prescriptions'),
                     // Keys shared with show page to keep SPA strings complete
                     'global.prescription_details' => localize('global.prescription_details'),
                     'global.type' => localize('global.type'),
                     'global.name' => localize('global.name'),
                     'global.usage_type' => localize('global.usage_type'),
                     'global.dosage' => localize('global.dosage'),
                     'global.frequency' => localize('global.frequency'),
                     'global.amount' => localize('global.amount'),
                     'global.alternatives' => localize('global.alternatives'),
                     'global.back' => localize('global.back'),
                     'global.deselect' => localize('global.deselect'),
                     'global.completed' => localize('global.completed'),
                     
'global.prescription_completed' => localize('global.prescription_completed'),
'global.prescription_readonly_notice' => localize('global.prescription_readonly_notice'),
'global.cancel' => localize('global.cancel'),
'global.save' => localize('global.save'),
'global.enter_amount' => localize('global.enter_amount'),
'global.original' => localize('global.original'),
'global.not_used' => localize('global.not_used'),
'global.selected_alternative' => localize('global.selected_alternative'),
                 ]) }}"
                 data-branch-id="{{ auth()->user()->branch_id }}">
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
<script>
// Global localization function for Vue components
window.localize = function(key) {
    const container = document.getElementById('prescription-index-app');
    if (container) {
        const data = container.dataset.localize;
        const translations = JSON.parse(data || '{}');
        return translations[key] || key;
    }
    return key;
};
</script>
@endpush

@vite(['public/assets/js/vue/prescription-index-app.js'])
