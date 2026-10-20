@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0">
                    <i class="bx bx-history me-2"></i>{{ localize('activity_log.title') }}
                </h5>
                <small class="text-muted">{{ localize('activity_log.subtitle') }}</small>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-trash me-1"></i>{{ localize('activity_log.prune') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">{{ localize('activity_log.prune_hint') }}</h6></li>
                    @foreach($prunePeriods as $period)
                        <li>
                            <form method="POST" action="{{ route('activity-logs.prune') }}"
                                  onsubmit="return confirm(@json(str_replace(':period', $period['label'], localize('activity_log.prune_confirm'))));">
                                @csrf
                                <input type="hidden" name="period" value="{{ $period['value'] }}">
                                <button type="submit" class="dropdown-item text-danger">
                                    {{ $period['label'] }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="{{ localize('activity_log.search_placeholder') }}">
                </div>
                <div class="col-md-3">
                    <select name="event" class="form-select">
                        <option value="">{{ localize('activity_log.all_events') }}</option>
                        @foreach($events as $eventOption)
                            <option value="{{ $eventOption['value'] }}" @selected(request('event') === $eventOption['value'])>
                                {{ $eventOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="subject_type" class="form-select select2">
                        <option value="">{{ localize('activity_log.all_subject_types') }}</option>
                        @foreach($subjectTypes as $typeOption)
                            <option value="{{ $typeOption['value'] }}" @selected(request('subject_type') === $typeOption['value'])>
                                {{ $typeOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-secondary w-100" type="submit">{{ localize('global.search') }}</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary w-100">{{ localize('global.reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ localize('activity_log.created_at') }}</th>
                        <th>{{ localize('activity_log.event') }}</th>
                        <th>{{ localize('activity_log.subject_type') }}</th>
                        <th>{{ localize('activity_log.subject_id') }}</th>
                        <th>{{ localize('activity_log.causer') }}</th>
                        <th>{{ localize('activity_log.ip_address') }}</th>
                        <th>{{ localize('activity_log.description') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        @php
                            $ip = data_get($activity->properties?->toArray() ?? [], 'ip');
                            $eventClass = match($activity->event) {
                                'created' => 'bg-success',
                                'updated' => 'bg-primary',
                                'deleted' => 'bg-danger',
                                'restored' => 'bg-info',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                {{ $activity->created_at ? verta($activity->created_at)->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td>
                                <span class="badge {{ $eventClass }}">
                                    {{ \App\Support\ActivityLogTranslator::eventLabel((string) $activity->event) }}
                                </span>
                            </td>
                            <td>{{ \App\Support\ActivityLogTranslator::subjectTypeLabel($activity->subject_type) }}</td>
                            <td>{{ $activity->subject_id ?? '-' }}</td>
                            <td>
                                @if($activity->causer)
                                    {{ trim(($activity->causer->name ?? '').' '.($activity->causer->last_name ?? '')) }}
                                @else
                                    <span class="text-muted">{{ localize('activity_log.system') }}</span>
                                @endif
                            </td>
                            <td class="text-nowrap"><code>{{ $ip ?: '-' }}</code></td>
                            <td>{{ \Illuminate\Support\Str::limit($activity->description, 80) }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('activity-logs.show', $activity) }}" class="btn btn-sm btn-primary" title="{{ localize('activity_log.view_details') }}">
                                    <i class="bx bx-show-alt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">{{ localize('activity_log.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())
            <div class="card-footer">{{ $activities->links() }}</div>
        @endif
    </div>
</div>
@endsection
