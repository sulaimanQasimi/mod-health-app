<div class="report-container">
    <!-- Export Controls Section -->
    <div class="export-section mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-primary fw-semibold">
                            <i class="fas fa-download me-2"></i>{{ localize('global.export_report') }}
                        </h5>
                        <p class="text-muted mb-0 small">{{ localize('global.select_export_format') }}</p>
                    </div>
                    <form action="{{ route('patients.export-report') }}" method="POST" class="d-flex gap-2">
                        {{ csrf_field() }}
                        <input type="hidden" name="data" value="{{ $items->pluck('id') }}">
                        
                        <button type="submit" name="type" value="excel" class="btn btn-success btn-lg px-4">
                            <i class="fas fa-file-excel me-2"></i>
                            <span class="fw-medium">Excel</span>
                        </button>
                        <button type="submit" name="type" value="pdf" class="btn btn-danger btn-lg px-4">
                            <i class="fas fa-file-pdf me-2"></i>
                            <span class="fw-medium">PDF</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="results-summary mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card bg-primary text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-users fa-2x me-3"></i>
                            <div>
                                <h3 class="mb-0 fw-bold">{{ $items->count() }}</h3>
                                <small class="opacity-75">{{ localize('global.total_patients') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-male fa-2x me-3"></i>
                            <div>
                                <h3 class="mb-0 fw-bold">{{ $items->where('gender', '0')->count() }}</h3>
                                <small class="opacity-75">{{ localize('global.male') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-female fa-2x me-3"></i>
                            <div>
                                <h3 class="mb-0 fw-bold">{{ $items->where('gender', '1')->count() }}</h3>
                                <small class="opacity-75">{{ localize('global.female') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-shield-alt fa-2x me-3"></i>
                            <div>
                                <h3 class="mb-0 fw-bold">{{ $items->where('job_category', '0')->count() }}</h3>
                                <small class="opacity-75">{{ localize('global.military') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="table-section">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white fw-semibold">
                        <i class="fas fa-table me-2 text-white"></i>{{ localize('global.patient_records') }}
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-primary rounded-pill fw-medium">{{ $items->count() }} {{ localize('global.records') }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="print_excel_table">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 text-center" style="width: 60px;">
                                    <i class="fas fa-hashtag text-muted"></i>
                                </th>
                                <th class="border-0 fw-semibold text-gray-700">{{ localize('global.patient_name') }}</th>
                                <th class="border-0 fw-semibold text-gray-700">{{ localize('global.nid') }}</th>
                                <th class="border-0 fw-semibold text-gray-700">{{ localize('global.id_card') }}</th>
                                <th class="border-0 fw-semibold text-gray-700">{{ localize('global.referral_name') }}</th>
                                <th class="border-0 fw-semibold text-gray-700 text-center">{{ localize('global.age') }}</th>
                                <th class="border-0 fw-semibold text-gray-700 text-center">{{ localize('global.gender') }}</th>
                                <th class="border-0 fw-semibold text-gray-700 text-center">{{ localize('global.job_category') }}</th>
                                <th class="border-0 fw-semibold text-gray-700 text-center">{{ localize('global.disease_type') }}</th>
                                <th class="border-0 fw-semibold text-gray-700">{{ localize('global.referred_by') }}</th>
                                <th class="border-0 fw-semibold text-gray-700">{{ localize('global.province') }}</th>
                                <th class="border-0 fw-semibold text-gray-700">{{ localize('global.district') }}</th>
                                <th class="border-0 fw-semibold text-gray-700">{{ localize('global.registered_date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr class="border-bottom">
                                    <td class="text-center text-muted fw-medium">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user text-white small"></i>
                                            </div>
                                            <span class="fw-medium">{{ $item->patient_name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item->nid }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item->id_card }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $item->referral_name }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">{{ $item->age }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($item->gender == '0')
                                            <span class="badge bg-primary rounded-pill">
                                                <i class="fas fa-male me-1"></i>{{ localize('global.male') }}
                                            </span>
                                        @else
                                            <span class="badge bg-pink rounded-pill">
                                                <i class="fas fa-female me-1"></i>{{ localize('global.female') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($item->job_category == '0')
                                            <span class="badge bg-warning rounded-pill">
                                                <i class="fas fa-shield-alt me-1"></i>{{ localize('global.military') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">
                                                <i class="fas fa-user-tie me-1"></i>{{ localize('global.civilian') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($item->type == '0')
                                            <span class="badge bg-success rounded-pill">
                                                <i class="fas fa-star me-1"></i>{{ localize('global.mod') }}
                                            </span>
                                        @elseif($item->type == '1')
                                            <span class="badge bg-info rounded-pill">
                                                <i class="fas fa-heart me-1"></i>{{ localize('global.recipient') }}
                                            </span>
                                        @else
                                            <span class="badge bg-purple rounded-pill">
                                                <i class="fas fa-home me-1"></i>{{ localize('global.family') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $item->referred_by }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item->province_name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item->district_name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ verta($item->registration_date)->format('Y/m/d') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted mb-2">{{ localize('global.no_records_found') }}</h5>
                                            <p class="text-muted mb-0">{{ localize('global.try_adjusting_your_search_criteria') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.report-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.export-section .card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.export-section .card-body {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.export-section h5 {
    color: white !important;
}

.export-section .btn {
    transition: all 0.3s ease;
    border: none;
    font-weight: 500;
}

.export-section .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.results-summary .card {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.results-summary .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.table-section .card {
    border-radius: 12px;
    overflow: hidden;
}

.table-section .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    position: relative;
    overflow: hidden;
}

.table-section .card-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.table {
    margin-bottom: 0;
}

.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
    border: none;
    color: #4a5568;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    position: relative;
    transition: all 0.3s ease;
}

.table thead th:hover {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.text-gray-700 {
    color: #4a5568 !important;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
}

.table tbody td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border: none;
    border-bottom: 1px solid #f1f3f4;
}

.avatar {
    width: 32px;
    height: 32px;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.badge.bg-pink {
    background-color: #e91e63 !important;
}

.badge.bg-purple {
    background-color: #9c27b0 !important;
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state i {
    opacity: 0.5;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .export-section .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
    
    .results-summary .row {
        margin: 0;
    }
    
    .results-summary .col-md-3 {
        padding: 0.5rem;
    }
}

/* Print styles */
@media print {
    .export-section,
    .results-summary {
        display: none;
    }
    
    .table-section .card {
        box-shadow: none;
        border: 1px solid #dee2e6;
    }
}
</style>
