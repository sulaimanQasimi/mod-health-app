<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diabetic Chart - Print</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.2;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header .pashto-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            direction: rtl;
            text-align: center;
        }
        
        .header .english-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }
        
        .patient-details {
            margin-top: 15px;
            font-size: 12px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 10px;
        }
        
        .patient-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            flex-wrap: wrap;
        }
        
        .patient-details-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-item {
            flex: 1;
            margin-right: 15px;
            min-width: 150px;
        }
        
        .detail-item:last-child {
            margin-right: 0;
        }
        
        .detail-item.full-width {
            flex: 100%;
            margin-right: 0;
        }
        
        .detail-item strong {
            color: #495057;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border: 2px solid #000;
        }
        
        .main-table th {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            background-color: #f0f0f0;
            vertical-align: middle;
        }
        
        .main-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            height: 30px;
            vertical-align: middle;
        }
        
        .main-table .column-header {
            font-weight: bold;
            text-align: center;
        }
        
        .main-table .sub-header {
            font-size: 10px;
            font-weight: normal;
            margin-top: 2px;
        }
        
        .glucose-column {
            width: 15%;
        }
        
        .glucose-sub-columns {
            display: flex;
            width: 100%;
        }
        
        .glucose-sub-column {
            flex: 1;
            border-right: 1px solid #000;
        }
        
        .glucose-sub-column:last-child {
            border-right: none;
        }
        
        .sliding-scale-section {
            margin-top: 40px;
        }
        
        .sliding-scale-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .sliding-scale-description {
            margin-bottom: 15px;
            text-align: justify;
            line-height: 1.4;
        }
        
        .sliding-scale-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        
        .sliding-scale-table th {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            background-color: #f0f0f0;
        }
        
        .sliding-scale-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background-color: #0056b3;
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            text-decoration: none;
        }
        
        .back-button:hover {
            background-color: #545b62;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="pashto-title">دشکر ناروغانو چارت</div>
            <div class="english-title">Diabetic Chart</div>
            @if($patient)
                <div class="patient-details">
                    <div class="patient-details-row">
                        <div class="detail-item">
                            <strong>Patient Name:</strong> {{ $patient->first_name }} {{ $patient->last_name }}
                        </div>
                        <div class="detail-item">
                            <strong>Patient ID:</strong> {{ $patient->patient_id ?? 'N/A' }}
                        </div>
                        <div class="detail-item">
                            <strong>Gender:</strong> {{ ucfirst($patient->gender ?? 'N/A') }}
                        </div>
                    </div>
                    <div class="patient-details-row">
                        <div class="detail-item">
                            <strong>Date of Birth:</strong> {{ $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : 'N/A' }}
                        </div>
                        <div class="detail-item">
                            <strong>Age:</strong> {{ $patient->date_of_birth ? $patient->date_of_birth->age . ' years' : 'N/A' }}
                        </div>
                        <div class="detail-item">
                            <strong>Phone:</strong> {{ $patient->phone ?? 'N/A' }}
                        </div>
                    </div>
                    @if($patient->address)
                    <div class="patient-details-row">
                        <div class="detail-item full-width">
                            <strong>Address:</strong> {{ $patient->address }}
                        </div>
                    </div>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Main Data Entry Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th class="column-header">
                        د نرس لاسلیک<br>
                        <span class="sub-header">(signature of the Staff Nurse)</span>
                    </th>
                    <th class="column-header">
                        فمي درمل<br>
                        <span class="sub-header">(Oral medicine)</span>
                    </th>
                    <th class="column-header">
                        د انسولین دوز<br>
                        <span class="sub-header">(Insulin with Dose)</span>
                    </th>
                    <th class="column-header glucose-column">
                        دگلوکوز کچه<br>
                        <span class="sub-header">(blood glucose level)</span>
                        <div class="glucose-sub-columns">
                            <div class="glucose-sub-column">RBS</div>
                            <div class="glucose-sub-column">FBS</div>
                        </div>
                    </th>
                    <th class="column-header">
                        وخت<br>
                        <span class="sub-header">(Time)</span>
                    </th>
                    <th class="column-header">
                        نيته<br>
                        <span class="sub-header">(Date)</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($diabetesCharts->count() > 0)
                    @foreach($diabetesCharts as $chart)
                    <tr>
                        <td>
                            @if($chart->nurse)
                                {{ $chart->nurse->first_name }} {{ $chart->nurse->last_name }}
                                @if($chart->nurse->employee_id)
                                    <br><small>({{ $chart->nurse->employee_id }})</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($chart->medicine)
                                {{ $chart->medicine->name }}
                            @endif
                        </td>
                        <td>
                            @if($chart->insulin_dose)
                                {{ $chart->insulin_dose }} {{ $chart->unit ?? 'units' }}
                            @endif
                        </td>
                        <td>
                            <div class="glucose-sub-columns">
                                <div class="glucose-sub-column">
                                    @if($chart->rbs)
                                        {{ $chart->rbs }}
                                    @endif
                                </div>
                                <div class="glucose-sub-column">
                                    @if($chart->fbs)
                                        {{ $chart->fbs }}
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($chart->time)
                                {{ $chart->time->format('H:i') }}
                            @endif
                        </td>
                        <td>
                            @if($chart->date)
                                {{ $chart->date->format('Y-m-d') }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>
                            No diabetes chart records found
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Diabetic Sliding Scale Chart Section -->
        <div class="sliding-scale-section">
            <div class="sliding-scale-title">Diabetic Sliding Scale Chart</div>
            
            <div class="sliding-scale-description">
                This sliding scale insulin chart is used to determine the dose of SHORT-ACTING insulin based on the patient's current blood glucose (BG) level. The value may vary based on physician orders.
            </div>
            
            <table class="sliding-scale-table">
                <thead>
                    <tr>
                        <th>Blood Glucose (mg/dL)</th>
                        <th>Insulin Dose (Units)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>&lt;70</td>
                        <td>Call Physician (Possible hypoglycemia)</td>
                    </tr>
                    <tr>
                        <td>70-130</td>
                        <td>0 units</td>
                    </tr>
                    <tr>
                        <td>131-180</td>
                        <td>2 units</td>
                    </tr>
                    <tr>
                        <td>181-230</td>
                        <td>4 units</td>
                    </tr>
                    <tr>
                        <td>231-280</td>
                        <td>6 units</td>
                    </tr>
                    <tr>
                        <td>281-330</td>
                        <td>8 units</td>
                    </tr>
                    <tr>
                        <td>331-400</td>
                        <td>10 units</td>
                    </tr>
                    <tr>
                        <td>&gt;400</td>
                        <td>Call physician (Possible hyperglycemia crisis)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Print and Back Buttons (hidden when printing) -->
    <button class="print-button no-print" onclick="window.print()">Print Chart</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
