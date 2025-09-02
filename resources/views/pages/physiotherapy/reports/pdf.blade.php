<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physiotherapy Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container-xxl {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            border: 1px solid #d9dee3;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            background: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.45);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #d9dee3;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .card-header h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #566a7f;
        }
        .card-body {
            padding: 1.5rem;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 0.75rem;
        }
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }
        .mb-0 {
            margin-bottom: 0 !important;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            border-collapse: collapse;
        }
        .table-borderless {
            border: none;
        }
        .table-borderless td {
            padding: 0.5rem 0;
            border: none;
            vertical-align: top;
        }
        .table-borderless td:first-child {
            font-weight: 600;
            color: #566a7f;
            width: 40%;
        }
        .table-bordered {
            border: 1px solid #d9dee3;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #d9dee3;
            padding: 0.75rem;
            vertical-align: middle;
        }
        .table-bordered th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #566a7f;
            text-align: left;
        }
        .table-responsive {
            overflow-x: auto;
        }
        h6 {
            font-size: 1rem;
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e7e7ff;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            text-align: center;
            box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.45);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
        }
        .badge-success {
            background-color: #71dd37;
            color: #fff;
        }
        .badge-warning {
            background-color: #ffab00;
            color: #fff;
        }
        .badge-secondary {
            background-color: #8592a3;
            color: #fff;
        }
        .badge-primary {
            background-color: #696cff;
            color: #fff;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #a1acb8 !important;
        }
        .progress {
            height: 1.5rem;
            background-color: #e9ecef;
            border-radius: 0.375rem;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #696cff 0%, #8592a3 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            border: 1px solid transparent;
            text-decoration: none;
        }
        .btn-success {
            background-color: #71dd37;
            border-color: #71dd37;
            color: #fff;
        }
        .btn-danger {
            background-color: #ff3e1d;
            border-color: #ff3e1d;
            color: #fff;
        }
        .btn-secondary {
            background-color: #8592a3;
            border-color: #8592a3;
            color: #fff;
        }
        .me-2 {
            margin-right: 0.5rem !important;
        }
        .d-flex {
            display: flex;
        }
        .justify-content-between {
            justify-content: space-between;
        }
        .align-items-center {
            align-items: center;
        }
        .d-inline {
            display: inline;
        }
        .d-inline-block {
            display: inline-block;
        }
        .bx {
            font-family: 'boxicons';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            vertical-align: middle;
            display: inline-block;
        }
        .bx-file-pdf::before {
            content: "📄";
        }
        .bx-file::before {
            content: "📊";
        }
        .bx-arrow-back::before {
            content: "←";
        }
        @media print {
            .card { box-shadow: none; }
            .section { page-break-inside: avoid; }
            body { margin: 10px; }
        }
    </style>
</head>
<body>
    <div class="container-xxl">
        <div class="content-wrapper">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Physiotherapy Report Result</h5>
                        <div>
                            <span class="btn btn-danger me-2">
                                📄 Export PDF
                            </span>
                            <span class="btn btn-success me-2">
                                📊 Export Excel
                            </span>
                            <span class="btn btn-secondary">
                                ← Back
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Report Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Start Date:</strong></td>
                                        <td>{{ verta($startDate)->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>End Date:</strong></td>
                                        <td>{{ verta($endDate)->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Generated At:</strong></td>
                                        <td>{{ now()->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Summary Report -->
                        <div class="mb-4">
                            <h6>Summary Report</h6>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-number">{{ $data['summary']['total_procedures'] ?? 0 }}</div>
                                    <div class="stat-label">Total Procedures</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number">{{ $data['summary']['completed_procedures'] ?? 0 }}</div>
                                    <div class="stat-label">Completed</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number">{{ $data['summary']['in_progress_procedures'] ?? 0 }}</div>
                                    <div class="stat-label">In Progress</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number">{{ $data['summary']['pending_procedures'] ?? 0 }}</div>
                                    <div class="stat-label">Pending</div>
                                </div>
                            </div>

                            <table class="table table-bordered">
                                <tr>
                                    <th>Metric</th>
                                    <th>Value</th>
                                </tr>
                                <tr>
                                    <td>Completion Rate</td>
                                    <td>{{ number_format($data['summary']['completion_rate'] ?? 0, 1) }}%</td>
                                </tr>
                                <tr>
                                    <td>Total Duration</td>
                                    <td>{{ $data['summary']['total_duration'] ?? 0 }} minutes</td>
                                </tr>
                                <tr>
                                    <td>Average Duration</td>
                                    <td>{{ $data['summary']['average_duration'] ?? 0 }} minutes</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Detailed Report -->
                        <div class="mb-4">
                            <h6>Detailed Report</h6>
                            @if(isset($data['detailed']) && $data['detailed']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Patient</th>
                                            <th>Type</th>
                                            <th>Physiotherapist</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                            <th>Start Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['detailed']->take(50) as $procedure)
                                        <tr>
                                            <td>{{ $procedure->id }}</td>
                                            <td>{{ $procedure->appointment->patient->name ?? 'N/A' }}</td>
                                            <td>{{ $procedure->physiotherapyType->name ?? 'N/A' }}</td>
                                            <td>{{ $procedure->physiotherapist->name ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $procedure->status == 'completed' ? 'success' : ($procedure->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $procedure->status)) }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $procedure->counter }}/{{ $procedure->days_count }}</td>
                                            <td>{{ $procedure->start_date ? $procedure->start_date->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted"><em>Showing first 50 procedures. Total: {{ $data['detailed']->count() }}</em></p>
                            @else
                            <p class="text-muted">No procedures found for the selected period.</p>
                            @endif
                        </div>

                        <!-- Report by Type -->
                        <div class="mb-4">
                            <h6>Report by Type</h6>
                            @if(isset($data['by_type']) && $data['by_type']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Total</th>
                                            <th>Completed</th>
                                            <th>In Progress</th>
                                            <th>Pending</th>
                                            <th>Completion Rate</th>
                                            <th>Avg Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['by_type'] as $type)
                                        <tr>
                                            <td>{{ $type['type']->name ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ $type['total_procedures'] ?? 0 }}</span>
                                            </td>
                                            <td class="text-center">{{ $type['completed_procedures'] ?? 0 }}</td>
                                            <td class="text-center">{{ $type['in_progress_procedures'] ?? 0 }}</td>
                                            <td class="text-center">{{ $type['pending_procedures'] ?? 0 }}</td>
                                            <td class="text-center">{{ number_format($type['completion_rate'] ?? 0, 1) }}%</td>
                                            <td class="text-center">{{ $type['average_duration'] ?? 0 }} min</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted">No data available by type.</p>
                            @endif
                        </div>

                        <!-- Report by Physiotherapist -->
                        <div class="mb-4">
                            <h6>Report by Physiotherapist</h6>
                            @if(isset($data['by_physiotherapist']['physiotherapists']) && $data['by_physiotherapist']['physiotherapists']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Physiotherapist</th>
                                            <th>Total</th>
                                            <th>Completed</th>
                                            <th>In Progress</th>
                                            <th>Pending</th>
                                            <th>Completion Rate</th>
                                            <th>Performance Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['by_physiotherapist']['physiotherapists'] as $physiotherapist)
                                        <tr>
                                            <td>
                                                <strong>{{ $physiotherapist['name'] ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $physiotherapist['email'] ?? 'N/A' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ $physiotherapist['total_procedures'] ?? 0 }}</span>
                                            </td>
                                            <td class="text-center">{{ $physiotherapist['completed_procedures'] ?? 0 }}</td>
                                            <td class="text-center">{{ $physiotherapist['in_progress_procedures'] ?? 0 }}</td>
                                            <td class="text-center">{{ $physiotherapist['pending_procedures'] ?? 0 }}</td>
                                            <td class="text-center">{{ number_format($physiotherapist['completion_rate'] ?? 0, 1) }}%</td>
                                            <td class="text-center">{{ number_format($physiotherapist['performance_score'] ?? 0, 1) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted">No data available by physiotherapist.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
        <p>Report generated by Mod Health App</p>
        <p>Date Range: {{ verta($startDate)->format('Y-m-d') }} to {{ verta($endDate)->format('Y-m-d') }}</p>
    </div>
</body>
</html>
