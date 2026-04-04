@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            @include('pages.blood_banks.partials.blood_requests_filters', ['departments' => $departments])

            @include('pages.blood_banks.partials.blood_requests_table', [
                'bloodRequests' => $bloodRequests,
                'cardTitle' => localize('global.rejected_blood_requests'),
            ])
        </div>
    </div>
@endsection
