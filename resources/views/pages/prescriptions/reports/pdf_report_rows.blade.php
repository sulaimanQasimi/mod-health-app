@foreach ($items as $item)
    <tr>
        <td>{{ $startIndex + $loop->iteration }}</td>
        <td>{{ $item->patient_name }}</td>
        <td>{{ $item->doctor_name }}</td>
        <td>{{ $item->branch_name }}</td>
        <td>
        @if ($item->is_completed == '0')
            <span class="badge rounded-pill bg-primary">
                {{ localize('global.undelivered_prescriptions') }}
            </span>              
        @else
        <span class="badge rounded-pill bg-success">
                {{ localize('global.delivered_prescriptions') }}
            </span> 
        @endif
        </td>
    </tr>
@endforeach
