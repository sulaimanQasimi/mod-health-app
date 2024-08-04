
<div class="modal-header">
    <h5 class="modal-title" id="showLabsItemModalLabel">
        {{ localize('global.show_lab_details') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>
<div class="modal-body" >
    <table class="table">
        <thead>
            <tr>
                <th>{{ localize('global.number') }}</th>
                <th>{{ localize('global.test_name') }}</th>
                <th>{{ localize('global.test_status') }}</th>
                <th>{{ localize('global.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lab_items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->labType->name }}</td>
                    <td>
                        @if ($item->status == '0')
                            <span
                                class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                        @else
                            <span class="badge bg-success">{{ localize('global.tested') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($item->is_delivered == 0)
                        <a href="{{ route('lab_items.deleteItem', $item) }}" class="btn btn-sm btn-danger">
                            <span class="bx bx-trash"></span>
                        </a>
                        @endif
                    </td>
                    
    
                </tr>
    
            @empty
                <div class="container">
                    <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                        <div class=" badge bg-label-danger mt-4">
                            {{ localize('global.no_previous_labs') }}
                        </div>
                    </div>
                </div>
            @endforelse
    
        </tbody>
    </table>
    
</div>
<div class="modal-footer">
    @if($lab->status == 0)
        <div class="d-flex justify-content-center mt-4">
            <form
                action="{{ route('lab_tests.print-card', ['lab' => $lab_items[0]->lab_id]) }}"
                method="GET" target="_blank">
                <button class="btn btn-primary" type="submit"><span
                        class="bx bx-printer me-1"></span>{{ localize('global.print_labs_card') }}</button>
            </form>
        </div>
    @endif
   
</div>

