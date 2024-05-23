@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-4">
                <div class="card-header">{{ localize('global.please_scan_card') }}</div>

                <div class="card-body">
                    <form id="scanForm" action="{{ route('scanQRCode') }}" method="GET">
                        <div class="mb-3">
                            <label for="qrCodeData" class="form-label">{{ localize('global.qr_code_data') }}</label>
                            <input id="qrCodeData" type="text" name="qrCodeData" class="form-control" placeholder="Scan QR code" required autofocus>
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

        // Function to handle the QR code scanning and form submission
        function scanQRCode(data) {
            $('#qrCodeData').val(data); // Set the QR code data to the input field
            $('#scanForm').submit(); // Submit the form
        }

        // Simulating QR code scan and form submission
        $(document).ready(function() {
            // Simulating the QR code data
            var qrCodeData = 'Your QR code data here';

            // Calling the scanQRCode function with the QR code data
            scanQRCode(qrCodeData);
        });
    }
</script>
@endsection
