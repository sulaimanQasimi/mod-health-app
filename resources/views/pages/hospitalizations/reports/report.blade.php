<div>
    <form action="{{ route('hospitalizations.export-report') }}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="data" value="{{ json_encode($items->pluck('id')->values()->all()) }}">

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
                    <th>{{ localize('global.room') }}</th>
                    <th>{{ localize('global.food_type') }}</th>
                    <th>{{ localize('global.companion_card_type') }}</th>
                    <th>{{ localize('global.discharge_status') }}</th>
                    <th>{{ localize('global.doctor_name') }}</th>
                    <th>{{ localize('global.branch') }}</th>
                    <th>{{ localize('global.hospitalization_date') }}</th>
                    <th>{{ localize('global.discharge_date') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->patient_name }}</td>
                        <td>{{ $item->room_name }}</td>
                        <td>{{ $item->food_type_name }}</td>
                        <td>{{ $item->companion_card_type }}</td>
                        <td>{{ $item->discharge_status }}</td>
                        <td>{{ $item->doctor_name }}</td>
                        <td>{{ $item->branch_name }}</td>
                        <td>{{ $item->jalali_created_at }}</td>
                        <td>{{ $item->jalali_discharged_at }}</td>
                    </tr>
                @endforeach
                @if ($items->count() == 0)
                    <tr>
                        <td colspan="10" class="text-center text-danger">
                            {{ localize('global.no_item_is_found') }}!!</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
