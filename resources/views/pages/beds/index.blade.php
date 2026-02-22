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
                    <h5 class="mb-0">{{ localize('global.beds') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('create-beds')
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('beds.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">

            {{-- Filter --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-none border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bx bx-filter-alt text-primary me-2"></i>{{ localize('global.filter') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('beds.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="bx bx-search me-1 text-primary"></i>{{ localize('global.search') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="bx bx-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}"
                                           placeholder="{{ localize('global.bed_number') }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="room_id" class="form-label fw-semibold">{{ localize('global.room') }}</label>
                                <select class="form-select" id="room_id" name="room_id">
                                    <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="is_occupied" class="form-label fw-semibold">{{ localize('global.status') ?: 'Status' }}</label>
                                <select class="form-select" id="is_occupied" name="is_occupied">
                                    <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                    <option value="0" {{ request('is_occupied') === '0' ? 'selected' : '' }}>{{ localize('global.available') ?: 'Available' }}</option>
                                    <option value="1" {{ request('is_occupied') === '1' ? 'selected' : '' }}>{{ localize('global.occupied') ?: 'Occupied' }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-filter me-1"></i>{{ localize('global.filter') }}
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('beds.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.bed_number')}}</th>
            <th>{{localize('global.related_room')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($beds as $bed)
            <tr>
                <td>{{ $beds->firstItem() ? ($loop->iteration + $beds->firstItem() - 1) : $loop->iteration }}</td>
                <td>{{ $bed->number }}</td>
                <td>{{ $bed->room->name ?? '—' }}</td>
                <td>
                    {{-- <a href="{{ route('beds.show', $bed) }}"><i class="bx bx-show-alt"></i></a> --}}
                    @can('edit-beds')
                    <a href="{{ route('beds.edit', $bed) }}"><i class="bx bx-message-edit"></i></a>
                    @endcan
                    @can('delete-beds')
                    <!-- Using an <a> tag -->
                    <a href="{{ route('beds.destroy', $bed) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$bed->id}}').submit(); }">
                        <i class="bx bx-trash text-danger"></i>
                    </a>
                    @endcan
                    <!-- Using a <form> element -->
                    <form id="delete-form-{{$bed->id}}" action="{{ route('beds.destroy', $bed) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    {{ $beds->links() }}
</div>
</div>
            </div>
        </div>
    </div>
</div>

@endsection
