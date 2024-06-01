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


                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.dosage') }}</th>
                                    <th>{{ localize('global.frequency') }}</th>
                                    <th>{{ localize('global.amount') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $descriptions = is_array($prescription->description)
                                        ? $prescription->description
                                        : json_decode($prescription->description, true);
                                    $dosages = is_array($prescription->dosage)
                                        ? $prescription->dosage
                                        : json_decode($prescription->dosage, true);
                                    $frequencies = is_array($prescription->frequency)
                                        ? $prescription->frequency
                                        : json_decode($prescription->frequency, true);
                                    $amounts = is_array($prescription->amount)
                                        ? $prescription->amount
                                        : json_decode($prescription->amount, true);
                                    $types = is_array($prescription->type)
                                        ? $prescription->type
                                        : json_decode($prescription->type, true);
                                    $statuses = is_array($prescription->is_delivered)
                                        ? $prescription->is_delivered
                                        : json_decode($prescription->is_delivered, true);
                                    // dd($statuses);
                                @endphp
                                @foreach ($descriptions as $key => $description)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $types[$key] }}</td>
                                        <td>{{ $description }}</td>
                                        <td>{{ $dosages[$key] }}</td>
                                        <td>{{ $frequencies[$key] }}</td>
                                        <td>{{ $amounts[$key] }}</td>
                                        <td>
                                            <a href="" class="type-button" data-type="{{ $statuses[$key] }}"><i
                                                    class="{{ $statuses[$key] == 0 ? 'bx bx-x-circle text-danger' : 'bx bx-check-circle text-success' }}"></i></a>
                                            <input type="hidden" name="is_delivered[]" value="{{ $statuses[$key] }}">
                                        </td>
                                    </tr>
                                @endforeach
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
