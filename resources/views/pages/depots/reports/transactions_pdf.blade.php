<h2>{{ localize('global.depot.report_transactions') }}</h2>
<table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>{{ localize('global.number') }}</th>
            <th>{{ localize('global.type') }}</th>
            <th>{{ localize('global.status') }}</th>
            <th>{{ localize('global.source') }}</th>
            <th>{{ localize('global.destination') }}</th>
            <th>{{ localize('global.item') }}</th>
            <th>{{ localize('global.quantity') }}</th>
            <th>{{ localize('global.date') }}</th>
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
                <td>{{ $item->transaction_date ? verta($item->transaction_date)->format('Y-m-d') : '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
