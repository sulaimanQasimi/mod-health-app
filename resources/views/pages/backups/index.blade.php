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
                                {{ __('Backup Management') }}
                            </h5>
                            <div>
                                <a href="{{ route('backups.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i>
                                    {{ __('Create Backup') }}
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
                                    <span>{{ __('Total Backups') }}</span>
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
                                    <span>{{ __('Total Size') }}</span>
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
                                    <span>{{ __('Latest Backup') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h6 class="mb-0 me-2" style="font-size: small;">
                                            {{ $backups->first() ? $backups->first()->date->format('M d, Y H:i') : __('No backups') }}
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
                                    <span>{{ __('Oldest Backup') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h6 class="mb-0 me-2" style="font-size: small;">
                                            {{ $backups->last() ? $backups->last()->date->format('M d, Y H:i') : __('No backups') }}
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
                                {{ __('Backup Files') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($backups->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Filename') }}</th>
                                                <th>{{ __('Date Created') }}</th>
                                                <th>{{ __('Size') }}</th>
                                                <th>{{ __('Actions') }}</th>
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
                                                                    <i class="bx bx-show me-1"></i> {{ __('View Details') }}
                                                                </a>
                                                                <a class="dropdown-item" href="{{ route('backups.download', $backup->filename) }}">
                                                                    <i class="bx bx-download me-1"></i> {{ __('Download') }}
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="#" 
                                                                   onclick="confirmDelete('{{ route('backups.destroy', $backup->filename) }}', '{{ $backup->filename }}')">
                                                                    <i class="bx bx-trash me-1"></i> {{ __('Delete') }}
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
                                    <h5 class="text-muted">{{ __('No backups found') }}</h5>
                                    <p class="text-muted">{{ __('Create your first backup to get started.') }}</p>
                                    <a href="{{ route('backups.create') }}" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i>
                                        {{ __('Create Backup') }}
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
                    <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete this backup?') }}</p>
                    <p class="text-muted small" id="backupName"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            {{ __('Delete') }}
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
</script>
@endsection
