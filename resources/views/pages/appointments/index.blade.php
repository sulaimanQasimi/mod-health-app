@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.appointments_list') }}</h5>
                </div>
                <div class="card-body">

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{localize('global.number')}}</th>
                                <th>{{localize('global.patient_name')}}</th>
                                <th>{{localize('global.last_name')}}</th>
                                <th>{{localize('global.referred_to')}}</th>
                                <th>{{localize('global.date')}}</th>
                                <th>{{localize('global.time')}}</th>
                                <th>{{localize('global.actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $appointment->patient->name }}</td>
                                    <td>{{ $appointment->patient->last_name }}</td>
                                    <td>{{ $appointment->doctor->name }}</td>
                                    <td>{{ $appointment->date }}</td>
                                    <td>{{ $appointment->time }}</td>
                                    <td>
                                        <a href="{{route('appointments.show', $appointment->id)}}"><span><i class="bx bx-expand"></i></span></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="col-md-12 mt-4 mb-4">
                        {{$appointments->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
