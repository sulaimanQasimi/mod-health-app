
<div class="modal-header">
    <h5 class="modal-title" id="showLabsItemModalLabel">
        {{ localize('global.show_prescription_details') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>
<div class="modal-body" >
    <table class="table">
        <thead>
            <tr>
                <th>{{ localize('global.number') }}</th>
                <th>{{ localize('global.type') }}</th>
                <th>{{ localize('global.description') }}</th>
                <th>{{ localize('global.dosage') }}</th>
                <th>{{ localize('global.frequency') }}</th>
                <th>{{ localize('global.amount') }}</th>
                <th>{{ localize('global.status') }}</th>
                <th>{{ localize('global.actions') }}</th>
                
            </tr>
        </thead>
        <tbody>
            @foreach ($prescription_items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->medicineType->type }}</td>
                <td>{{ $item->medicine->name }}</td>
                <td>{{ $item->dosage }}</td>
                <td>{{ $item->frequency }}</td>
                <td>{{ $item->amount }}</td>
                <td>
                    <span><i
                            class="{{ $item->is_delivered == 0 ? 'bx bx-x-circle text-danger' : 'bx bx-check-circle text-success' }}"></i></span>
                </td>
                <td>
                    @if($item->is_delivered == 0)
                    @can('edit-prescription')
                    <a href="{{ route('prescription_items.editItem', $item->id) }}"><span><i
                        class="bx bx-message-edit"></i></span></a>
                    @endcan
                    @can('delete-prescription')
                    <a href="{{ route('prescription_items.deleteItem', $item) }}"><span><i
                        class="bx bx-trash text-danger"></i></span></a>
                    @endcan
                    @endif
                </td>
                
            </tr>
            @endforeach
    
        </tbody>
    </table>

</div>
<div class="modal-footer">
    
        <div class="d-flex justify-content-center mt-4">
            <form
                action="{{ route('prescriptions.print-card', ['appointment' => $appointment->id, 'prescriptionId' => $prescription->id]) }}"
                method="GET" target="_blank">
                <button class="btn btn-primary" type="submit"><span
                        class="bx bx-printer me-1"></span>{{ localize('global.print_prescription') }}</button>
            </form>
        </div>
   
</div>

