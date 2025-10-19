@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ localize('global.filters') }}</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('lab_tests.index') }}" class="row g-3" id="lab-filter-form">
                    <div class="col-md-3">
                        <label for="search" class="form-label">{{ localize('global.search') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" 
                                value="{{ request('search') }}" placeholder="{{ localize('global.search_by_patient_name') }}">
                            @if(request('search'))
                                <button type="button" class="btn btn-outline-danger" id="clearSearch" title="{{ localize('global.clear_search') }}">
                                    <i class="bx bx-x"></i>
                                </button>
                            @endif
                            <button type="submit" class="btn btn-outline-primary" title="{{ localize('global.search') }}">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="lab_type_id" class="form-label">{{ localize('global.lab_type') }}</label>
                        <select class="form-select select2" id="lab_type_id" name="lab_type_id">
                            <option value="">{{ localize('global.all') }}</option>
                            @foreach($labTypes as $labType)
                                <option value="{{ $labType->id }}" {{ request('lab_type_id') == $labType->id ? 'selected' : '' }}>
                                    {{ $labType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">{{ localize('global.status') }}</label>
                        <select class="form-select select2" id="status" name="status">
                            <option value="">{{ localize('global.all') }}</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ localize('global.not_completed') }}</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ localize('global.completed') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">{{ localize('global.date_from') }}</label>
                        <input type="text" class="form-control datepicker_dari pdp-el" id="date_from" name="date_from" 
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">{{ localize('global.date_to') }}</label>
                        <input type="text" class="form-control datepicker_dari pdp-el" id="date_to" name="date_to" 
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.new_tests') }}</h5>
                    {{-- <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('lab_types.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                    </div> --}}
                </div>

                <div class="card-body">
                    <!-- Loading overlay -->
                    <div id="lab-loading-overlay" class="loading-overlay" style="display: none;">
                        <div class="loading-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{ localize('global.loading') }}...</span>
                            </div>
                            <p class="mt-2">{{ localize('global.loading') }}...</p>
                        </div>
                    </div>

                    <!-- Lab tests container for AJAX updates -->
                    <div id="lab-tests-container">
                        @include('pages.labs.partials.lab-table', ['labs' => $labs])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    border-radius: 0.375rem;
}

.loading-spinner {
    text-align: center;
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.pagination .page-link {
    transition: all 0.2s ease;
}

.pagination .page-link:hover {
    transform: translateY(-1px);
}

.pagination .page-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle filter form submission with AJAX
    $('#lab-filter-form').on('submit', function(e) {
        e.preventDefault();
        
        showLoadingOverlay();
        
        var formData = $(this).serialize();
        var url = '{{ route("lab_tests.index") }}?' + formData;
        
        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#lab-tests-container').html(response.html);
                $('#lab-tests-container').addClass('fade-in');
                
                // Update URL
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('{{ localize("global.error_loading_data") }}');
            },
            complete: function() {
                hideLoadingOverlay();
            }
        });
    });

    // Auto-submit on select change
    $('select[name="lab_type_id"], select[name="status"]').change(function() {
        $('#lab-filter-form').submit();
    });

    // Clear search functionality
    $(document).on('click', '#clearSearch', function() {
        $('input[name="search"]').val('');
        $('#lab-filter-form').submit();
    });

    // Initialize Select2
    $('.select2').select2({
        placeholder: '{{ localize("global.select") }}',
        allowClear: true,
        width: '100%'
    });

    // Initialize Persian datepicker
    $('.datepicker_dari').persianDatepicker({
        format: 'YYYY/MM/DD',
        observer: true,
    });

    // Handle pagination clicks with AJAX
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        
        var url = $(this).attr('href');
        if (!url) return;
        
        // Show loading overlay
        showLoadingOverlay();
        
        // Disable pagination links
        $('.pagination .page-link').addClass('disabled');
        
        // Make AJAX request
        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Update the content
                $('#lab-tests-container').html(response.html);
                
                // Add fade-in animation
                $('#lab-tests-container').addClass('fade-in');
                
                // Update URL without page reload
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
                
                // Scroll to top of table
                $('html, body').animate({
                    scrollTop: $('#lab-tests-container').offset().top - 100
                }, 300);
                
                // Re-enable pagination links
                $('.pagination .page-link').removeClass('disabled');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                
                // Show error message
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert('{{ localize("global.error_occurred") }}: ' + xhr.responseJSON.message);
                } else {
                    alert('{{ localize("global.error_loading_data") }}');
                }
                
                // Re-enable pagination links
                $('.pagination .page-link').removeClass('disabled');
            },
            complete: function() {
                // Hide loading overlay
                hideLoadingOverlay();
            }
        });
    });
    
    function showLoadingOverlay() {
        $('#lab-loading-overlay').show();
    }
    
    function hideLoadingOverlay() {
        $('#lab-loading-overlay').hide();
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        if (event.state) {
            location.reload();
        }
    });
});
</script>
@endpush
