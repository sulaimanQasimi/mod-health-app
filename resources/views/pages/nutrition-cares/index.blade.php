@extends('layouts.master')

@section('title', localize('global.nutrition_care'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.nutrition_care') }}</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNutritionCareModal">
                            <i class="fas fa-plus"></i> {{ localize('global.create_nutrition_care') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('nutrition-cares.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" placeholder="{{ localize('global.patient_name') }}" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="morphable_type" class="form-control">
                                        <option value="">{{ localize('global.all_types') }}</option>
                                        <option value="App\Models\UnderReview" {{ request('morphable_type') == 'App\Models\UnderReview' ? 'selected' : '' }}>{{ localize('global.under_review') }}</option>
                                        <option value="App\Models\Hospitalization" {{ request('morphable_type') == 'App\Models\Hospitalization' ? 'selected' : '' }}>{{ localize('global.hospitalization') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="morphable_id" class="form-control" placeholder="Record ID" value="{{ request('morphable_id') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="nutritionCaresTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.nurse') }}</th>
                                    <th>{{ localize('global.observations') }}</th>
                                    <th>{{ localize('global.interventions') }}</th>
                                    <th>{{ localize('global.nutrition_care_full_note') }}</th>
                                    <th>{{ localize('global.date_signature') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Nutrition Care Modal -->
<div class="modal fade" id="createNutritionCareModal" tabindex="-1" aria-labelledby="createNutritionCareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createNutritionCareModalLabel">{{ localize('global.create_nutrition_care') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createNutritionCareForm">
                <div class="modal-body">
                    @include('pages.nutrition-cares.partials.form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ localize('global.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Nutrition Care Modal -->
<div class="modal fade" id="editNutritionCareModal" tabindex="-1" aria-labelledby="editNutritionCareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNutritionCareModalLabel">{{ localize('global.edit_nutrition_care') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editNutritionCareForm">
                <div class="modal-body">
                    @include('pages.nutrition-cares.partials.form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ localize('global.update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load nutrition cares data
    loadNutritionCares();

    // Create form submission
    $('#createNutritionCareForm').on('submit', function(e) {
        e.preventDefault();
        createNutritionCare();
    });

    // Edit form submission
    $('#editNutritionCareForm').on('submit', function(e) {
        e.preventDefault();
        updateNutritionCare();
    });
});

function loadNutritionCares() {
    $.ajax({
        url: '{{ route("nutrition-cares.index") }}',
        method: 'GET',
        data: {
            ajax: true,
            morphable_type: '{{ request("morphable_type") }}',
            morphable_id: '{{ request("morphable_id") }}'
        },
        success: function(response) {
            var tbody = $('#nutritionCaresTable tbody');
            tbody.empty();
            
            if (response.data.length === 0) {
                tbody.append('<tr><td colspan="7" class="text-center">No nutrition care records found</td></tr>');
                return;
            }

            response.data.forEach(function(record) {
                var observations = [];
                var interventions = [];
                
                // Collect observation fields
                if (record.cough) observations.push('{{ localize("global.cough") }}');
                if (record.sound) observations.push('{{ localize("global.sound") }}');
                if (record.fluid_swallowing_ability) observations.push('{{ localize("global.fluid_swallowing_ability") }}');
                if (record.weight) observations.push('{{ localize("global.weight") }}');
                if (record.amount_and_type_of_nutrition) observations.push('{{ localize("global.amount_and_type_of_nutrition") }}');
                if (record.diarrhea) observations.push('{{ localize("global.diarrhea") }}');
                if (record.heart_failure_and_kidney_disease) observations.push('{{ localize("global.heart_failure_and_kidney_disease") }}');
                if (record.remaining_materials) observations.push('{{ localize("global.remaining_materials") }}');
                if (record.type_of_tube) observations.push('{{ localize("global.type_of_tube") }}');

                // Collect intervention fields
                if (record.constipation) interventions.push('{{ localize("global.constipation") }}');
                if (record.nutrition_is_provided) interventions.push('{{ localize("global.nutrition_is_provided") }}');
                if (record.mouth_hygiene) interventions.push('{{ localize("global.mouth_hygiene") }}');
                if (record.oral_nutrition_advices) interventions.push('{{ localize("global.oral_nutrition_advices") }}');
                if (record.voice_exercise) interventions.push('{{ localize("global.voice_exercise") }}');
                if (record.swallowing_exercise) interventions.push('{{ localize("global.swallowing_exercise") }}');
                if (record.aspiration_prevention_proceeded) interventions.push('{{ localize("global.aspiration_prevention_proceeded") }}');

                var row = '<tr>' +
                    '<td>' + record.id + '</td>' +
                    '<td>' + record.patient_name + '</td>' +
                    '<td>' + (record.nurse ? record.nurse.full_name : 'N/A') + '</td>' +
                    '<td>' + (observations.length > 0 ? observations.join(', ') : '-') + '</td>' +
                    '<td>' + (interventions.length > 0 ? interventions.join(', ') : '-') + '</td>' +
                    '<td>' + (record.nutrition_care_full_note ? record.nutrition_care_full_note.substring(0, 50) + '...' : '-') + '</td>' +
                    '<td>' + new Date(record.created_at).toLocaleDateString() + '</td>' +
                    '<td>' +
                        '<div class="btn-group" role="group">' +
                            '<button class="btn btn-sm btn-info" onclick="viewNutritionCare(' + record.id + ')" title="View">' +
                                '<i class="fas fa-eye"></i>' +
                            '</button>' +
                            '<button class="btn btn-sm btn-warning" onclick="editNutritionCare(' + record.id + ')" title="Edit">' +
                                '<i class="fas fa-edit"></i>' +
                            '</button>' +
                            '<button class="btn btn-sm btn-danger" onclick="deleteNutritionCare(' + record.id + ')" title="Delete">' +
                                '<i class="fas fa-trash"></i>' +
                            '</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
                tbody.append(row);
            });
        },
        error: function(xhr) {
            console.error('Error loading nutrition cares:', xhr);
        }
    });
}

function createNutritionCare() {
    var formData = $('#createNutritionCareForm').serialize();
    
    $.ajax({
        url: '{{ route("nutrition-cares.store") }}',
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#createNutritionCareModal').modal('hide');
            $('#createNutritionCareForm')[0].reset();
            loadNutritionCares();
            showAlert('success', response.message);
        },
        error: function(xhr) {
            var errors = xhr.responseJSON.errors;
            if (errors) {
                showAlert('error', Object.values(errors).flat().join('<br>'));
            } else {
                showAlert('error', 'An error occurred while creating the nutrition care record.');
            }
        }
    });
}

function editNutritionCare(id) {
    $.ajax({
        url: '/nutrition-cares/' + id,
        method: 'GET',
        success: function(response) {
            // Populate edit form with data
            $('#editNutritionCareForm input[name="patient_name"]').val(response.patient_name);
            $('#editNutritionCareForm select[name="nurse_id"]').val(response.nurse_id);
            $('#editNutritionCareForm textarea[name="nutrition_care_full_note"]').val(response.nutrition_care_full_note);
            
            // Set boolean fields
            var booleanFields = ['cough', 'sound', 'fluid_swallowing_ability', 'weight', 'amount_and_type_of_nutrition', 
                               'diarrhea', 'heart_failure_and_kidney_disease', 'remaining_materials', 'type_of_tube',
                               'constipation', 'nutrition_is_provided', 'mouth_hygiene', 'oral_nutrition_advices',
                               'voice_exercise', 'swallowing_exercise', 'aspiration_prevention_proceeded'];
            
            booleanFields.forEach(function(field) {
                $('#editNutritionCareForm input[name="' + field + '"]').prop('checked', response[field]);
            });
            
            $('#editNutritionCareForm').attr('data-id', id);
            $('#editNutritionCareModal').modal('show');
        },
        error: function(xhr) {
            showAlert('error', 'Error loading nutrition care record.');
        }
    });
}

function updateNutritionCare() {
    var id = $('#editNutritionCareForm').attr('data-id');
    var formData = $('#editNutritionCareForm').serialize();
    
    $.ajax({
        url: '/nutrition-cares/' + id,
        method: 'PUT',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#editNutritionCareModal').modal('hide');
            loadNutritionCares();
            showAlert('success', response.message);
        },
        error: function(xhr) {
            var errors = xhr.responseJSON.errors;
            if (errors) {
                showAlert('error', Object.values(errors).flat().join('<br>'));
            } else {
                showAlert('error', 'An error occurred while updating the nutrition care record.');
            }
        }
    });
}

function deleteNutritionCare(id) {
    if (confirm('Are you sure you want to delete this nutrition care record?')) {
        $.ajax({
            url: '/nutrition-cares/' + id,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                loadNutritionCares();
                showAlert('success', response.message);
            },
            error: function(xhr) {
                showAlert('error', 'An error occurred while deleting the nutrition care record.');
            }
        });
    }
}

function viewNutritionCare(id) {
    window.open('/nutrition-cares/' + id, '_blank');
}

function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
    
    $('.card-body').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endpush
