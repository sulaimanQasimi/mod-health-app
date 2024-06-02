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
                        <h5 class="mb-0">{{ localize('global.icu_details') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
                                <span class="text-white"> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="col-md-12">
                            <div class="border border-label-primary mb-4 text-center p-4">
                                <h5 class="mb-4 p-3 bg-label-primary text-center">{{ localize('global.icu_details') }}</h5>

                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-3">

                                                    <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.last_name') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->last_name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.phone') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->phone }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.nid') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->nid }}
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-3">

                                                    <h5 class="mb-2">{{ localize('global.province') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->province->name_dr }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.district') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->district->name_dr }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.referred_by') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->recipient->name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.creation_date') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->created_at }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 card p-1">
                                            <!-- Left side content -->
                                            <div class="row">
                                                <div class="col-md-6 d-flex justify-content-end align-items-center">
                                                    {!! QrCode::size(100)->generate($icu->patient->id) !!}
                                                </div>
                                                <div class="col-md-6 d-flex justify-content-start align-items-center">
                                                    @isset($icu->patient->image)
                                                        <img src="{{ asset($icu->patient->image) }}" alt="Patient Image"
                                                            width="100" height="100">
                                                    @else
                                                        <div class=" badge bg-label-danger mt-4">
                                                            {{ localize('global.no_image') }}
                                                        </div>
                                                    @endisset
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.all_diagnoses') }}</h5>
                            <div class="row p-4">
                                <div class="mb-4">
                                    @php
                                        $primaryDiagnoses = $previousDiagnoses->where('type', 0);
                                        $finalDiagnoses = $previousDiagnoses->where('type', 1);
                                    @endphp

                                    <div class="container">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="col-md-12">
                                                        <h5 class="mb-4 p-1 bg-label-warning text-center"><i
                                                                class="bx bx-popsicle p-1"></i>{{ localize('global.primary_diagnoses') }}
                                                        </h5>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="col-md-12">
                                                        <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                                                class="bx bx-popsicle p-1"></i>{{ localize('global.final_diagnoses') }}
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    @foreach ($primaryDiagnoses as $diagnose)
                                                        <li class="m-1 p-1">
                                                            <span
                                                                class="bg-label-warning text-center p-1">{{ $diagnose->created_at->format('Y-m-d') }}</span>
                                                            {{ $diagnose->description }}
                                                        </li>
                                                    @endforeach
                                                </div>
                                                <div class="col-md-6">
                                                    @foreach ($finalDiagnoses as $diagnose)
                                                        <li class="m-1 p-1">
                                                            <span
                                                                class="bg-label-success text-center p-1">{{ $diagnose->created_at->format('Y-m-d') }}</span>
                                                            {{ $diagnose->description }}
                                                        </li>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.previous_labs') }}</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.test_name') }}</th>
                                        <th>{{ localize('global.test_status') }}</th>
                                        <th>{{ localize('global.result') }}</th>
                                        <th>{{ localize('global.result_file') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($previousLabs as $lab)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $lab->labType->name }}</td>
                                            <td>
                                                @if ($lab->status == '0')
                                                    <span
                                                        class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ localize('global.tested') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $lab->result }}</td>
                                            <td>
                                                @isset($lab->result_file)
                                                    <a href="{{ asset('storage/' . $lab->result_file) }}" target="_blank">
                                                        <i class="fa fa-file"></i> {{ localize('global.file') }}
                                                    </a>
                                                @endisset

                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($icu->status == 'new')
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-glasses p-1"></i>{{ localize('global.approve_reject_icu') }}</h5>

                            <div class="row d-flex justify-content-center">


                                <div class="d-flex justify-content-center p-2">
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#createICUApproveModal{{ $icu->id }}"><span><i
                                                    class="bx bx-check"></i>{{localize('global.approve')}}</span></button>
                                        </div>

                                        <div class="col-md-2">
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#createICURejectModal{{ $icu->id }}"><span><i
                                                class="bx bx-x"></i>{{localize('global.reject')}}</span></button>
                                        </div>
                                </div>
                                @endif
                            </div>

                            <div class="modal fade" id="createICUApproveModal{{ $icu->id }}" tabindex="-1"
                                aria-labelledby="createICUApproveModalLabel{{ $icu->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createICUApproveModalLabel{{ $icu->id }}">
                                                {{ localize('global.approve_icu') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('icus.update', $icu) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden"
                                                    name="status" value="approved">

                                                <div class="form-group">

                                                    <div class="form-group">
                                                        <label
                                                            for="icu_enterance_note{{ $icu->id }}">{{ localize('global.icu_enterance_note') }}</label>
                                                        <textarea class="form-control" id="icu_enterance_note{{ $icu->id }}" name="icu_enterance_note" rows="3"></textarea>
                                                    </div>



                                                </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                            <button type="submit"
                                                class="btn btn-primary">{{ localize('global.save') }}</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="createICURejectModal{{ $icu->id }}" tabindex="-1"
                                aria-labelledby="createICURejectModalLabel{{ $icu->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createICURejectModalLabel{{ $icu->id }}">
                                                {{ localize('global.reject_icu') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('icus.update', $icu) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden"
                                                    name="status" value="rejected">

                                                <div class="form-group">

                                                    <div class="form-group">
                                                        <label
                                                            for="icu_reject_reason{{ $icu->id }}">{{ localize('global.icu_reject_reason') }}</label>
                                                        <textarea class="form-control" id="icu_reject_reason{{ $icu->id }}" name="icu_reject_reason" rows="3"></textarea>
                                                    </div>



                                                </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                            <button type="submit"
                                                class="btn btn-primary">{{ localize('global.save') }}</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>






                            @if($icu->status == 'approved')
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-glasses p-1"></i>{{ localize('global.visits') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createVisitModal{{ $icu->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>
                            <!-- Create visit Modal -->
                            <div class="modal fade" id="createVisitModal{{ $icu->id }}" tabindex="-1"
                                aria-labelledby="createVisitModalLabel{{ $icu->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createVisitModalLabel{{ $icu->id }}">
                                                {{ localize('global.add_visit') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('visits.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $icu->patient_id }}"
                                                    name="patient_id" value="{{ $icu->patient_id }}">
                                                <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                    value="{{ $icu->id }}">
                                                <input type="hidden" id="doctor_id{{ $icu->id }}" name="doctor_id"
                                                    value="{{ $icu->doctor->id }}">
                                                <!-- Add other diagnosis form fields as needed -->
                                                <div class="form-group">
                                                    <label
                                                        for="description{{ $icu->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control" id="description{{ $icu->id }}" name="description" rows="3"></textarea>
                                                </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                            <button type="submit"
                                                class="btn btn-primary">{{ localize('global.save') }}</button>
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
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.description') }}</th>
                                            <th>{{ localize('global.by') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($icu->visits as $visit)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $visit->description }}</td>
                                                <td>{{ $visit->doctor->name }}</td>
                                                <td>
                                                    <a href="{{ route('visits.edit', $visit->id) }}"><span><i
                                                                class="bx bx-edit"></i></span></a>
                                                    <a href="{{ route('visits.destroy', $visit->id) }}"><span><i
                                                                class="bx bx-trash text-danger"></i></span></a>

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

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-hard-hat p-1"></i>{{ localize('global.checkups') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createLabModal{{ $icu->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>

                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createLabModal{{ $icu->id }}" tabindex="-1"
                            aria-labelledby="createLabModalLabel{{ $icu->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createLabModalLabel{{ $icu->id }}">
                                            {{ localize('global.add_lab_test') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('lab_tests.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $icu->patient_id }}"
                                                name="patient_id" value="{{ $icu->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $icu->id }}"
                                                name="appointment_id" value="{{ $icu->appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $icu->id }}" name="doctor_id"
                                                value="{{ $icu->doctor->id }}">
                                            <input type="hidden" id="branch_id{{ $icu->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">
                                            <input type="hidden" id="hospitalization_id{{ $icu->id }}"
                                                name="hospitalization_id" value="">
                                                <input type="hidden" id="i_c_u_id{{ $icu->id }}"
                                                name="i_c_u_id" value="{{$icu->id}}">

                                            <input type="hidden" id="status{{ $icu->id }}" name="status"
                                                value="0">

                                            <div class="form-group">

                                                <label
                                                    for="lab_type_section{{ $icu->id }}">{{ localize('global.lab_type_section') }}</label>
                                                <select class="form-control select2" name="lab_type_section"
                                                    id="lab_type_section">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($labTypeSections as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->section }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label
                                                    for="lab_type_id{{ $icu->id }}">{{ localize('global.lab_type') }}</label>
                                                <select class="form-control select2" name="lab_type_id[]"
                                                    id="lab_type_id" onchange="loadLabTypeTests()">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($labTypes as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div id="labTypeTestsContainer"></div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Lab Modal -->
                        <div class="col-md-12 mt-4">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.test_name') }}</th>
                                        <th>{{ localize('global.test_status') }}</th>
                                        <th>{{ localize('global.result') }}</th>
                                        <th>{{ localize('global.result_file') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($icu->labs as $lab)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $lab->labType->name }}</td>
                                            <td>
                                                @if ($lab->status == '0')
                                                    <span
                                                        class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ localize('global.tested') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $lab->result }}</td>
                                            <td>
                                                @isset($lab->result_file)
                                                    <a href="{{ asset('storage/' . $lab->result_file) }}" target="_blank">
                                                        <i class="fa fa-file"></i> {{ localize('global.file') }}
                                                    </a>
                                                @endisset

                                            </td>
                                            <td>
                                                <a href="{{ route('lab_tests.edit', $lab->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('lab_tests.destroy', $lab->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

                                            </td>

                                        </tr>

                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.no_previous_labs') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse

                                </tbody>
                            </table>
                        </div>

@endif

                        </div>

                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function loadLabTypeTests() {
            var labTypeId = document.getElementById('lab_type_id').value;
            var labTypeTestsContainer = document.getElementById('labTypeTestsContainer');
            labTypeTestsContainer.innerHTML = ''; // Clear previous checkboxes

            // Make an AJAX request to fetch the lab type tests based on the selected lab_type_id
            fetch('/lab-tests/' + labTypeId)
                .then(response => response.json())
                .then(data => {
                    // Create checkboxes for each lab type test
                    data.forEach(function(test) {
                        var checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'lab_type_id[]'; // Use an array to submit multiple values
                        checkbox.value = test.id;

                        // Update the lab_type_id value when a checkbox is checked/unchecked
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                // Append the test id to the lab_type_id value
                                document.getElementById('lab_type_id').value += ',' + this.value;
                            } else {
                                // Remove the test id from the lab_type_id value
                                var labTypeIdValue = document.getElementById('lab_type_id').value;
                                labTypeIdValue = labTypeIdValue.replace(',' + this.value, '');
                                labTypeIdValue = labTypeIdValue.replace(this.value + ',', '');
                                labTypeIdValue = labTypeIdValue.replace(this.value, '');
                                document.getElementById('lab_type_id').value = labTypeIdValue;
                            }
                        });

                        // Create a label for the checkbox
                        var label = document.createElement('label');
                        label.appendChild(checkbox);
                        label.appendChild(document.createTextNode(test.name));

                        // Append the checkbox to the labTypeTestsContainer
                        labTypeTestsContainer.appendChild(label);
                    });
                })
                .catch(error => {
                    console.log(error);
                });
        }
    </script>
@endsection
