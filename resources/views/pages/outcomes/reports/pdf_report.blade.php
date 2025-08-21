<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.outcome_report') }}</title>
    <style>
        @font-face {
            font-family: 'B Nazanin';
            src: url('{{ asset("persian_font.ttf") }}') format('opentype');
        }
        
        body {
            font-family: 'B Nazanin', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .report-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-info td {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .report-info td:first-child {
            font-weight: bold;
            width: 120px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            color: white;
            font-weight: bold;
        }
        
        .badge-primary { background-color: #007bff; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
        .badge-info { background-color: #17a2b8; }
        .badge-dark { background-color: #343a40; }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        
        .summary p {
            margin: 5px 0;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ localize('global.outcome_report') }}</h1>
        <p>{{ localize('global.generated_on') }}: {{ now()->format('Y/m/d H:i:s') }}</p>
    </div>
    
    <div class="report-info">
        <table>
            <tr>
                <td>{{ localize('global.total_records') }}:</td>
                <td>{{ $items->count() }}</td>
            </tr>
            <tr>
                <td>{{ localize('global.report_period') }}:</td>
                                 <td>
                     @if($items->min('outcome_date') && $items->max('outcome_date'))
                         {{ \Verta::instance($items->min('outcome_date'))->format('Y/m/d') }} - {{ \Verta::instance($items->max('outcome_date'))->format('Y/m/d') }}
                     @else
                         -
                     @endif
                 </td>
            </tr>
        </table>
    </div>
    
    <div class="summary">
        <h3>{{ localize('global.summary') }}</h3>
        @php
            $prescriptionCount = $items->where('outcome_type', 'prescription')->count();
            $expiredCount = $items->where('outcome_type', 'expired')->count();
            $damagedCount = $items->where('outcome_type', 'damaged')->count();
            $lostCount = $items->where('outcome_type', 'lost')->count();
            $returnCount = $items->where('outcome_type', 'return')->count();
            $totalAmount = $items->sum('amount');
        @endphp
        <p>{{ localize('global.prescription') }}: {{ $prescriptionCount }}</p>
        <p>{{ localize('global.expired') }}: {{ $expiredCount }}</p>
        <p>{{ localize('global.damaged') }}: {{ $damagedCount }}</p>
        <p>{{ localize('global.lost') }}: {{ $lostCount }}</p>
        <p>{{ localize('global.return') }}: {{ $returnCount }}</p>
        <p>{{ localize('global.total_amount') }}: {{ $totalAmount }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>{{ localize('global.row') }}</th>
                <th>{{ localize('global.medicine_name') }}</th>
                <th>{{ localize('global.patient_name') }}</th>
                <th>{{ localize('global.doctor_name') }}</th>
                <th>{{ localize('global.amount') }}</th>
                <th>{{ localize('global.outcome_type') }}</th>
                <th>{{ localize('global.outcome_date') }}</th>
                <th>{{ localize('global.reason') }}</th>
                <th>{{ localize('global.batch_number') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->medicine_name ?? '-' }}</td>
                    <td>{{ $item->patient_name ?? '-' }}</td>
                    <td>{{ $item->doctor_name ?? '-' }}</td>
                    <td>{{ $item->amount ?? '-' }}</td>
                    <td>
                        @switch($item->outcome_type)
                            @case('prescription')
                                <span class="badge badge-primary">{{ localize('global.prescription') }}</span>
                                @break
                            @case('expired')
                                <span class="badge badge-warning">{{ localize('global.expired') }}</span>
                                @break
                            @case('damaged')
                                <span class="badge badge-danger">{{ localize('global.damaged') }}</span>
                                @break
                            @case('lost')
                                <span class="badge badge-secondary">{{ localize('global.lost') }}</span>
                                @break
                            @case('return')
                                <span class="badge badge-info">{{ localize('global.return') }}</span>
                                @break
                            @default
                                <span class="badge badge-dark">{{ $item->outcome_type }}</span>
                        @endswitch
                    </td>
                                         <td>
                         @if($item->outcome_date)
                             {{ \Verta::instance($item->outcome_date)->format('Y/m/d') }}
                         @else
                             -
                         @endif
                     </td>
                    <td>{{ $item->reason ?? '-' }}</td>
                    <td>{{ $item->batch_number ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">{{ localize('global.no_records_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>{{ localize('global.report_generated_by') }}: {{ auth()->user()->name ?? 'System' }}</p>
        <p>{{ localize('global.page') }} 1</p>
    </div>
</body>
</html>
