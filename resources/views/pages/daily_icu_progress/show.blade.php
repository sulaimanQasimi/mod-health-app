@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-body">
                       <div class="col-md-12">
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-hourglass p-1"></i>{{ localize('global.daily_icu_progress') }}</h5>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.icu_day') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->icu_day}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.icu_diagnose') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->icu_diagnose}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.daily_events') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->daily_events}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.hr') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->hr}}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.bp') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->bp}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.spo2') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->spo2}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.t') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->t}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.rr') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->rr}}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.gcs') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->gcs}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.cvs') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->cvs}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.pupils') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->pupils}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.s1s2') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->s1s2}}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.rs') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->rs}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.gi') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->gi}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.renal') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->renal}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.musculoskeletal_system') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->musculoskeletal_system}}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.extremities') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->extremities}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.assesment') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->assesment}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.icu_daily_plan') }}</h5>
                                        <p class="p-1">{{$dailyIcuProgress->icu_daily_plan}}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="bg-label-primary p-1 text-center">{{ localize('global.lab_ids') }}</h5>
                                        
                                            @foreach ($labTypes as $value)
                                                <span class="badge bg-primary">
                                                    {{$value->name}}
                                                </span>
                                            @endforeach
                                      
                                    </div>
                                </div>

                            </div>
                            
                        </div>
                        <a href="{{ url()->previous() }}"><button type="button"
                            class="btn btn-danger">{{ localize('global.back') }}</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
