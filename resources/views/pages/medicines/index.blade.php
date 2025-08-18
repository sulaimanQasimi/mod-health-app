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
                        <h5 class="mb-0">{{ localize('global.medicines') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            @can('create-medicines')
                            <a class="btn btn-secondary create-new btn-primary" href="{{ route('medicines.create') }}"
                                type="button">
                                <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                            </a>
                            @endcan
                        </div>
                    </div>

                    <!-- Advanced Search Form -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('medicines.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_by_name') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="medicine_type_id" class="form-label">{{ localize('global.medicine_type') }}</label>
                                <select class="form-select" id="medicine_type_id" name="medicine_type_id">
                                    <option value="">{{ localize('global.all_types') }}</option>
                                    @foreach($medicineTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('medicine_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="disease_id" class="form-label">{{ localize('global.for_disease') }}</label>
                                <select class="form-select" id="disease_id" name="disease_id">
                                    <option value="">{{ localize('global.all_diseases') }}</option>
                                    @foreach($diseases as $disease)
                                        <option value="{{ $disease->id }}" {{ request('disease_id') == $disease->id ? 'selected' : '' }}>
                                            {{ $disease->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="per_page" class="form-label">{{ localize('global.per_page') }}</label>
                                <select class="form-select" id="per_page" name="per_page">
                                    @foreach([10, 15, 25, 50, 100] as $perPage)
                                        <option value="{{ $perPage }}" {{ request('per_page', 15) == $perPage ? 'selected' : '' }}>
                                            {{ $perPage }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i> {{ localize('global.search') }}
                                </button>
                                <a href="{{ route('medicines.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh"></i> {{ localize('global.clear') }}
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <!-- Results Summary -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-muted">
                                    {{ localize('global.showing') }} {{ $medicines->firstItem() ?? 0 }} 
                                    {{ localize('global.to') }} {{ $medicines->lastItem() ?? 0 }} 
                                    {{ localize('global.of') }} {{ $medicines->total() }} 
                                    {{ localize('global.results') }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="sort_by" class="form-label me-2 mb-0">{{ localize('global.sort_by') }}:</label>
                                <select class="form-select form-select-sm" id="sort_by" name="sort_by" style="width: auto;" onchange="updateSort()">
                                    <option value="id" {{ request('sort_by', 'id') == 'id' ? 'selected' : '' }}>{{ localize('global.id') }}</option>
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>{{ localize('global.name') }}</option>
                                    <option value="medicine_type_id" {{ request('sort_by') == 'medicine_type_id' ? 'selected' : '' }}>{{ localize('global.medicine_type') }}</option>
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>{{ localize('global.created_at') }}</option>
                                </select>
                                <select class="form-select form-select-sm ms-2" id="sort_order" name="sort_order" style="width: auto;" onchange="updateSort()">
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>{{ localize('global.descending') }}</option>
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>{{ localize('global.ascending') }}</option>
                                </select>
                            </div>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.name')}}</th>
                                    <th>{{localize('global.medicine_type')}}</th>
                                    <th>{{localize('global.for_disease')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($medicines as $medicine)
                                    <tr>
                                        <td>{{ $medicine->id }}</td>
                                        <td>{{ $medicine->name }}</td>
                                        <td>{{ $medicine->medicineType->type ?? 'Null' }}</td>
                                        <td>
                                            @foreach ($medicine->getAssociatedDiseaseAttribute() as $disease)
                                                <span class="badge bg-primary">{{ $disease->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @can('edit-medicines')
                                            <a href="{{route('medicines.edit', $medicine->id)}}"><span><i class="bx bx-message-edit"></i></span></a>
                                            @endcan
                                            @can('delete-medicines')
                                            <a href="{{ route('medicines.destroy', $medicine) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$medicine->id}}').submit(); }">
                                                <i class="bx bx-trash text-danger"></i>
                                            </a>
                                            @endcan
                                            <!-- Using a <form> element -->
                                            <form id="delete-form-{{$medicine->id}}" action="{{ route('medicines.destroy', $medicine) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ localize('global.no_medicines_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <!-- Enhanced Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                {{ localize('global.page') }} {{ $medicines->currentPage() }} 
                                {{ localize('global.of') }} {{ $medicines->lastPage() }}
                            </div>
                            <div>
                                {{ $medicines->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateSort() {
            const sortBy = document.getElementById('sort_by').value;
            const sortOrder = document.getElementById('sort_order').value;
            const url = new URL(window.location);
            url.searchParams.set('sort_by', sortBy);
            url.searchParams.set('sort_order', sortOrder);
            window.location.href = url.toString();
        }
    </script>
@endsection
