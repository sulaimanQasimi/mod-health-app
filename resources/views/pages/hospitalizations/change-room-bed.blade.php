@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-transfer me-2 text-primary"></i>
                    {{ localize('global.change_room_and_bed') ?: 'Change Room and Bed' }}
                </h5>
                <a href="{{ route('hospitalizations.show', $hospitalization->id) }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                </a>
            </div>

            <div class="card-body">
                {{-- Current Information --}}
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading mb-2">
                        <i class="bx bx-info-circle me-2"></i>{{ localize('global.current_information') ?: 'Current Information' }}
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ localize('global.patient') }}:</strong> {{ $hospitalization->patient->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ localize('global.current_room') }}:</strong> 
                            <span class="badge bg-label-info">{{ $hospitalization->room->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ localize('global.current_bed') }}:</strong> 
                            <span class="badge bg-label-success">{{ $hospitalization->bed->number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('hospitalizations.updateRoomBed', $hospitalization->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="room_id" class="form-label fw-semibold">
                                <i class="bx bx-building me-1 text-primary"></i>
                                {{ localize('global.select_room') ?: 'Select Room' }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" name="room_id" id="room_id" required>
                                <option value="">{{ localize('global.select') }}...</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" 
                                        {{ $hospitalization->room_id == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bed_id" class="form-label fw-semibold">
                                <i class="bx bx-bed me-1 text-primary"></i>
                                {{ localize('global.select_bed') ?: 'Select Bed' }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" name="bed_id" id="bed_id" required>
                                <option value="">{{ localize('global.select') }}...</option>
                                @if($hospitalization->room_id)
                                    {{-- Beds will be loaded via AJAX --}}
                                @endif
                            </select>
                            @error('bed_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>{{ localize('global.note') ?: 'Note' }}:</strong> 
                        {{ localize('global.changing_room_bed_note') ?: 'Changing the room and bed will automatically free the current bed and occupy the new bed.' }}
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('hospitalizations.show', $hospitalization->id) }}" class="btn btn-secondary">
                            <i class="bx bx-x me-1"></i>{{ localize('global.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i>{{ localize('global.update') ?: 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-css')
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-right: 20px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 10px;
        }
    </style>
@endpush

@push('custom-js')
    <script>
        $(document).ready(function() {
            var $bedSelect = $('#bed_id');
            var currentBedId = {{ $hospitalization->bed_id ?? 'null' }};
            var currentRoomId = $('#room_id').val();
            
            // Function to load beds via AJAX
            function loadBeds(roomId, bedId) {
                if (roomId && roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        data: {
                            bed_id: bedId || currentBedId
                        },
                        success: function (response) {
                            // Destroy existing Select2 instance
                            if (typeof $.fn.select2 !== 'undefined' && $bedSelect.hasClass('select2-hidden-accessible')) {
                                $bedSelect.select2('destroy');
                            }
                            
                            // Update bed options
                            $bedSelect.html(response);
                            
                            // Reinitialize Select2 for bed dropdown
                            if (typeof $.fn.select2 !== 'undefined') {
                                $bedSelect.select2({
                                    width: '100%',
                                    placeholder: '{{ localize("global.select") }}...',
                                    allowClear: true,
                                    language: {
                                        noResults: function() {
                                            return '{{ localize("global.no_results_found") ?: "No results found" }}';
                                        }
                                    }
                                });
                            }
                            
                            // Ensure the bed is selected if bedId was provided
                            if (bedId || currentBedId) {
                                var bedToSelect = bedId || currentBedId;
                                $bedSelect.val(bedToSelect).trigger('change');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading beds:', error);
                            // Destroy existing Select2 instance
                            if (typeof $.fn.select2 !== 'undefined' && $bedSelect.hasClass('select2-hidden-accessible')) {
                                $bedSelect.select2('destroy');
                            }
                            $bedSelect.html('<option value="">{{ localize("global.select") }}</option>');
                            if (typeof $.fn.select2 !== 'undefined') {
                                $bedSelect.select2({
                                    width: '100%',
                                    placeholder: '{{ localize("global.select") }}...',
                                    allowClear: true
                                });
                            }
                        }
                    });
                } else {
                    // Clear bed dropdown if no room selected
                    if (typeof $.fn.select2 !== 'undefined' && $bedSelect.hasClass('select2-hidden-accessible')) {
                        $bedSelect.select2('destroy');
                    }
                    $bedSelect.html('<option value="">{{ localize("global.select") }}</option>');
                    if (typeof $.fn.select2 !== 'undefined') {
                        $bedSelect.select2({
                            width: '100%',
                            placeholder: '{{ localize("global.select") }}...',
                            allowClear: true
                        });
                    }
                }
            }
            
            // Initialize Select2 for room dropdown
            if (typeof $.fn.select2 !== 'undefined') {
                $('#room_id').select2({
                    width: '100%',
                    placeholder: '{{ localize("global.select") }}...',
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return '{{ localize("global.no_results_found") ?: "No results found" }}';
                        }
                    }
                });
            }
            
            // Load beds on page load if room is selected
            if (currentRoomId && currentRoomId !== '') {
                loadBeds(currentRoomId, currentBedId);
            } else {
                // Initialize Select2 even if no room is selected
                if (typeof $.fn.select2 !== 'undefined') {
                    $bedSelect.select2({
                        width: '100%',
                        placeholder: '{{ localize("global.select") }}...',
                        allowClear: true
                    });
                }
            }
            
            // Handle room change and update bed dropdown with Select2
            $('#room_id').on('change', function () {
                var roomId = $(this).val();
                currentBedId = null; // Reset bed selection when room changes
                loadBeds(roomId, null);
            });
        });
    </script>
@endpush
