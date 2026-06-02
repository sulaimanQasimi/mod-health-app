<h2>Depot Requests Report</h2>
<table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead><tr><th>Number</th><th>Status</th><th>Requesting</th><th>Source</th><th>Item</th><th>Qty</th><th>Date</th></tr></thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->request_number }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->requestingDepot?->name }}</td>
                <td>{{ $item->sourceDepot?->name }}</td>
                <td>{{ $item->itemName() }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->created_at?->format('Y-m-d') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
