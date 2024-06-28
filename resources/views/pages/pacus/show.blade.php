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
                                <h5 class="mb-4 p-3 bg-label-primary text-center">{{ localize('global.pacu_details') }}</h5>

                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-3">

                                                    <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                                    <div>
                                                        {{ $pacu->patient->name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.last_name') }}</h5>
                                                    <div>
                                                        {{ $pacu->patient->last_name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.phone') }}</h5>
                                                    <div>
                                                        {{ $pacu->patient->phone }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.nid') }}</h5>
                                                    <div>
                                                        {{ $pacu->patient->nid }}
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-3">

                                                    <h5 class="mb-2">{{ localize('global.province') }}</h5>
                                                    <div>
                                                        {{ $pacu->patient->province->name_dr }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.district') }}</h5>
                                                    <div>
                                                        {{ $pacu->patient->district->name_dr }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.referred_by') }}</h5>
                                                    <div>
                                                        {{ $pacu->patient->recipient->name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.creation_date') }}</h5>
                                                    <div>
                                                        {{ $pacu->patient->created_at }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 card p-1">
                                            <!-- Left side content -->
                                            <div class="row">
                                                <div class="col-md-6 d-flex justify-content-end align-items-center">
                                                    {!! QrCode::size(100)->generate($pacu->patient->id) !!}
                                                </div>
                                                <div class="col-md-6 d-flex justify-content-start align-items-center">
                                                    @isset($pacu->patient->image)
                                                        <img src="{{ asset($pacu->patient->image) }}" alt="Patient Image"
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

                            @if ($pacu->status == 'new')
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-glasses p-1"></i>{{ localize('global.mark_pacu_complete') }}</h5>

                                <div class="row d-flex justify-content-center">


                                    <div class="d-flex justify-content-center p-2">
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#createPACUCompletionModal{{ $pacu->id }}"><span><i
                                                        class="bx bx-check"></i>{{ localize('global.complete') }}</span></button>
                                        </div>
                                    </div>
                            @endif
                        </div>

                        <div class="modal fade" id="createPACUCompletionModal{{ $pacu->id }}" tabindex="-1"
                            aria-labelledby="createPACUCompletionModalLabel{{ $pacu->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createPACUCompletionModalLabel{{ $pacu->id }}">
                                            {{ localize('global.approve_icu') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('pacus.update', $pacu) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="completed">

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







                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-glasses p-1"></i>{{ localize('global.visits') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createVisitModal{{ $pacu->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>
                            <!-- Create visit Modal -->
                            <div class="modal fade" id="createVisitModal{{ $pacu->id }}" tabindex="-1"
                                aria-labelledby="createVisitModalLabel{{ $pacu->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createVisitModalLabel{{ $pacu->id }}">
                                                {{ localize('global.add_visit') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('visits.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $pacu->patient_id }}"
                                                    name="patient_id" value="{{ $pacu->patient_id }}">
                                                <input type="hidden" id="p_a_c_u_id{{ $pacu->id }}" name="p_a_c_u_id"
                                                    value="{{ $pacu->id }}">
                                                <input type="hidden" id="doctor_id{{ $pacu->id }}"
                                                    name="doctor_id" value="{{ $pacu->doctor->id }}">
                                                <!-- Add other diagnosis form fields as needed -->
                                                <div class="form-group">
                                                    <label
                                                        for="description{{ $pacu->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control" id="description{{ $pacu->id }}" name="description" rows="3"></textarea>
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
                                        @forelse ($pacu->visits as $visit)
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
    <script>
        $(document).ready(function() {
            $('#branch').on('change', function() {
                var branchId = $(this).val();
                if (branchId !== '') {
                    $.ajax({
                        url: '/get_departments/' + branchId,
                        type: 'GET',
                        success: function(response) {

                            $('#department').html(response);
                        }
                    })
                }
            });

            $('#department').on('change', function() {
                var departmentId = $(this).val();
                if (departmentId !== '') {
                    $.ajax({
                        url: '/get_doctors/' + departmentId,
                        type: 'GET',
                        success: function(response) {

                            $('#doctor_id').html(response);
                        }
                    })
                }
            });
        })
    </script>
@endsection
