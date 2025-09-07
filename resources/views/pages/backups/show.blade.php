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
                                    {{ __('Backup Details') }}
                                </h5>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('backups.index') }}">{{ __('Backups') }}</a>
                                        </li>
                                        <li class="breadcrumb-item active">{{ $backup->filename }}</li>
                                    </ol>
                                </nav>
                            </div>
                            <div>
                                <a href="{{ route('backups.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="bx bx-arrow-back me-1"></i>
                                    {{ __('Back to List') }}
                                </a>
                                <a href="{{ route('backups.download', $backup->filename) }}" class="btn btn-primary">
                                    <i class="bx bx-download me-1"></i>
                                    {{ __('Download') }}
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
                                {{ __('Basic Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('Filename') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="text-muted">{{ $backup->filename }}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('Date Created') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="text-muted">{{ $backup->date->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('Size') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="badge bg-label-info">
                                        {{ number_format($backup->size / 1024 / 1024, 2) }} MB
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('Age') }}:</strong>
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
                                {{ __('Actions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('backups.download', $backup->filename) }}" class="btn btn-primary">
                                    <i class="bx bx-download me-2"></i>
                                    {{ __('Download Backup') }}
                                </a>
                                <button type="button" class="btn btn-outline-info" onclick="showBackupInfo()">
                                    <i class="bx bx-info-circle me-2"></i>
                                    {{ __('Show Technical Details') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="confirmDelete('{{ route('backups.destroy', $backup->filename) }}', '{{ $backup->filename }}')">
                                    <i class="bx bx-trash me-2"></i>
                                    {{ __('Delete Backup') }}
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
                                {{ __('Technical Details') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>{{ __('File Information') }}</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>{{ __('Full Path') }}:</strong> {{ $backup->path }}</li>
                                        <li><strong>{{ __('Disk') }}:</strong> {{ $backup->disk }}</li>
                                        <li><strong>{{ __('Size in Bytes') }}:</strong> {{ number_format($backup->size) }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>{{ __('Date Information') }}</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>{{ __('Created') }}:</strong> {{ $backup->date->toDateTimeString() }}</li>
                                        <li><strong>{{ __('Timezone') }}:</strong> {{ $backup->date->timezone->getName() }}</li>
                                        <li><strong>{{ __('Timestamp') }}:</strong> {{ $backup->date->timestamp }}</li>
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
                                {{ __('Backup Tips') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">{{ __('Important Notes') }}</h6>
                                <ul class="mb-0">
                                    <li>{{ __('Backups contain sensitive data and should be stored securely.') }}</li>
                                    <li>{{ __('Regular backups are essential for data protection.') }}</li>
                                    <li>{{ __('Test your backups regularly to ensure they can be restored.') }}</li>
                                    <li>{{ __('Keep backups in multiple locations for redundancy.') }}</li>
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
                    <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete this backup?') }}</p>
                    <p class="text-muted small" id="backupName"></p>
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ __('This action cannot be undone.') }}
                    </div>
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

    function showBackupInfo() {
        const details = document.getElementById('technicalDetails');
        if (details.style.display === 'none') {
            details.style.display = 'block';
        } else {
            details.style.display = 'none';
        }
    }
</script>
@endsection
