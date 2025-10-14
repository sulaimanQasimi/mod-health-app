@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.lab_type_sections') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('create-lab-types')
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('lab_type_sections.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- Pagination Info -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted">
                            {{ localize('global.showing') }} {{ $labTypeSections->firstItem() }} {{ localize('global.to') }} {{ $labTypeSections->lastItem() }} 
                            {{ localize('global.of') }} {{ $labTypeSections->total() }} {{ localize('global.entries') }}
                        </div>
                    </div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.name')}}</th>
            <th>{{localize('global.related_section')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($labTypeSections as $labTypeSection)
            <tr>
                <td>{{ $loop->iteration}}</td>
                <td>{{ $labTypeSection->section }}</td>
                <td>
                    @if($labTypeSection->section_id && $labTypeSection->relatedSection)
                        <span class="badge bg-primary">{{ $labTypeSection->relatedSection->name }}</span>
                    @else
                        <span class="badge bg-secondary">{{ localize('global.no_section') }}</span>
                    @endif
                </td>
                <td>
                    {{-- <a href="{{ route('lab_type_sections.show', $labTypeSection) }}"><i class="bx bx-show-alt"></i></a> --}}
                    @can('edit-lab-types')
                    <a href="{{ route('lab_type_sections.edit', $labTypeSection) }}"><i class="bx bx-message-edit"></i></a>
                    @endcan
                    @can('delete-lab-types')
                    <a href="{{ route('lab_type_sections.destroy', $labTypeSection) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$labTypeSection->id}}').submit(); }">
                        <i class="bx bx-trash text-danger"></i>
                    </a>
                    @endcan
                    <!-- Using a <form> element -->
                    <form id="delete-form-{{$labTypeSection->id}}" action="{{ route('lab_type_sections.destroy', $labTypeSection) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Bootstrap Pagination -->
@if($labTypeSections->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $labTypeSections->links('pagination.bootstrap-4') }}
    </div>
@endif
</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('custom-css')
<style>
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        color: #6c757d;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
    }
    
    .pagination .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }
    
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
</style>
@endpush
