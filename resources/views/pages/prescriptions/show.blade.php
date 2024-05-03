@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.prescription_details') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-danger" href="{{ url()->previous() }}"
                           type="button">
                            <span class="text-white"> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                        </a>
                    </div>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.type')}}</th>
            <th>{{localize('global.description')}}</th>
            <th>{{localize('global.dosage')}}</th>
            <th>{{localize('global.frequency')}}</th>
            <th>{{localize('global.amount')}}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $descriptions = is_array($prescription->description) ? $prescription->description : json_decode($prescription->description, true);
            $dosages = is_array($prescription->dosage) ? $prescription->dosage : json_decode($prescription->dosage, true);
            $frequencies = is_array($prescription->frequency) ? $prescription->frequency : json_decode($prescription->frequency, true);
            $amounts = is_array($prescription->amount) ? $prescription->amount : json_decode($prescription->amount, true);
            $types = is_array($prescription->type) ? $prescription->type : json_decode($prescription->type, true);
        @endphp
        @foreach ($descriptions as $key => $description)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $types[$key] }}</td>
                <td>{{ $description }}</td>
                <td>{{ $dosages[$key] }}</td>
                <td>{{ $frequencies[$key] }}</td>
                <td>{{ $amounts[$key] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</div>
@if($prescription->is_delivered == false)
<div class="d-flex justify-content-center m-2">
    <a href="{{ route('prescriptions.issue', $prescription) }}" class="btn btn-success text-white p-2 m-2"><i class="bx bx-check"></i></a>

    <a href="{{ route('prescriptions.reject', $prescription) }}" class="btn btn-danger text-white p-2 m-2"><i class="bx bx-x"></i></a>

</div>
@endif

            </div>
        </div>
    </div>
</div>

@endsection
