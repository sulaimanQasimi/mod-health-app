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
    <h2 class="excel_table_title">{{ localize('global.test_registration_report') }}</h2>
    <div class="col-md-12 mt-2">
        <table class="table" id="print_excel_table">
            <thead>
                <tr>
                    <th>{{ localize('global.number') }}</th>
                    <th>{{ localize('global.test_type') }}</th>
                    <th>{{ localize('global.date') }}</th>
                    <th>{{ localize('global.count') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['test_type'] }}</td>
                        <td>{{ $item['date'] }}</td>
                        <td>{{ $item['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
