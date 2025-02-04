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
                    <h5 class="mb-0">{{ localize('global.delivered_prescriptions') }}</h5>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.patient_name')}}</th>
            <th>{{localize('global.referred_to')}}</th>
            <th>{{localize('global.created_at')}}</th>
            <th>{{localize('global.status')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($prescriptions as $prescription)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $prescription->patient->name }}</td>
                <td>{{ $prescription->doctor->name }}</td>
                <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($prescription->created_at) }}</td>
                <td>{{ $prescription->is_completed == 0 ? localize('global.not_delivered') : localize('global.delivered') }}</td>
                <td>
                    <a href="{{ route('prescriptions.show', $prescription) }}"><i class="bx bx-show-alt"></i></a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="col-md-12 mt-4 mb-4">
    {{$prescriptions->links('pagination::bootstrap-4')}}
</div>
</div>
            </div>
        </div>
    </div>
</div>

@endsection
