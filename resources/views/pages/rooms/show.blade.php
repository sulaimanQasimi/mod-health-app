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
                        <h5 class="mb-0">{{ localize('global.room_details') }}</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <strong>{{ localize('global.name') }}:</strong>
                            <p>{{ $room->name }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>{{ localize('global.floor') }}:</strong>
                            <p>{{ $room->floor->name }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>{{ localize('global.department') }}:</strong>
                            <p>{{ $room->department->name }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>{{ localize('global.branch') }}:</strong>
                            <p>{{ $room->branch->name }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>{{ localize('global.number_of_beds') }}:</strong>
                            <p>{{ $room->beds->count() }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>{{ localize('global.beds') }}:</strong>
                            @foreach($room->beds as $bed)
                                <span class="badge bg-primary">{{$bed->number}}</span>
                            @endforeach
                        </div>
                        {{-- <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-primary">{{ localize('global.edit') }}</a> --}}
                        <a href="{{ route('rooms.index') }}" class="btn btn-danger">{{ localize('global.back') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
