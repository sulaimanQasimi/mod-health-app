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
                    <h5 class="mb-0">{{ localize('global.list_section') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('create-sections')
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('sections.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.name')}}</th>
            <th>{{localize('global.departments')}}</th>
            <th>{{localize('global.lab_type_sections')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sections as $section)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $section->name }}</td>
                <td>{{ $section->department->name ?? 'NULL' }}</td>
                <td>
                    <span class="badge bg-info">—</span>
                </td>
                <td>
                    {{-- <a href="{{ route('sections.show', $section) }}"><i class="bx bx-show-alt"></i></a> --}}
                    @can('delete-sections')
                    <a href="{{ route('sections.edit', $section) }}"><i class="bx bx-message-edit"></i></a>
                    @endcan
                    @can('delete-sections')
                    <!-- Using an <a> tag -->
                        <a href="{{ route('sections.destroy', $section->id) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{ $section->id }}').submit(); }">
                            <i class="bx bx-trash text-danger"></i>
                        </a>
                        @endcan
                        <!-- Using a <form> element -->
                        <form id="delete-form-{{ $section->id }}" action="{{ route('sections.destroy', $section->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Pagination -->
@if($sections->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        {{-- Per Page Selector --}}
        <div class="d-flex align-items-center">
            <label for="per_page" class="form-label me-2 mb-0">
                {{ localize('global.per_page') ?: 'Per page' }}:
            </label>
            <select class="form-select form-select-sm" id="per_page" name="per_page" style="width: auto;" onchange="changePerPage(this.value)">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ request('per_page') == 15 || !request('per_page') ? 'selected' : '' }}>15</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>
        
        {{-- Pagination Navigation --}}
        <nav aria-label="{{ localize('global.pagination') ?: 'Pagination' }}">
            <ul class="pagination pagination-simple mb-0">
                {{-- Previous Page --}}
                @if($sections->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="bx bx-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $sections->previousPageUrl() }}">
                            <i class="bx bx-chevron-left"></i>
                        </a>
                    </li>
                @endif
                
                {{-- Page Numbers --}}
                @php
                    $currentPage = $sections->currentPage();
                    $lastPage = $sections->lastPage();
                    $startPage = max(1, $currentPage - 1);
                    $endPage = min($lastPage, $currentPage + 1);
                @endphp
                
                {{-- Show first page if not in range --}}
                @if($startPage > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $sections->url(1) }}">1</a>
                    </li>
                    @if($startPage > 2)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif
                
                {{-- Page numbers in range --}}
                @for($i = $startPage; $i <= $endPage; $i++)
                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                        @if($i == $currentPage)
                            <span class="page-link">{{ $i }}</span>
                        @else
                            <a class="page-link" href="{{ $sections->url($i) }}">{{ $i }}</a>
                        @endif
                    </li>
                @endfor
                
                {{-- Show last page if not in range --}}
                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $sections->url($lastPage) }}">{{ $lastPage }}</a>
                    </li>
                @endif
                
                {{-- Next Page --}}
                @if($sections->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $sections->nextPageUrl() }}">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="bx bx-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
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
    /* Simple Pagination Styles */
    .pagination-simple {
        margin-bottom: 0;
        gap: 0.25rem;
    }
    
    .pagination-simple .page-link {
        border-radius: 0.375rem;
        margin: 0 2px;
        border: 1px solid #dee2e6;
        color: #6c757d;
        background-color: #fff;
        transition: all 0.2s ease;
        min-width: 38px;
        text-align: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .pagination-simple .page-link:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
        color: #495057;
    }
    
    .pagination-simple .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
    
    .pagination-simple .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
        cursor: not-allowed;
    }
    
    /* Per page selector */
    .form-select-sm {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }
    
    .form-select-sm:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }
        
        .d-flex.justify-content-between > div {
            width: 100%;
            justify-content: center;
        }
        
        /* Mobile pagination adjustments */
        .pagination-simple {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .pagination-simple .page-link {
            min-width: 35px;
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Mobile per page selector */
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .d-flex.justify-content-between > div:first-child {
            order: 2;
        }
        
        .d-flex.justify-content-between > nav {
            order: 1;
        }
    }
    
    @media (max-width: 576px) {
        .pagination-simple .page-link {
            min-width: 30px;
            padding: 0.25rem 0.375rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@push('custom-js')
<script>
// Change per page function
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>
@endpush
