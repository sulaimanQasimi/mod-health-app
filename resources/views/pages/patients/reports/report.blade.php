<div>
    <form action="{{ route('patients.export-report') }}" method="POST">
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
                    <th>{{ localize('global.nid') }}</th>
                    <th>{{ localize('global.id_card') }}</th>
                    <th>{{ localize('global.referral_name') }}</th>
                    <th>{{ localize('global.age') }}</th>
                    <th>{{ localize('global.gender') }}</th>
                    <th>{{ localize('global.job_category') }}</th>
                    <th>{{ localize('global.disease_type') }}</th>
                    <th>{{ localize('global.referred_by') }}</th>
                    <th>{{ localize('global.province') }}</th>
                    <th>{{ localize('global.district') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->patient_name }}</td>
                        <td>{{ $item->nid }}</td>
                        <td>{{ $item->id_card }}</td>
                        <td>{{ $item->referral_name }}</td>
                        <td>{{ $item->age }}</td>
                        <td>
                        @if ($item->gender == '0')
                            <span >
                                {{ localize('global.male') }}
                            </span>              
                        @else
                        <span >
                                {{ localize('global.female') }}
                            </span> 
                        @endif
                        </td>
                        <td>
                        @if ($item->job_category == '0')
                            <span >
                                {{ localize('global.military') }}
                            </span>              
                        @else
                        <span >
                                {{ localize('global.civilian') }}
                            </span> 
                        @endif
                        </td>
                        <td>
                        @if ($item->type == '0')
                            <span >
                                {{ localize('global.mod') }}
                            </span>              
                        @elseif($item->type == '1')
                        <span >
                                {{ localize('global.recipient') }}
                            </span> 
                     @else
                        <span >
                                {{ localize('global.family') }}
                            </span>
                        @endif
                        </td>
                        <td>{{ $item->referred_by }}</td>
                        <td>{{ $item->province_name }}</td>
                        <td>{{ $item->district_name }}</td>
                        
                    </tr>
                @endforeach
                @if ($items->count() == 0)
                    <tr>
                        <td colspan="12" class="text-center text-danger">
                            {{ localize('global.no_item_is_found') }}!!</td>
                    </tr>
                @endif
            </tbody>
    </div>
</div>
