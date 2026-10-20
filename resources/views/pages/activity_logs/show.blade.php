@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">{{ localize('activity_log.details') }}</h4>
            <small class="text-muted">#{{ $activity->id }}</small>
        </div>
        <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">{{ localize('activity_log.performed_by') }}</h6></div>
                <div class="card-body">
                    @if($activity->causer)
                        <div class="fw-semibold">{{ trim(($activity->causer->name ?? '').' '.($activity->causer->last_name ?? '')) }}</div>
                        <div class="text-muted small">{{ $activity->causer->email ?? '' }}</div>
                    @else
                        <span class="text-muted">{{ localize('activity_log.system') }}</span>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">{{ localize('activity_log.target_record') }}</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">{{ localize('activity_log.event') }}</dt>
                        <dd class="col-7">
                            <span class="badge bg-secondary">
                                {{ \App\Support\ActivityLogTranslator::eventLabel((string) $activity->event) }}
                            </span>
                        </dd>
                        <dt class="col-5">{{ localize('activity_log.subject_type') }}</dt>
                        <dd class="col-7">{{ \App\Support\ActivityLogTranslator::subjectTypeLabel($activity->subject_type) }}</dd>
                        <dt class="col-5">{{ localize('activity_log.subject_id') }}</dt>
                        <dd class="col-7">{{ $activity->subject_id ?? '-' }}</dd>
                        <dt class="col-5">{{ localize('activity_log.created_at') }}</dt>
                        <dd class="col-7">{{ $activity->created_at ? verta($activity->created_at)->format('Y-m-d H:i:s') : '-' }}</dd>
                        <dt class="col-5">{{ localize('activity_log.description') }}</dt>
                        <dd class="col-7">{{ $activity->description }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ localize('activity_log.technical_details') }}</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">{{ localize('activity_log.ip_address') }}</dt>
                        <dd class="col-7"><code>{{ $ip ?: '-' }}</code></dd>
                        <dt class="col-5">{{ localize('activity_log.request_method') }}</dt>
                        <dd class="col-7"><code>{{ $method ?: '-' }}</code></dd>
                        <dt class="col-5">{{ localize('activity_log.request_url') }}</dt>
                        <dd class="col-7 text-break small">{{ $url ?: '-' }}</dd>
                        <dt class="col-5">{{ localize('activity_log.user_agent') }}</dt>
                        <dd class="col-7 text-break small">{{ $userAgent ?: '-' }}</dd>
                        <dt class="col-5">{{ localize('activity_log.log_name') }}</dt>
                        <dd class="col-7">{{ $activity->log_name }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ localize('activity_log.changed_fields') }}</h6>
                    <span class="badge bg-label-primary">{{ str_replace(':count', (string) $changeKeys->count(), localize('activity_log.fields_count')) }}</span>
                </div>
                <div class="card-body p-0">
                    @if($changeKeys->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">{{ localize('activity_log.no_field_changes') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ localize('activity_log.field_name') }}</th>
                                        <th>{{ localize('activity_log.value_before') }}</th>
                                        <th>{{ localize('activity_log.value_after') }}</th>
                                        <th>{{ localize('activity_log.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($changeKeys as $key)
                                        @php
                                            $before = $old[$key] ?? null;
                                            $after = $attributes[$key] ?? null;
                                            $hasBefore = array_key_exists($key, $old);
                                            $hasAfter = array_key_exists($key, $attributes);
                                            if ($hasAfter && ! $hasBefore) {
                                                $status = localize('activity_log.status_added');
                                                $badge = 'bg-success';
                                            } elseif ($hasBefore && ! $hasAfter) {
                                                $status = localize('activity_log.status_removed');
                                                $badge = 'bg-danger';
                                            } elseif ($before !== $after) {
                                                $status = localize('activity_log.status_changed');
                                                $badge = 'bg-primary';
                                            } else {
                                                $status = localize('activity_log.status_unchanged');
                                                $badge = 'bg-secondary';
                                            }
                                            $format = function ($value) {
                                                if (is_bool($value)) {
                                                    return $value ? 'true' : 'false';
                                                }
                                                if ($value === null) {
                                                    return 'null';
                                                }
                                                if (is_array($value) || is_object($value)) {
                                                    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                                }
                                                return (string) $value;
                                            };
                                        @endphp
                                        <tr>
                                            <td><code>{{ $key }}</code></td>
                                            <td class="text-break small">{{ $hasBefore ? $format($before) : '—' }}</td>
                                            <td class="text-break small">{{ $hasAfter ? $format($after) : '—' }}</td>
                                            <td><span class="badge {{ $badge }}">{{ $status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ localize('activity_log.raw_data') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="copyJsonBtn">
                        {{ localize('activity_log.copy_json') }}
                    </button>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded mb-0 small" id="activityJson" style="max-height: 360px; overflow: auto;">{{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('copyJsonBtn')?.addEventListener('click', function () {
    const text = document.getElementById('activityJson')?.textContent || '';
    navigator.clipboard.writeText(text).then(() => {
        const btn = this;
        const original = btn.textContent;
        btn.textContent = @json(localize('activity_log.copied'));
        setTimeout(() => { btn.textContent = original; }, 1500);
    });
});
</script>
@endsection
