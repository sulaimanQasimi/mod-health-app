@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Page Header -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-archive me-2"></i>
                                    {{ localize('global.backup_details') }}
                                </h5>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('backups.index') }}">{{ localize('global.backups') }}</a>
                                        </li>
                                        <li class="breadcrumb-item active">{{ $backup->filename }}</li>
                                    </ol>
                                </nav>
                            </div>
                            <div>
                                <a href="{{ route('backups.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="bx bx-arrow-back me-1"></i>
                                    {{ localize('global.back_to_list') }}
                                </a>
                                <a href="{{ route('backups.download', $backup->filename) }}" class="btn btn-primary">
                                    <i class="bx bx-download me-1"></i>
                                    {{ localize('global.download') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup Information -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-info-circle me-2"></i>
                                {{ localize('global.basic_information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ localize('global.filename') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="text-muted">{{ $backup->filename }}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ localize('global.date_created') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="text-muted">{{ $backup->date->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ localize('global.size') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="badge bg-label-info">
                                        {{ number_format($backup->size / 1024 / 1024, 2) }} MB
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ localize('global.age') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="text-muted">{{ $backup->date->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-cog me-2"></i>
                                {{ localize('global.actions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('backups.download', $backup->filename) }}" class="btn btn-primary">
                                    <i class="bx bx-download me-2"></i>
                                    {{ localize('global.download_backup') }}
                                </a>
                                <button type="button" class="btn btn-outline-info" onclick="showBackupInfo()">
                                    <i class="bx bx-info-circle me-2"></i>
                                    {{ localize('global.show_technical_details') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="confirmDelete('{{ route('backups.destroy', $backup->filename) }}', '{{ $backup->filename }}')">
                                    <i class="bx bx-trash me-2"></i>
                                    {{ localize('global.delete_backup') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Details (Hidden by default) -->
            <div class="row" id="technicalDetails" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-code-alt me-2"></i>
                                {{ localize('global.technical_details') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>{{ localize('global.file_information') }}</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>{{ localize('global.full_path') }}:</strong> {{ $backup->path }}</li>
                                        <li><strong>{{ localize('global.disk') }}:</strong> {{ $backup->disk }}</li>
                                        <li><strong>{{ localize('global.size_in_bytes') }}:</strong> {{ number_format($backup->size) }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>{{ localize('global.date_information') }}</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>{{ localize('global.created') }}:</strong> {{ $backup->date->toDateTimeString() }}</li>
                                        <li><strong>{{ localize('global.timezone') }}:</strong> {{ $backup->date->timezone->getName() }}</li>
                                        <li><strong>{{ localize('global.timestamp') }}:</strong> {{ $backup->date->timestamp }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup Usage Tips -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-bulb me-2"></i>
                                {{ localize('global.backup_tips') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">{{ localize('global.important_notes') }}</h6>
                                <ul class="mb-0">
                                    <li>{{ localize('global.backups_contain_sensitive_data') }}</li>
                                    <li>{{ localize('global.regular_backups_essential') }}</li>
                                    <li>{{ localize('global.test_backups_regularly') }}</li>
                                    <li>{{ localize('global.keep_backups_multiple_locations') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.confirm_delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ localize('global.are_you_sure_delete_backup') }}</p>
                    <p class="text-muted small" id="backupName"></p>
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ localize('global.action_cannot_be_undone') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ localize('global.cancel') }}
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            {{ localize('global.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function confirmDelete(url, filename) {
        document.getElementById('backupName').textContent = filename;
        document.getElementById('deleteForm').action = url;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function showBackupInfo() {
        const details = document.getElementById('technicalDetails');
        const btn = document.querySelector('button[onclick="showBackupInfo()"]');
        
        if (details.style.display === 'none') {
            details.style.display = 'block';
            details.style.animation = 'slideDown 0.3s ease-out';
            btn.innerHTML = '<i class="bx bx-chevron-up me-2"></i>{{ localize("global.hide_technical_details") }}';
        } else {
            details.style.display = 'none';
            btn.innerHTML = '<i class="bx bx-chevron-down me-2"></i>{{ localize("global.show_technical_details") }}';
        }
    }

    // Enhanced UI features
    $(document).ready(function() {
        // Add smooth animations to cards
        $('.card').each(function(index) {
            $(this).css('animation-delay', (index * 0.1) + 's');
            $(this).addClass('fade-in');
        });

        // Add hover effects to buttons
        $('.btn').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );

        // Add loading state for download button
        $('a[href*="download"]').on('click', function() {
            const btn = $(this);
            const originalText = btn.html();
            
            btn.html('<i class="bx bx-loader-alt bx-spin me-1"></i>Downloading...');
            
            // Reset after 2 seconds
            setTimeout(function() {
                btn.html(originalText);
            }, 2000);
        });
    });
</script>

<style>
    .fade-in {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .alert {
        border-left: 4px solid;
        animation: slideInLeft 0.5s ease-out;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: scale(1.05);
    }
</style>
@endsection
