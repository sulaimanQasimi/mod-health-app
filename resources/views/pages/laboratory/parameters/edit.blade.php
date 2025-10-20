@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Parameter</h5>
            </div>

            @if(Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="container-xxl flex-grow-1 container-p-y">
                <form action="{{ route('laboratory.parameters.update', $parameter->id) }}" method="POST">
                    @csrf
                    @method('POST')

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $category->id == $parameter->testcategory_id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="test_id">Test</label>
                            <select name="test_id" id="test_id" class="form-control" required>
                                <option value="">Select Test</option>
                                @foreach($tests as $test)
                                    <option value="{{ $test->id }}" {{ $test->id == $parameter->test_id ? 'selected' : '' }}>
                                        {{ $test->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="parameter_name">Parameter Name</label>
                            <input type="text" name="parameter_name" id="parameter_name" class="form-control" 
                                   value="{{ $parameter->parameter_name }}" required>
                        </div>

                        <div class="col-md-4">
                            <label for="unit">Unit</label>
                            <input type="text" name="unit" id="unit" class="form-control" 
                                   value="{{ $parameter->unit }}">
                        </div>

                        <div class="col-md-4">
                            <label for="normal_range">Normal Range</label>
                            <input type="text" name="normal_range" id="normal_range" class="form-control" 
                                   value="{{ $parameter->normal_range }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="result">Result</label>
                            <input type="number" name="result" id="result" class="form-control" 
                                   value="{{ $parameter->result }}" step="0.01">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Parameter</button>
                    <a href="{{ route('laboratory.parameters.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Load tests when category changes
    document.getElementById('category_id').addEventListener('change', function(){
        let categoryId = this.value;
        let testSelect = document.getElementById('test_id');
        testSelect.innerHTML = '<option value="">Loading...</option>';

        if (categoryId) {
            fetch(`/laboratory/tests-by-category/${categoryId}`)
                .then(res => res.json())
                .then(data => {
                    let options = `<option value="">Select Test</option>`;
                    data.forEach(test => {
                        options += `<option value="${test.id}">${test.name}</option>`;
                    });
                    testSelect.innerHTML = options;
                });
        } else {
            testSelect.innerHTML = '<option value="">Select Test</option>';
        }
    });
</script>
@endsection
