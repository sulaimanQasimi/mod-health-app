<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Case Summary</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .header { margin-bottom: 12px; }
        .box { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 6px; }
        .row { display: flex; gap: 12px; }
        .col { flex: 1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #eee; padding: 6px; }
        th { background: #fafafa; text-align: left; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Prosthetics Case Summary</div>
        <div class="muted">Case No: <strong>{{ $prosthetic_case->case_number }}</strong> | Status: <strong>{{ $prosthetic_case->status }}</strong></div>
    </div>

    <div class="box">
        <div class="row">
            <div class="col">
                <div><strong>Patient:</strong> {{ $prosthetic_case->patient->name ?? '-' }} {{ $prosthetic_case->patient->father_name ? '(' . $prosthetic_case->patient->father_name . ')' : '' }}</div>
                <div><strong>MRN / ID:</strong> {{ $prosthetic_case->patient->id_card ?? $prosthetic_case->patient_id }}</div>
            </div>
            <div class="col">
                <div><strong>Side:</strong> {{ $prosthetic_case->side ?? '-' }}</div>
                <div><strong>Device Type:</strong> {{ $prosthetic_case->device_type ?? '-' }}</div>
                <div><strong>Body Region:</strong> {{ $prosthetic_case->body_region ?? '-' }}</div>
            </div>
        </div>

        <div style="margin-top:10px">
            <div><strong>Primary Diagnosis:</strong> {{ $prosthetic_case->primary_diagnosis ?? '-' }}</div>
            <div><strong>Secondary Diagnosis:</strong> {{ $prosthetic_case->secondary_diagnosis ?? '-' }}</div>
        </div>
    </div>

    <div class="box">
        <div class="title">Clinical Assessment</div>
        <div><strong>Fit Outcome:</strong> {{ $prosthetic_case->assessment->fit_outcome ?? '—' }}</div>
        <div style="margin-top:6px"><strong>History / Present Condition:</strong> {{ $prosthetic_case->assessment->history_present_condition ?? '—' }}</div>
        <div><strong>Skin / Stump Notes:</strong> {{ $prosthetic_case->assessment->skin_stump_notes ?? '—' }}</div>
        <div><strong>Functional Goals:</strong> {{ $prosthetic_case->assessment->functional_goals ?? '—' }}</div>
    </div>

    <div class="box">
        <div class="title">Measurements</div>
        <div><strong>Measurement Version:</strong> {{ $latestMeasurementSet->version ?? '—' }}</div>

        <table style="margin-top:8px">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Value (Numeric)</th>
                    <th>Unit</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rows = $latestMeasurementSet?->measurements ?? collect();
                @endphp
                @foreach($rows as $m)
                    <tr>
                        <td>{{ $m->name }}</td>
                        <td>{{ $m->value_numeric ?? $m->value_text ?? '' }}</td>
                        <td>{{ $m->unit ?? '' }}</td>
                        <td>{{ $m->notes ?? '' }}</td>
                    </tr>
                @endforeach
                @if(($rows ?? collect())->count() === 0)
                    <tr><td colspan="4" class="muted">No measurements available.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="title">Prescription</div>
        <div><strong>Device Timing:</strong> {{ $activePrescription->device_timing ?? '-' }}</div>
        <div><strong>Socket:</strong> {{ $activePrescription->socket_type ?? '-' }}</div>
        <div><strong>Liner:</strong> {{ $activePrescription->liner_type ?? '-' }}</div>
        <div><strong>Foot:</strong> {{ $activePrescription->foot_type ?? '-' }}</div>

        <table style="margin-top:8px">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Name</th>
                    <th>Qty</th>
                    <th>Unit Cost Snapshot</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($activePrescription->lines ?? collect()) as $line)
                    <tr>
                        <td>{{ $line->catalogItem->item_code ?? '-' }}</td>
                        <td>{{ $line->catalogItem->name ?? '-' }}</td>
                        <td>{{ $line->quantity }}</td>
                        <td>{{ $line->unit_cost_snapshot }}</td>
                    </tr>
                @endforeach
                @if(($activePrescription->lines ?? collect())->count() === 0)
                    <tr><td colspan="4" class="muted">No prescription lines.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="title">Estimate & Workshop</div>
        <div><strong>Estimate Total:</strong> {{ $latestEstimate->total ?? '—' }} {{ $latestEstimate->currency ?? '' }}</div>
        <div><strong>Work Order:</strong> {{ $activeWorkOrder->work_order_number ?? '—' }}</div>
        <div><strong>Work Order Stage:</strong> {{ $activeWorkOrder->production_stage ?? '—' }}</div>
    </div>

    <div class="box">
        <div class="title">Fitting & Delivery</div>
        <div><strong>Latest Fitting Outcome:</strong> {{ $latestFitting->outcome ?? '—' }}</div>
        <div><strong>Latest Delivery:</strong> {{ $latestDelivery->delivered_at?->format('Y-m-d') ?? '—' }}</div>
        <div><strong>Warranty Until:</strong> {{ $latestDelivery->warranty_until ?? '—' }}</div>
        <div><strong>Follow-up Scheduled:</strong> {{ $latestDelivery->follow_up_scheduled_at ?? '—' }}</div>
    </div>

    <div class="box">
        <div class="title">Follow-up</div>
        <div><strong>Next Follow-up:</strong> {{ $upcomingFollowUp?->scheduled_at ?? '—' }}</div>
    </div>
</body>
</html>

