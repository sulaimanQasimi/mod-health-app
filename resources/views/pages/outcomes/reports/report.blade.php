<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ localize('global.outcome_report') }}</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                <i class="bx bx-file me-1"></i>{{ localize('global.export_excel') }}
            </button>
            <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                <i class="bx bx-file-pdf me-1"></i>{{ localize('global.export_pdf') }}
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all" class="form-check-input">
                    </th>
                    <th>{{ localize('global.medicine_name') }}</th>
                    <th>{{ localize('global.patient_name') }}</th>
                    <th>{{ localize('global.doctor_name') }}</th>
                    <th>{{ localize('global.amount') }}</th>
                    <th>{{ localize('global.outcome_type') }}</th>
                    <th>{{ localize('global.outcome_date') }}</th>
                    <th>{{ localize('global.reason') }}</th>
                    <th>{{ localize('global.batch_number') }}</th>
                    <th>{{ localize('global.created_by') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input item-checkbox" value="{{ $item->id }}">
                        </td>
                        <td>{{ $item->medicine_name ?? '-' }}</td>
                        <td>{{ $item->patient_name ?? '-' }}</td>
                        <td>{{ $item->doctor_name ?? '-' }}</td>
                        <td>{{ $item->amount ?? '-' }}</td>
                        <td>
                            @switch($item->outcome_type)
                                @case('prescription')
                                    <span class="badge bg-primary">{{ localize('global.prescription') }}</span>
                                    @break
                                @case('expired')
                                    <span class="badge bg-warning">{{ localize('global.expired') }}</span>
                                    @break
                                @case('damaged')
                                    <span class="badge bg-danger">{{ localize('global.damaged') }}</span>
                                    @break
                                @case('lost')
                                    <span class="badge bg-secondary">{{ localize('global.lost') }}</span>
                                    @break
                                @case('return')
                                    <span class="badge bg-info">{{ localize('global.return') }}</span>
                                    @break
                                @default
                                    <span class="badge bg-dark">{{ $item->outcome_type }}</span>
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
                        <td>{{ $item->created_by_name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">{{ localize('global.no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($items->count() > 0)
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">{{ localize('global.total_records') }}: {{ $items->count() }}</span>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                        {{ localize('global.select_all') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                        {{ localize('global.deselect_all') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function selectAll() {
    $('.item-checkbox').prop('checked', true);
    $('#select-all').prop('checked', true);
}

function deselectAll() {
    $('.item-checkbox').prop('checked', false);
    $('#select-all').prop('checked', false);
}

function exportReport(type) {
    const selectedItems = [];
    $('.item-checkbox:checked').each(function() {
        selectedItems.push($(this).val());
    });
    
    if (selectedItems.length === 0) {
        alert('{{ localize("global.please_select_items_to_export") }}');
        return;
    }
    
    const form = $('<form>', {
        'method': 'POST',
        'action': '{{ route("outcomes.export-report") }}'
    });
    
    form.append($('<input>', {
        'type': 'hidden',
        'name': '_token',
        'value': '{{ csrf_token() }}'
    }));
    
    form.append($('<input>', {
        'type': 'hidden',
        'name': 'data',
        'value': JSON.stringify(selectedItems)
    }));
    
    form.append($('<input>', {
        'type': 'hidden',
        'name': 'type',
        'value': type
    }));
    
    $('body').append(form);
    form.submit();
    form.remove();
}

$(document).ready(function() {
    $('#select-all').change(function() {
        $('.item-checkbox').prop('checked', $(this).is(':checked'));
    });
    
    $('.item-checkbox').change(function() {
        if (!$(this).is(':checked')) {
            $('#select-all').prop('checked', false);
        } else {
            const totalCheckboxes = $('.item-checkbox').length;
            const checkedCheckboxes = $('.item-checkbox:checked').length;
            if (checkedCheckboxes === totalCheckboxes) {
                $('#select-all').prop('checked', true);
            }
        }
    });
});
</script>
