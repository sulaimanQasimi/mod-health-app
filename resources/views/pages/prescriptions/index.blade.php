@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <!-- Vue.js Prescription Index App -->
            <div id="prescription-index-app" data-permissions="{{ json_encode([
        'canView' => auth()->user()->can('view-prescriptions'),
        'canEdit' => auth()->user()->can('edit-prescriptions'),
        'canDelete' => auth()->user()->can('delete-prescriptions'),
        'canExport' => auth()->user()->can('export-prescriptions')
    ]) }}" data-localize="{{ json_encode([
        'global.filters' => localize('global.filters'),
        'global.search' => localize('global.search'),
        'global.search_by_patient_name' => localize('global.search_by_patient_name'),
        'global.clear_search' => localize('global.clear_search'),
        'global.token_id' => localize('global.token_id'),
        'global.search_by_token_id' => localize('global.search_by_token_id'),
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
        'global.back' => localize('global.back'),
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
        'global.deselect' => localize('global.deselect'),
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
        // Show page keys to support SPA navigation without Blade reload
        'global.prescription_details' => localize('global.prescription_details'),
        'global.type' => localize('global.type'),
        'global.name' => localize('global.name'),
        'global.usage_type' => localize('global.usage_type'),
        'global.dosage' => localize('global.dosage'),
        'global.frequency' => localize('global.frequency'),
        'global.amount' => localize('global.amount'),
        'global.alternatives' => localize('global.alternatives'),
        'global.original' => localize('global.original'),
        'global.original_not_used' => localize('global.original_not_used'),
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
        'global.no_medicines_found' => localize('global.no_medicines_found'),
        'global.in_progress' => localize('global.in_progress'),
        'global.completed' => localize('global.completed'),
        'global.prescription_completed' => localize('global.prescription_completed'),
        'global.prescription_readonly_notice' => localize('global.prescription_readonly_notice'),
        'global.bulk_mark_delivered' => localize('global.bulk_mark_delivered'),
        'global.bulk_mark_not_delivered' => localize('global.bulk_mark_not_delivered'),
        'global.clear_selection' => localize('global.clear_selection'),
        'global.prescription_completed_readonly' => localize('global.prescription_completed_readonly'),
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
        'global.please_select_medicine' => localize('global.please_select_medicine'),
        'global.failed_to_load_prescription_details' => localize('global.failed_to_load_prescription_details'),
        'global.item_status_updated_successfully' => localize('global.item_status_updated_successfully'),
        'global.failed_to_update_item_status' => localize('global.failed_to_update_item_status'),
        'global.items_marked_as_delivered' => localize('global.items_marked_as_delivered'),
        'global.items_marked_as_not_delivered' => localize('global.items_marked_as_not_delivered'),
        'global.failed_to_update_items' => localize('global.failed_to_update_items'),
        'global.prescription_completed_successfully' => localize('global.prescription_completed_successfully'),
        'global.failed_to_complete_prescription' => localize('global.failed_to_complete_prescription'),
        'global.original_data_copied' => localize('global.original_data_copied'),
        'global.alternative_deselected_successfully' => localize('global.alternative_deselected_successfully'),
        'global.alternative_deleted_successfully' => localize('global.alternative_deleted_successfully'),
        'global.alternative_selected_successfully' => localize('global.alternative_selected_successfully'),
        'global.alternative_status_updated_successfully' => localize('global.alternative_status_updated_successfully'),
        'global.failed_to_select_alternative' => localize('global.failed_to_select_alternative'),
        'global.failed_to_deselect_alternative' => localize('global.failed_to_deselect_alternative'),
        'global.failed_to_delete_alternative' => localize('global.failed_to_delete_alternative'),
        'global.failed_to_update_alternative_status' => localize('global.failed_to_update_alternative_status'),
        'global.failed_to_update_alternative_selection' => localize('global.failed_to_update_alternative_selection'),
        'global.failed_to_deselect_alternative_selection' => localize('global.failed_to_deselect_alternative_selection'),
        'global.confirm_select_alternative' => localize('global.confirm_select_alternative'),
        'global.are_you_sure_you_want_to_delete_this_alternative' => localize('global.are_you_sure_you_want_to_delete_this_alternative'),
        'global.cancel' => localize('global.cancel'),
        'global.save' => localize('global.save'),
        'global.enter_amount' => localize('global.enter_amount'),
        'global.close' => localize('global.close'),

    ]) }}" data-branch-id="{{ auth()->user()->branch_id }}">
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script>
        // Global localization function for Vue components
        window.localize = function (key) {
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