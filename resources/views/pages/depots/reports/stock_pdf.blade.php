<h2>Depot Stock Report</h2>
<table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead><tr><th>Depot</th><th>Type</th><th>Item</th><th>Available</th><th>Unit</th></tr></thead>
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
