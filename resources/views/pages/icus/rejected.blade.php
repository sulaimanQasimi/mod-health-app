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
                        <h5 class="mb-0">{{ localize('global.rejected_icus') }}</h5>
                    </div>
                    <div class="card-body">


                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($icus as $icu)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $icu->patient->name }}</td>
                                        <td>{{ $icu->description}}</td>
                                        <td>
                                            @if ($icu->status == 'new')
                                                <span class="bx bx-x-circle text-danger"></span>
                                            @else
                                                <span class="bx bx-check-circle text-success"></span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('icus.show', $icu) }}"><i
                                                    class="bx bx-expand"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {{ $icus->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
