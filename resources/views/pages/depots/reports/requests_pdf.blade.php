<h2>{{ localize('global.depot.report_requests') }}</h2>
<table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>{{ localize('global.number') }}</th>
            <th>{{ localize('global.status') }}</th>
            <th>{{ localize('global.depot.requesting_depot') }}</th>
            <th>{{ localize('global.depot.source_depot') }}</th>
            <th>{{ localize('global.item') }}</th>
            <th>{{ localize('global.quantity') }}</th>
            <th>{{ localize('global.date') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->request_number }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->requestingDepot?->name }}</td>
                <td>{{ $item->sourceDepot?->name }}</td>
                <td>{{ $item->itemName() }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->created_at ? verta($item->created_at)->format('Y-m-d') : '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
