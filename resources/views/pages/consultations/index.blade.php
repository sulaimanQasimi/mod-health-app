@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.my_consultations') }}</h5>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            {{-- <th>{{localize('global.patient_name')}}</th> --}}
            <th>{{localize('global.title')}}</th>
            <th>{{localize('global.time')}}</th>
            <th>{{localize('global.result')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($consultations as $consultation)
            <tr>
                <td>{{ $loop->iteration }}</td>
                {{-- <td>{{ $consultation->patient->name }}</td> --}}
                <td>{{ $consultation->title }}</td>
                <td>{{ $consultation->created_at }}</td>
                <td>{{ $consultation->result }}</td>
                <td>
                    <a href="{{ route('consultations.show', $consultation) }}"><i class="bx bx-show-alt"></i></a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="col-md-12 mt-4 mb-4">
    {{$consultations->links('pagination::bootstrap-4')}}
</div>
</div>
            </div>
        </div>
    </div>
</div>

@endsection
