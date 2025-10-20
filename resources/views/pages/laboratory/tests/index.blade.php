@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ localize('global.lab_tests_management') }}</h5>
            </div>

            @if(Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="container-xxl flex-grow-1 container-p-y">
                {{-- Add Lab Test Form --}}
                <form action="{{ route('laboratory.tests.store') }}" method="POST">
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
                            <label for="name">{{ localize('global.test_name') }}</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                </form>

                {{-- Lab Tests Table --}}
                <div class="row mt-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ localize('global.name') }}</th>
                                <th>{{ localize('global.category') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labTests as $test)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $test->name }}</td>
                                <td>{{ $test->category->name ?? '-' }}</td>
                                <td>
                                    {{-- Edit Button --}}
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal" 
                                        data-id="{{ $test->id }}" 
                                        data-name="{{ $test->name }}" 
                                        data-category="{{ $test->category_id }}">
                                        {{ localize('global.edit') }}
                                    </button>

                                    {{-- Delete Button --}}
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal" 
                                        data-id="{{ $test->id }}" 
                                        data-name="{{ $test->name }}">
                                        {{ localize('global.delete') }}
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Lab Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category">Category</label>
                        <select name="category_id" id="edit_category" class="form-control" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_name">Test Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Edit Modal
    var editModal = document.getElementById('editModal')
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget
        var id = button.getAttribute('data-id')
        var name = button.getAttribute('data-name')
        var category = button.getAttribute('data-category')
        var form = document.getElementById('editForm')

        form.action = '/laboratory/tests/' + id
        document.getElementById('edit_name').value = name
        document.getElementById('edit_category').value = category
    })

    // Delete Modal
    var deleteModal = document.getElementById('deleteModal')
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget
        var id = button.getAttribute('data-id')
        var name = button.getAttribute('data-name')
        var form = document.getElementById('deleteForm')

        form.action = '/laboratory/tests/' + id
        document.getElementById('deleteMessage').innerText = `Are you sure you want to delete "${name}"?`
    })
</script>
@endsection
