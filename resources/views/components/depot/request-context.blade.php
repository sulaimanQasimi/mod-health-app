<div class="border rounded p-3 bg-light mb-0">
    <div class="text-uppercase text-muted small fw-semibold mb-2">{{ localize('global.depot.request_context') }}</div>
    <div class="row g-3">
        @foreach($fields as $field)
            <div class="col-sm-6">
                <div class="text-muted small">{{ $field['label'] }}</div>
                <div class="fw-medium">{{ $field['value'] ?: '—' }}</div>
            </div>
        @endforeach
    </div>
</div>
