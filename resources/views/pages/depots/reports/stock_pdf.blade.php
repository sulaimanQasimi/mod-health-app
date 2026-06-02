<h2>{{ localize('global.depot.report_stock') }}</h2>
<table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>{{ localize('global.depot.name') }}</th>
            <th>{{ localize('global.type') }}</th>
            <th>{{ localize('global.item') }}</th>
            <th>{{ localize('global.available') }}</th>
            <th>{{ localize('global.unit') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item['depot_name'] }}</td>
                <td>{{ $item['item_type'] }}</td>
                <td>{{ $item['item_name'] }}</td>
                <td>{{ $item['available'] }}</td>
                <td>{{ $item['unit'] ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
