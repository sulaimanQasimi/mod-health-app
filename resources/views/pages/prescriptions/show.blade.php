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
                        <h5 class="mb-0">{{ localize('global.prescription_details') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
                                <span class="text-white"> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">


                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                    <tr>
                                        <td>{{ $prescription->id }}</td>
                                        <td>{{ $prescription->patient->name }}</td>
                                        <td>
                                            @if ($prescription->is_completed == '0')
                                                <span
                                                    class="badge bg-danger">{{ localize('global.not_delivered') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-success">{{ localize('global.delivered') }}</span>
                                            @endif
                                        </td>
                                        <td>


                                            <a href="#" data-bs-toggle="modal" onclick="getPrescriptionItems({{$prescription->id}})"
                                                data-bs-target="#showPrescriptionItemModal"><span><i
                                                        class="bx bx-expand"></i></span></a>
                                        </td>
                                    </tr>
                                
                            </tbody>
                        </table>
                        @if($prescription->is_completed == '0')
                        <div class="d-flex justify-content-center text-center mt-2">
                            <form action="{{ route('prescriptions.changeStatus', $prescription) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_completed" value="1">
                                <button type="submit" class="btn btn-success">
                                    <span><i class="bx bx-check-shield"></i></span>
                                </button>
                            </form>
                        </div>
                        @endif
                        </form>
                        </button>
                    </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.type-button').click(function() {
                var button = $(this);
                var currentType = button.attr('data-type');
                var updatedType = currentType == "0" ? "1" : "0";

                button.attr('data-type', updatedType);
                button.text(updatedType);
                button.next("input[type='hidden']").val(updatedType);

                // Get the prescription ID and key from the button's data attributes
                var prescriptionId = '{{ $prescription->id }}'; // Replace with the actual prescription ID
                var key = button.closest('tr').index();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                // Send an AJAX request to update the status
                $.ajax({
                    url: "{{ url('prescriptions/update-status/') }}/" + prescriptionId + '/' + key,
                    type: 'POST',
                    data: {
                        updatedType: updatedType,
                    },


                    success: function(response) {
                        console.log('Status updated successfully');
                    },
                    error: function(xhr, status, error) {
                        console.error('An error occurred while updating the status');
                    }
                });
            });
        });
    </script>
@endsection
