<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Notes - Print</title>
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
        
        .observation-column {
            width: 40%;
            text-align: left;
        }
        
        .time-column {
            width: 15%;
        }
        
        .date-column {
            width: 15%;
        }
        
        .nurse-column {
            width: 20%;
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
        
        .note-text {
            text-align: left;
            padding: 5px;
            word-wrap: break-word;
            max-width: 300px;
        }
        
        .time-display {
            font-weight: bold;
        }
        
        .empty-row {
            height: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="pashto-title">د نرس یادداشتونه</div>
            <div class="english-title">Nurse Notes</div>
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
                    <th class="column-header observation-column">
                        Observation Consist Of Medication Management<br>
                        <span class="sub-header">(د درملو د مدیریت د مشاهداتو یادداشتونه)</span>
                    </th>
                    <th class="column-header date-column">
                        نیته<br>
                        <span class="sub-header">(Date)</span>
                    </th>
                    <th class="column-header time-column">
                        PM<br>
                        <span class="sub-header">(Afternoon)</span>
                    </th>
                    <th class="column-header time-column">
                        AM<br>
                        <span class="sub-header">(Morning)</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($nurseNotes->count() > 0)
                    @foreach($nurseNotes as $note)
                    <tr>
                        <td>
                            @if($note->nurse)
                                {{ $note->nurse->first_name }} {{ $note->nurse->last_name }}
                                @if($note->nurse->employee_id)
                                    <br><small>({{ $note->nurse->employee_id }})</small>
                                @endif
                            @endif
                        </td>
                        <td class="note-text">
                            @if($note->note)
                                {{ $note->note }}
                            @endif
                        </td>
                        <td>
                            @if($note->date)
                                {{ $note->date->format('Y-m-d') }}
                            @endif
                        </td>
                        <td class="time-display">
                            @if($note->time_pm)
                                {{ $note->time_pm->format('H:i') }}
                            @endif
                        </td>
                        <td class="time-display">
                            @if($note->time_am)
                                {{ $note->time_am->format('H:i') }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>
                            No nurse notes found
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Print and Back Buttons (hidden when printing) -->
    <button class="print-button no-print" onclick="window.print()">Print Notes</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
