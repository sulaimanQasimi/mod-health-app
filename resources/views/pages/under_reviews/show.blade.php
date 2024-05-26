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
                    <h5 class="mb-0">{{ localize('global.under_review_details') }}</h5>
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
                        <div class="border border-label-primary mb-4 text-center">
                            <h5 class="mb-4 p-3 bg-label-primary text-center">{{ localize('global.under_review_details') }}</h5>

                        <div class="row p-2">
                            <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                            <div>
                                {{$underReview->patient->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.referred_to') }}</h5>
                            <div>
                                {{$underReview->doctor->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.date') }}</h5>
                            <div>
                                {{$underReview->created_at->format('Y-m-d')}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.time') }}</h5>
                            <div>
                                {{$underReview->created_at->format('H:m:s')}}
                            </div>
                        </div>
                        </div>
                        
                        <div class="row text-start m-4">
                            <div class="col-md-12 mt-2 mb-2">
                            <h5 class="mb-2">{{ localize('global.reason') }}</h5>
                            <div>
                                {{$underReview->reason}}
                            </div>
                        </div>
                        <div class="col-md-12 mt-2 mb-2">
                            <h5 class="mb-2">{{ localize('global.remarks') }}</h5>
                            <div>
                                {{$underReview->remarks}}
                            </div>
                        </div>
                        </div>
                        </div>

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i class="bx bx-glasses p-1"></i>{{localize('global.visits') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createVisitModal{{ $underReview->id }}"><span><i class="bx bx-plus"></i></span></button>
                                <!-- Create visit Modal -->
                                <div class="modal fade" id="createVisitModal{{ $underReview->id }}" tabindex="-1" aria-labelledby="createVisitModalLabel{{ $underReview->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createVisitModalLabel{{ $underReview->id }}">{{localize('global.add_visit')}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('visits.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $underReview->patient_id }}" name="patient_id" value="{{ $underReview->patient_id }}">
                                                    <input type="hidden" id="under_review_id{{ $underReview->id }}" name="under_review_id" value="{{ $underReview->id }}">
                                                    <input type="hidden" id="doctor_id{{ $underReview->id }}" name="doctor_id" value="{{ $underReview->doctor->id }}">
                                                    <!-- Add other diagnosis form fields as needed -->
                                                    <div class="form-group">
                                                        <label for="description{{ $underReview->id }}">{{localize('global.description')}}</label>
                                                        <textarea class="form-control" id="description{{ $underReview->id }}" name="description" rows="3"></textarea>
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
                                    <th>{{localize('global.by')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($underReview->visits as $visit)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$visit->description}}</td>
                                    <td>{{$visit->doctor->name}}</td>
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


                        {{-- lab tests from underReview --}}

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                            class="bx bx-hard-hat p-1"></i>{{ localize('global.under_review_checkups') }}</h5>

                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#createLabModal{{ $underReview->id }}"><span><i
                                class="bx bx-plus"></i></span></button>
                    <!-- Create  Lab Modal -->
                    <div class="modal fade" id="createLabModal{{ $underReview->id }}" tabindex="-1"
                        aria-labelledby="createLabModalLabel{{ $underReview->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createLabModalLabel{{ $underReview->id }}">
                                        {{ localize('global.add_lab_test') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('lab_tests.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" id="patient_id{{ $underReview->patient_id }}"
                                            name="patient_id" value="{{ $underReview->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $underReview->appointment->id }}"
                                            name="appointment_id" value="{{ $underReview->id }}">
                                        <input type="hidden" id="doctor_id{{ $underReview->id }}" name="doctor_id"
                                            value="{{ $underReview->doctor->id }}">
                                        <input type="hidden" id="branch_id{{ $underReview->id }}" name="branch_id"
                                            value="{{ auth()->user()->branch_id }}">
                                        <input type="hidden" id="status{{ $underReview->id }}" name="status"
                                            value="0">
                                        <input type="hidden" id="under_review_id{{ $underReview->id }}" name="under_review_id"
                                            value="{{ $underReview->id }}">
                                        <div class="form-group">

                                            <label
                                                for="lab_type_section{{ $underReview->id }}">{{ localize('global.lab_type_section') }}</label>
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
                                                for="lab_type_id{{ $underReview->id }}">{{ localize('global.lab_type') }}</label>
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
                                @forelse ($underReview->labs as $lab)
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

                        {{-- end lab tests from underReview --}}


                        {{-- discharge --}}
                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                            class="bx bx-walk p-1"></i>{{ localize('global.discharge_patient') }}</h5>

                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#createDischargeModal{{ $underReview->id }}"><span><i
                                class="bx bx-plus"></i></span></button>
                    <!-- Create  Lab Modal -->
                    <div class="modal fade" id="createDischargeModal{{ $underReview->id }}" tabindex="-1"
                        aria-labelledby="createDischargeModalLabel{{ $underReview->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createDischargeModalLabel{{ $underReview->id }}">
                                        {{ localize('global.add_lab_test') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('under_reviews.update', $underReview) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" id="is_discharged{{ $underReview->id }}" name="is_discharged"
                                            value="1">
                                            <div class="form-group">
                                                <label
                                                    for="discharge_remark{{ $underReview->id }}">{{ localize('global.discharge_remark') }}</label>
                                                <textarea class="form-control" id="discharge_remark{{ $underReview->id }}" name="discharge_remark" rows="3"></textarea>
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
                        {{$underReview->discharge_remark}}
                    </div>


                        {{-- end discharge --}}


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