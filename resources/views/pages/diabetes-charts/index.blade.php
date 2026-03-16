@extends('layouts.master')

@section('title', localize('global.diabetes_chart_management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.diabetes_chart_management') }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('diabetes-charts.print') }}" class="btn btn-info" target="_blank">
                            <i class="fas fa-print"></i> {{ localize('global.print_chart') }}
                        </a>
                        <a href="{{ route('diabetes-charts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ localize('global.add_diabetes_chart') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('diabetes-charts.index') }}" class="row g-3">
                                <div class="col-md-2">
                                    <input type="text" name="search" class="form-control" placeholder="{{ localize('global.search') }}" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="chartable_type" class="form-select">
                                        <option value="">{{ localize('global.all_types') }}</option>
                                        <option value="App\\Models\\UnderReview" {{ request('chartable_type') == 'App\\Models\\UnderReview' ? 'selected' : '' }}>
                                            {{ localize('global.under_review') }}
                                        </option>
                                        <option value="App\\Models\\Hospitalization" {{ request('chartable_type') == 'App\\Models\\Hospitalization' ? 'selected' : '' }}>
                                            {{ localize('global.hospitalization') }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" autocomplete="off" name="start_date" class="form-control datepicker_dari" value="{{ request('start_date') }}" placeholder="{{ localize('global.start_date') }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" autocomplete="off" name="end_date" class="form-control datepicker_dari" value="{{ request('end_date') }}" placeholder="{{ localize('global.end_date') }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <select name="nurse_id" class="form-select">
                                        <option value="">{{ localize('global.all_nurses') }}</option>
                                        @foreach($nurses as $nurse)
                                            <option value="{{ $nurse->id }}" {{ request('nurse_id') == $nurse->id ? 'selected' : '' }}>
                                                {{ $nurse->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="medicine_id" class="form-select">
                                        <option value="">{{ localize('global.all_medicines') }}</option>
                                        @foreach($medicines as $medicine)
                                            <option value="{{ $medicine->id }}" {{ request('medicine_id') == $medicine->id ? 'selected' : '' }}>
                                                {{ $medicine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> {{ localize('global.filter') }}
                                    </button>
                                    <a href="{{ route('diabetes-charts.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> {{ localize('global.clear_filters') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ localize('global.date') }}</th>
                                    <th>{{ localize('global.time') }}</th>
                                    <th>{{ localize('global.rbs') }}</th>
                                    <th>{{ localize('global.fbs') }}</th>
                                    <th>{{ localize('global.insulin_dose') }}</th>
                                    <th>{{ localize('global.unit') }}</th>
                                    <th>{{ localize('global.nurse') }}</th>
                                    <th>{{ localize('global.medicine') }}</th>
                                    <th>{{ localize('global.chartable_type') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($diabetesCharts as $chart)
                                    <tr>
                                        <td>{{ $chart->id }}</td>
                                        <td>
                                            @if($chart->date)
                                                <span class="badge bg-info">{{ $chart->date->format('Y-m-d') }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_set') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($chart->time)
                                                <span class="badge bg-secondary">{{ $chart->formatted_time }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_set') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($chart->rbs)
                                                <span class="badge bg-warning">{{ $chart->rbs }} {{ $chart->unit }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($chart->fbs)
                                                <span class="badge bg-success">{{ $chart->fbs }} {{ $chart->unit }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($chart->insulin_dose)
                                                <span class="badge bg-primary">{{ $chart->insulin_dose }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($chart->unit)
                                                <small>{{ $chart->unit }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($chart->nurse)
                                                <span class="badge bg-info">{{ $chart->nurse->full_name }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($chart->medicine)
                                                <span class="badge bg-secondary">{{ $chart->medicine->name }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($chart->diabetes_chartable_type === 'App\\Models\\UnderReview')
                                                <span class="badge bg-warning">{{ localize('global.under_review') }}</span>
                                            @elseif($chart->diabetes_chartable_type === 'App\\Models\\Hospitalization')
                                                <span class="badge bg-danger">{{ localize('global.hospitalization') }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.unknown') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('diabetes-charts.show', $chart) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="{{ localize('global.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('diabetes-charts.edit', $chart) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="{{ localize('global.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('diabetes-charts.destroy', $chart) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="{{ localize('global.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">{{ localize('global.no_diabetes_charts_found') }}</p>
                                                <a href="{{ route('diabetes-charts.create') }}" class="btn btn-primary">
                                                    {{ localize('global.add_first_diabetes_chart') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($diabetesCharts->hasPages())
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <small class="text-muted">
                                        {{ localize('global.showing') }} 
                                        <strong>{{ $diabetesCharts->firstItem() ?? 0 }}</strong> 
                                        {{ localize('global.to') }} 
                                        <strong>{{ $diabetesCharts->lastItem() ?? 0 }}</strong> 
                                        {{ localize('global.of') }} 
                                        <strong>{{ $diabetesCharts->total() }}</strong> 
                                        {{ localize('global.results') }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    <nav aria-label="Diabetes Charts Pagination">
                                        {{ $diabetesCharts->links('pagination::bootstrap-5') }}
                                    </nav>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-center">
                                    <small class="text-muted">
                                        {{ localize('global.showing') }} 
                                        <strong>{{ $diabetesCharts->count() }}</strong> 
                                        {{ localize('global.results') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-link {
        color: #007bff;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        transition: all 0.15s ease-in-out;
    }
    
    .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #dee2e6;
        z-index: 2;
    }
    
    .page-link:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        z-index: 3;
    }
    
    .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    .page-item:first-child .page-link {
        margin-left: 0;
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }
    
    .page-item:last-child .page-link {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    
    /* Responsive pagination */
    @media (max-width: 576px) {
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .page-link {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Initialize Persian date picker for date inputs
    $(document).ready(function() {
        $('.datepicker_dari').each(function() {
            var $this = $(this);
            
            // Get existing value
            var existingValue = $this.val();
            
            // Initialize Persian date picker
            $this.persianDatepicker({
                formatDate: 'YYYY-MM-DD',
                calendar: {
                    persian: {
                        locale: 'en',
                        showHint: true,
                        leapYearMode: 'algorithmic'
                    }
                },
                checkDate: function(unix) {
                    return true;
                },
                onSelect: function() {
                    // Ensure the value is set correctly
                    $this.trigger('change');
                }
            });
            
            // Set the value if it exists (convert from YYYY-MM-DD format)
            if (existingValue) {
                // The date picker will handle the conversion
                $this.val(existingValue);
            }
        });
    });
</script>
@endpush
