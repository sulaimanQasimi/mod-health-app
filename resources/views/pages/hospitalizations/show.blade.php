@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.hospitalization_details') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-danger" href="{{ url()->previous() }}"
                           type="button">
                            <span class="text-white"> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <div class="col-md-12">
                        <div class="border border-label-primary mb-4">
                            <h5 class="mb-4 p-3 bg-label-primary text-center">{{ localize('global.hospitalization_details') }}</h5>

                        <div class="row p-2">
                            <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                            <div>
                                {{$hospitalization->patient->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.referred_to') }}</h5>
                            <div>
                                {{$hospitalization->doctor->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.date') }}</h5>
                            <div>
                                {{$hospitalization->created_at}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.time') }}</h5>
                            <div>
                                {{$hospitalization->time}}
                            </div>
                        </div>
                        </div>
                        <div class="row p-2">
                            <div class="col-md-12 mt-2 mb-2">
                            <h5 class="mb-2">{{ localize('global.reason') }}</h5>
                            <div>
                                {{$hospitalization->reason}}
                            </div>
                        </div>
                        <div class="col-md-12 mt-2 mb-2">
                            <h5 class="mb-2">{{ localize('global.remarks') }}</h5>
                            <div>
                                {{$hospitalization->remarks}}
                            </div>
                        </div>
                        </div>

                        </div>

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i class="bx bx-glasses p-1"></i>{{localize('global.visits') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createVisitModal{{ $hospitalization->id }}"><span><i class="bx bx-plus"></i></span></button>
                                <!-- Create visit Modal -->
                                <div class="modal fade" id="createVisitModal{{ $hospitalization->id }}" tabindex="-1" aria-labelledby="createVisitModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createVisitModalLabel{{ $hospitalization->id }}">{{localize('global.add_visit')}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('visits.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}" name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                    <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}" name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                    <input type="hidden" id="doctor_id{{ $hospitalization->id }}" name="doctor_id" value="{{ $hospitalization->doctor->id }}">
                                                    <!-- Add other diagnosis form fields as needed -->
                                                    <div class="form-group">
                                                        <label for="description{{ $hospitalization->id }}">{{localize('global.description')}}</label>
                                                        <textarea class="form-control" id="description{{ $hospitalization->id }}" name="description" rows="3"></textarea>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{localize('global.cancel')}}</button>
                                                <button type="submit" class="btn btn-primary">{{localize('global.save')}}</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Create visit Modal -->
                        <div class="col-md-12 mt-4">




                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.description')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($hospitalization->visits as $visit)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$visit->description}}</td>
                                    <td>
                                        <a href="{{route('visits.edit', $visit->id)}}"><span><i class="bx bx-edit"></i></span></a>
                                        <a href="{{route('visits.destroy', $visit->id)}}"><span><i class="bx bx-trash text-danger"></i></span></a>

                                    </td>
                                </tr>
                                @empty
                                <div class="container">
                                    <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                        <div class=" badge bg-label-danger mt-4">
                                            {{ localize('global.no_previous_visits') }}
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </tbody>
                        </table>

                        </div>


                    </div>

                </div>


            </div>
        </div>
    </div>
</div>

@endsection
