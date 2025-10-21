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
                            <h5 class="card-title mb-0">
                                <i class="bx bx-archive me-2"></i>
                                {{ localize('global.backup_management') }}
                            </h5>
                            <div>
                                <a href="{{ route('backups.create') }}" class="btn btn-primary" id="createBackupBtn">
                                    <i class="bx bx-plus me-1"></i>
                                    {{ localize('global.create_backup') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.total_backups') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">{{ $backups->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded p-2">
                                    <i class="bx bx-archive bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-success">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.total_size') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2" style="font-size: large;">
                                            {{ $backups->sum('size') > 0 ? 
                                                number_format($backups->sum('size') / 1024 / 1024, 2) . ' MB' : 
                                                '0 B' }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-success rounded p-2">
                                    <i class="bx bx-data bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-info">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.latest_backup') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h6 class="mb-0 me-2" style="font-size: small;">
                                            {{ $backups->first() ? $backups->first()->date->format('M d, Y H:i') : localize('global.no_backups') }}
                                        </h6>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded p-2">
                                    <i class="bx bx-time bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.oldest_backup') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h6 class="mb-0 me-2" style="font-size: small;">
                                            {{ $backups->last() ? $backups->last()->date->format('M d, Y H:i') : localize('global.no_backups') }}
                                        </h6>
                                    </div>
                                </div>
                                <span class="badge bg-warning rounded p-2">
                                    <i class="bx bx-history bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backups Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>
                                {{ localize('global.backup_files') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($backups->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{ localize('global.filename') }}</th>
                                                <th>{{ localize('global.date_created') }}</th>
                                                <th>{{ localize('global.size') }}</th>
                                                <th>{{ localize('global.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($backups as $backup)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-file-archive me-2 text-primary"></i>
                                                            <span class="fw-medium">{{ $backup->filename }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">
                                                            {{ $backup->date->format('M d, Y H:i:s') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-label-info">
                                                            {{ number_format($backup->size / 1024 / 1024, 2) }} MB
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item" href="{{ route('backups.show', $backup->filename) }}">
                                                                    <i class="bx bx-show me-1"></i> {{ localize('global.view_details') }}
                                                                </a>
                                                                <a class="dropdown-item" href="{{ route('backups.download', $backup->filename) }}">
                                                                    <i class="bx bx-download me-1"></i> {{ localize('global.download') }}
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="#" 
                                                                   onclick="confirmDelete('{{ route('backups.destroy', $backup->filename) }}', '{{ $backup->filename }}')">
                                                                    <i class="bx bx-trash me-1"></i> {{ localize('global.delete') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bx bx-archive bx-lg text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ localize('global.no_backups_found') }}</h5>
                                    <p class="text-muted">{{ localize('global.create_first_backup') }}</p>
                                    <a href="{{ route('backups.create') }}" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i>
                                        {{ localize('global.create_backup') }}
                                    </a>
                                </div>
                            @endif
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

    // Enhanced UI features
    $(document).ready(function() {
        // Add loading state for backup creation
        $('#createBackupBtn').on('click', function() {
            const btn = $(this);
            const originalText = btn.html();
            
            btn.prop('disabled', true);
            btn.html('<i class="bx bx-loader-alt bx-spin me-1"></i>Creating...');
            
            // Re-enable after 3 seconds (backup is queued)
            setTimeout(function() {
                btn.prop('disabled', false);
                btn.html(originalText);
            }, 3000);
        });

        // Add hover effects to table rows
        $('.table tbody tr').hover(
            function() {
                $(this).addClass('table-active');
            },
            function() {
                $(this).removeClass('table-active');
            }
        );

        // Add smooth animations to stat cards
        $('.card').each(function(index) {
            $(this).css('animation-delay', (index * 0.1) + 's');
            $(this).addClass('fade-in');
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

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
</style>
@endsection
