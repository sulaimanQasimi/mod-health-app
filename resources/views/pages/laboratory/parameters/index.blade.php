@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ localize('global.test_parameters') }}</h5>
            </div>

            @if(Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="container-xxl flex-grow-1 container-p-y">

                {{-- Add Lab Test Parameter Form --}}
                <form action="{{ route('laboratory.parameters.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="category_id">{{ localize('global.category') }}</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">{{ localize('global.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="test_id">{{ localize('global.test') }}</label>
                            <select name="test_id" id="test_id" class="form-control" required>
                                <option value="">{{ localize('global.select_category_first') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Parameter Table --}}
                    <table class="table table-bordered" id="parameters_table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ localize('global.parameter_name') }}</th>
                                <th>{{ localize('global.unit') }}</th>
                                <th>{{ localize('global.normal_range') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><input type="text" name="parameter_name[]" class="form-control" required></td>
                                <td><input type="text" name="unit[]" class="form-control"></td>
                                <td><input type="text" name="normal_range[]" class="form-control"></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">{{ localize('global.remove_parameter') }}</button></td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" id="add_row" class="btn btn-secondary mb-3">{{ localize('global.add_parameter') }}</button>
                    <br>
                    <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                </form>

                {{-- Existing Parameters Table --}}
                <div class="mt-4">
                    <h6>{{ localize('global.existing_parameters') }}</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ localize('global.category') }}</th>
                                <th>{{ localize('global.test') }}</th>
                                <th>{{ localize('global.parameter_name') }}</th>
                                <th>{{ localize('global.unit') }}</th>
                                <th>{{ localize('global.normal_range') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parameters as $param)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $param->testCategory->name ?? '-' }}</td>
                                    <td>{{ $param->labTest->name ?? '-' }}</td>
                                    <td>{{ $param->parameter_name }}</td>
                                    <td>{{ $param->unit }}</td>
                                    <td>{{ $param->normal_range }}</td>
                                    <td>
                                        <a href="{{ route('laboratory.parameters.edit', $param->id) }}" class="btn btn-sm btn-warning">
                                            {{ localize('global.edit') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            @if($parameters->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">{{ localize('global.no_data_found') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
  
  document.addEventListener('DOMContentLoaded', function() {
    const addRowBtn = document.getElementById('add_row');
    const tableBody = document.querySelector('#parameters_table tbody');

    // Add new row
    addRowBtn.addEventListener('click', function() {
        let rowCount = tableBody.rows.length + 1;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${rowCount}</td>
            <td><input type="text" name="parameter_name[]" class="form-control" required></td>
            <td><input type="text" name="unit[]" class="form-control"></td>
            <td><input type="text" name="normal_range[]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
        `;
        tableBody.appendChild(row);

        // Add remove functionality to the new row
        row.querySelector('.remove-row').addEventListener('click', function() {
            row.remove();
            // Re-number remaining rows
            Array.from(tableBody.rows).forEach((r, i) => r.cells[0].innerText = i + 1);
        });
    });

    // Remove existing row(s)
    document.querySelectorAll('.remove-row').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = btn.closest('tr');
            row.remove();
            // Re-number remaining rows
            Array.from(tableBody.rows).forEach((r, i) => r.cells[0].innerText = i + 1);
        });
    });
});

    // Load tests based on selected category
    document.getElementById('category_id').addEventListener('change', function(){
        let categoryId = this.value;
        let testSelect = document.getElementById('test_id');
        testSelect.innerHTML = '<option value="">Loading...</option>';

        fetch(`/laboratory/tests-by-category/${categoryId}`)
            .then(res => res.json())
            .then(data => {
                let options = `<option value="">{{ localize('global.select_test') }}</option>`;
                data.forEach(test => {
                    options += `<option value="${test.id}">${test.name}</option>`;
                });
                testSelect.innerHTML = options;
            });
    });
</script>
@endsection
