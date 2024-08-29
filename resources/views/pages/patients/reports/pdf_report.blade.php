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
    <h2 class="excel_table_title">{{ localize('global.reception_report_title') }}</h2>
    <div class="col-md-12 mt-2">
        <table class="table" id="print_excel_table">
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
            </tbody>
        </table>
    </div>
</body>

</html>