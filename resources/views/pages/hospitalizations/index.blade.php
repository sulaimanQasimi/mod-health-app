@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.hospitalized_patients') }}</h5>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.patient_name')}}</th>
            <th>{{localize('global.room')}}</th>
            <th>{{localize('global.bed')}}</th>
            <th>{{localize('global.hospitalization_date')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($hospitalizations as $hospitalization)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $hospitalization->patient->name }}</td>
                <td>{{ $hospitalization->room->name }}</td>
                <td>{{ $hospitalization->bed->number }}</td>
                <td>{{ $hospitalization->created_at }}</td>
                <td>
                    <a href="{{ route('hospitalizations.show', $hospitalization) }}"><i class="bx bx-show-alt"></i></a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="col-md-12 mt-4 mb-4">
    {{$hospitalizations->links('pagination::bootstrap-4')}}
</div>
</div>
            </div>
        </div>
    </div>
</div>

@endsection
