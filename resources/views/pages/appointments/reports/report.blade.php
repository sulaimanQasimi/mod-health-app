<div>
    <form action="{{ route('appointments.export-report') }}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="data" value="{{ collect($items)->pluck('id')->toJson() }}">

        <div class="demo-inline-spacing">
            {{-- <button type="button" onclick="exportExcelFile()" value="excel" class="btn btn-label-primary">
                <span class="me-1"><i class="fa fa-file-excel"></i></span>export Excel
            </button> --}}
            <button type="submit" name="type" value="excel" class="btn btn-label-primary">
                <span class="me-1"> <i class="fa fa-file-excel"></i></span>Excel
            </button>
            <button type="submit" name="type" value="pdf" class="btn btn-label-danger">
                <span class="me-1"><i class="fa fa-file-pdf"></i></span>PDF
            </button>
        </div>

    </form>
    <div class="col-md-12 mt-2">
        <table class="table table-bordered table-striped table-responsive w-100" id="print_excel_table">
            <thead>
                <tr>
                <th>{{ localize('global.number') }}</th>
                    <th>{{ localize('global.patient_name') }}</th>
                    <th>{{ localize('global.doctor_name') }}</th>
                    <th>{{ localize('global.branch') }}</th>
                    <th>{{ localize('global.processed_by') }}</th>
                    <th>{{ localize('global.status') }}</th>
                    <th>{{ localize('global.date') }}</th>
                    <th>{{ localize('global.time') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $item)
                    <tr>
                        <td>
                            @if(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator'))
                                {{ $items->firstItem() + $loop->index }}
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </td>
                        <td>{{ $item->patient->name ?? '—' }}</td>
                        <td>{{ $item->doctor->name ?? '—' }}</td>
                        <td>{{ $item->branch->name ?? '—' }}</td>
                        <td>{{ $item->processedBy->name ?? '—' }}</td>
                        <td>
                        @if ($item->is_completed == '0')
                            <span class="badge rounded-pill bg-primary">
                                {{ localize('global.ongoing_appointments') }}
                            </span>              
                        @else
                        <span class="badge rounded-pill bg-success">
                                {{ localize('global.completed_appointments') }}
                            </span> 
                        @endif
                        </td>
                        <td>
                            @php
                                $vertaDate = $item->date ? \Hekmatinasser\Verta\Facades\Verta::createFromFormat('Y-m-d', $item->date) : false;
                            @endphp
                            @if($vertaDate)
                                {{ $vertaDate->format('Y/m/d') }}
                            @else
                                {{ $item->date ?? '—' }}
                            @endif
                        </td>
                        <td>{{ $item->time }}</td>
                        
                    </tr>
                @endforeach
                @if ($items->count() == 0)
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            {{ localize('global.no_item_is_found') }}!!
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator') && $items->hasPages())
            <div class="card-footer border-top py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted small mb-2 mb-md-0">
                        {{ localize('global.showing') }} {{ $items->firstItem() }} {{ localize('global.to') }} {{ $items->lastItem() }} 
                        {{ localize('global.of') }} {{ $items->total() }} {{ localize('global.results') }}
                    </div>
                    <div>
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        @elseif(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator'))
            <div class="card-footer border-top py-3">
                <div class="text-muted small">
                    {{ localize('global.showing') }} {{ $items->total() }} {{ localize('global.results') }}
                </div>
            </div>
        @endif
    </div>
</div>
