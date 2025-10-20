@extends('layouts.master')

@section('content')
<div class="card p-4">
    <h4>{{ localize('global.register_patient_test') }}</h4>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('laboratory.registrations.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-4">
                <label>{{ localize('global.patient') }}</label>
                <select id="patient_id" name="patient_id" class="form-select" required></select>
            </div>

            <div class="col-md-4">
                <label>{{ localize('global.test_category') }}</label>
                <select name="test_category_id" id="test_category" class="form-select" required>
                    <option value="">{{ localize('global.select_category') }}</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>{{ localize('global.test_name') }}</label>
                <select name="lab_test_id" id="lab_test" class="form-select" required>
                    <option value="">{{ localize('global.select_test') }}</option>
                </select>
            </div>
        </div>
        
        <div id="parameter-section" class="mt-3">
            <h5>{{ localize('global.test_parameters') }}</h5>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="select_all_parameters">
                <label class="form-check-label" for="select_all_parameters">{{ localize('global.select_all_parameters') }}</label>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ localize('global.select') }}</th>
                            <th>{{ localize('global.parameter_name') }}</th>
                            <th>{{ localize('global.normal_range') }}</th>
                        </tr>
                    </thead>
                    <tbody id="parameter-list">
                        <!-- Dynamic rows -->
                    </tbody>
                </table>
            </div>
        </div>

        <button class="btn btn-primary mt-3" type="submit">{{ localize('global.register_patient_test') }}</button>
    </form>
</div>
@endsection

@section('scripts')
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Patient autocomplete
        $('#patient_id').select2({
            placeholder: '{{ localize("global.patient_search_placeholder") }}',
            ajax: {
                url: '{{ route("laboratory.search-patients") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        // Load lab tests when category changes
        $('#test_category').on('change', function() {
            let categoryId = $(this).val();
            if (categoryId) {
                $.get('{{ route("laboratory.get-tests", ":id") }}'.replace(':id', categoryId))
                .done(function(data) {
                    let options = '<option value="">{{ localize("global.select_test") }}</option>';
                    data.forEach(t => options += `<option value="${t.id}">${t.name}</option>`);
                    $('#lab_test').html(options);
                    $('#parameter-list').html('');
                    $('#select_all_parameters').prop('checked', false);
                })
                .fail(function() {
                    alert('{{ localize("global.error_loading_tests") }}');
                    $('#lab_test').html('<option value="">{{ localize("global.select_test") }}</option>');
                    $('#parameter-list').html('');
                    $('#select_all_parameters').prop('checked', false);
                });
            } else {
                $('#lab_test').html('<option value="">{{ localize("global.select_test") }}</option>');
                $('#parameter-list').html('');
                $('#select_all_parameters').prop('checked', false);
            }
        });

        $('#lab_test').on('change', function() {
            let testId = $(this).val();
            $('#select_all_parameters').prop('checked', false);
            $('#parameter-list').html('');

            if (!testId) return;

            $.get('{{ route("laboratory.get-parameters", ":id") }}'.replace(':id', testId))
                .done(function(data) {
                    if (data.length === 0) {
                        $('#parameter-list').html('<tr><td colspan="3"><em>{{ localize("global.no_parameters_found") }}</em></td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach(p => {
                        rows += `
                <tr>
                    <td>
                          <input type="checkbox" class="form-check-input parameter-checkbox"
                       name="test_parameter_ids[]" 
                       value="${p.id}">
                <input type="hidden" name="parameter_ids[]" value="${p.id}">
                    </td>
                    <td>${p.parameter_name}</td>
                    <td>${p.normal_range || ''}</td>
                </tr>`;
                    });
                    $('#parameter-list').html(rows);
                })
                .fail(function(xhr) {
                    console.error(xhr.responseText);
                    $('#parameter-list').html('<tr><td colspan="3"><em>{{ localize("global.error_loading_parameters") }}</em></td></tr>');
                });
        });

        // Select/Deselect all parameters
        $('#select_all_parameters').on('change', function() {
            let checked = $(this).is(':checked');
            $('.parameter-checkbox').prop('checked', checked);
        });

        // Update "Select All" checkbox dynamically
        $('#parameter-list').on('change', '.parameter-checkbox', function() {
            let total = $('.parameter-checkbox').length;
            let checked = $('.parameter-checkbox:checked').length;
            $('#select_all_parameters').prop('checked', total === checked);
        });

    });
</script>
@endsection
