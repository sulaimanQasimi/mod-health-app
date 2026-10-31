@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            @include('pages.blood_banks.partials.blood_requests_filters', [
                'departments' => $departments,
                'filterAction' => url()->current(),
            ])

            @include('pages.blood_banks.partials.blood_requests_table', [
                'bloodRequests' => $bloodRequests,
                'cardTitle' => $cardTitle,
                'showRouteName' => 'blood_banks.phlebotomy.show',
            ])
        </div>
    </div>
@endsection
