@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-4">
                <div class="card-header">{{ localize('global.please_scan_test') }}</div>

                <div class="card-body">
                    <form id="scanForm" action="{{ route('laboratory.scan.ref') }}" method="GET">
                        <div class="mb-3">
                            <label for="ref_no" class="form-label">{{ localize('global.ref_number') }}</label>
                            <input id="ref_no" type="text" name="ref_no" class="form-control" placeholder="Scan or enter reference number" required autofocus>
                        </div>
                    </form>
                    @if(session('error'))
                    <div class="alert alert-danger mt-3">
                        {{ session('error') }}
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script>
    // Check if the form has already been submitted
    if (!localStorage.getItem('formSubmitted')) {
        // Set a flag to indicate that the form has been submitted
        localStorage.setItem('formSubmitted', true);

        // Function to handle the reference number scanning and form submission
        function scanRefNumber(data) {
            $('#ref_no').val(data); // Set the reference number to the input field
            $('#scanForm').submit(); // Submit the form
        }

        // Simulating reference number scan and form submission
        $(document).ready(function() {
            // Simulating the reference number data
            var refNumberData = 'Your reference number here';

            // Calling the scanRefNumber function with the reference number data
            scanRefNumber(refNumberData);
        });
    }
</script>
@endsection
