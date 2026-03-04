@foreach ($items as $item)
    <tr>
        <td>{{ $startIndex + $loop->iteration }}</td>
        <td>{{ $item->patient_name ?? '-' }}</td>
        <td>{{ $item->patient_father_name ?? '-' }}</td>
        <td>{{ $item->patient_id_card ?? '-' }}</td>
        <td>{{ $item->doctor_name ?? '-' }}</td>
        <td>{{ $item->department_name ?? '-' }}</td>
        <td>{{ $item->branch_name ?? '-' }}</td>
        <td>{{ $item->pharmacy_name ?? '-' }}</td>
        <td>{{ $item->processor_name ?? '-' }}</td>
        <td>{{ $item->created_at ? \Hekmatinasser\Verta\Verta::instance($item->created_at)->format('Y/m/d H:i') : '-' }}</td>
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
