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
                        <h5 class="mb-0">{{ localize('global.delivered_blood_requests') }}</h5>
                    </div>
                    <div class="card-body">


                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.requested_department') }}</th>
                                    <th>{{ localize('global.blood_group') }}</th>
                                    <th>{{ localize('global.rh') }}</th>
                                    <th>{{ localize('global.blood_type') }}</th>
                                    <th>{{ localize('global.quantity') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bloodRequests as $bloodRequest)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $bloodRequest->patient->name }}</td>
                                        <td>{{ $bloodRequest->department->name }}</td>
                                        <td>
                                            @if ($bloodRequest->group == 'A')
                                                <span class="text-danger"><i class="bx fa-solid fa-a"></i></span>
                                            @elseif($bloodRequest->group == 'B')
                                                <span class="text-danger"><i class="bx fa-solid fa-b"></i></span>
                                            @elseif($bloodRequest->group == 'AB')
                                                <span class="text-danger" dir="ltr"><i class="bx fa-solid fa-a"></i><i
                                                        class="bx fa-solid fa-b"></i></span>
                                            @elseif($bloodRequest->group == 'O')
                                                <span class="text-danger"><i class="bx fa-solid fa-o"></i></span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($bloodRequest->rh == '+')
                                            <span class="bx bx-plus-circle text-danger"></span>
                                        @else
                                            <span class="bx bx-minus-circle text-danger"></span>
                                        @endif
                                        </td>
                                        <td>{{ $bloodRequest->type }}</td>
                                        <td>{{ $bloodRequest->quantity }}</td>
                                        <td>
                                            {{$bloodRequest->status}}
                                                    
                                        </td>
                                        <td>
                                            <a href="{{ route('blood_banks.show', $bloodRequest->id) }}"><span><i
                                                class="bx bx-expand"></i></span></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {{ $bloodRequests->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
