<div>
    <form action="{{ route('blood_banks.export-report') }}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="data" value="{{ $items->pluck('id') }}">

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
                    <th>{{ localize('global.status') }}</th>
                    <th>{{ localize('global.blood_group') }}</th>
                    <th>{{ localize('global.blood_rh') }}</th>
                    <th>{{ localize('global.department') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->patient_name }}</td>
                        <td>{{ $item->status }}</td>
                        <td>{{ $item->group }}</td>
                        <td>{{ $item->rh }}</td>
                        <td>{{ $item->department_name }}</td>
                        
                    </tr>
                @endforeach
                @if ($items->count() == 0)
                    <tr>
                        <td colspan="9" class="text-center text-danger">
                            {{ localize('global.no_item_is_found') }}!!</td>
                    </tr>
                @endif
            </tbody>
    </div>
</div>
