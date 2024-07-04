<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style type="text/css">
        body,
        body *,
        .label {
            font-family: 'Arial', sans-serif !important;
        }

        .excel_table_title {
            text-align: center;
        }

        #print_excel_table {
            border-collapse: collapse;
            width: 100%;
            direction: rtl;
        }

        #print_excel_table td,
        #print_excel_table th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        #print_excel_table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #print_excel_table tr:hover {
            background-color: #ddd;
        }

        #print_excel_table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: center;
            background-color: #c2bfbf;
            color: rgb(34, 33, 33);
        }
    </style>
</head>

<body>
    <h2 class="excel_table_title">{{ localize('global.operations_report_title') }}</h2>
    <div class="col-md-12 mt-2">
        <table class="table" id="print_excel_table">
            <thead>
                <tr>
                    <th>{{ localize('global.number') }}</th>
                    <th>{{ localize('global.patient_name') }}</th>
                    <th>{{ localize('global.operation_surgion') }}</th>
                    <th>{{ localize('global.operation_status') }}</th>
                    <th>{{ localize('global.operation_approval') }}</th>
                    <th>{{ localize('global.reserve_status') }}</th>
                    <th>{{ localize('global.operation_type') }}</th>
                    <th>{{ localize('global.date') }}</th>
                    <th>{{ localize('global.time') }}</th>
                 
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->patient_name }}</td>
                        <td>{{ $item->operation_surgion_name}}</td>
                        <td>
                        @if ($item->is_operation_done == '0')
                            <span class="badge rounded-pill bg-danger">
                                {{ localize('global.operation_uncomplete') }}
                            </span>              
                        @else
                        <span class="badge rounded-pill bg-success">
                                {{ localize('global.operation_complete') }}
                            </span> 
                        @endif
                        </td>
                        <td>
                        @if ($item->is_operation_approved == '0')
                            <span class="badge rounded-pill bg-danger">
                                {{ localize('global.operation_not_approved') }}
                            </span>              
                        @else
                        <span class="badge rounded-pill bg-primary">
                                {{ localize('global.operation_approved') }}
                            </span> 
                        @endif
                        </td>
                        <td>
                        @if ($item->is_reserved == '0')
                            <span class="badge rounded-pill bg-danger">
                                {{ localize('global.not_reserved') }}
                            </span>              
                        @else
                        <span class="badge rounded-pill bg-info">
                                {{ localize('global.reserved') }}
                            </span> 
                        @endif
                        </td>
                        <td>{{ $item->operation_type_name }}</td>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->time }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>