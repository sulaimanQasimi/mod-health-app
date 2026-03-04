<div>
    <div class="col-md-12 mt-2">
        <table class="table table-bordered table-striped table-responsive w-100" id="print_excel_table">
            <thead>
                <tr>
                    <th>{{ localize('global.number') }}</th>
                    <th>{{ localize('global.patient_name') }}</th>
                    <th>{{ localize('global.father_name') }}</th>
                    <th>{{ localize('global.patient_id_card') }}</th>
                    <th>{{ localize('global.doctor_name') }}</th>
                    <th>{{ localize('global.department') }}</th>
                    <th>{{ localize('global.branch') }}</th>
                    <th>{{ localize('global.pharmacy') }}</th>
                    <th>{{ localize('global.processed_by') }}</th>
                    <th>{{ localize('global.date') }}</th>
                    <th>{{ localize('global.status') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->patient_name ?? '-' }}</td>
                        <td>{{ $item->patient_father_name ?? '-' }}</td>
                        <td>{{ $item->patient_id_card ?? '-' }}</td>
                        <td>{{ $item->doctor_name ?? '-' }}</td>
                        <td>{{ $item->department_name ?? '-' }}</td>
                        <td>{{ $item->branch_name ?? '-' }}</td>
                        <td>{{ $item->pharmacy_name ?? '-' }}</td>
                        <td>{{ $item->processor_name ?? '-' }}</td>
                        <td>
                            @if($item->created_at)
                                {{ \Hekmatinasser\Verta\Verta::instance($item->created_at)->format('Y/m/d H:i') }}
                            @else
                                -
                            @endif
                        </td>
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
                @if ($items->count() == 0)
                    <tr>
                        <td colspan="11" class="text-center text-danger">
                            {{ localize('global.no_item_is_found') }}!!</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
