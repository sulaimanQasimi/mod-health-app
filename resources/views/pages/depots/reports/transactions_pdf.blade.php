<h2>Depot Transactions Report</h2>
<table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Number</th><th>Type</th><th>Status</th><th>Source</th><th>Destination</th><th>Item</th><th>Qty</th><th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->transaction_number }}</td>
                <td>{{ $item->type }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->fromDepot?->name ?? $item->depot?->name }}</td>
                <td>{{ $item->toDepot?->name ?? $item->pharmacy?->name }}</td>
                <td>{{ $item->medicine?->name ?? $item->tool?->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ optional($item->transaction_date)->format('Y-m-d') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
