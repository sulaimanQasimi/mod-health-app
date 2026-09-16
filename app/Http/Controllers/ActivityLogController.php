<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\PaginatesInertiaIndex;
use App\Support\ActivityLogTranslator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'event', 'subject_type', 'per_page'];

    private const LIST_COLUMNS = [
        'id',
        'description',
        'event',
        'log_name',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'created_at',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::query()
            ->select(self::LIST_COLUMNS)
            ->with(['causer:id,name,last_name,email'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $this->applySearch($query, $request->string('search')->toString());
        }

        if ($request->filled('event')) {
            $query->where('event', $request->string('event')->toString());
        }

        if ($request->filled('subject_type')) {
            $subjectType = $request->string('subject_type')->toString();
            $query->where('subject_type', $subjectType);
        }

        $paginator = $this->paginateQuery($query, $request, 20, [10, 15, 20, 25, 50]);

        return Inertia::render('ActivityLogs/Index', [
            'activities' => $this->paginationPayload($paginator, fn (Activity $activity) => $this->transformActivity($activity)),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'events' => collect(['created', 'updated', 'deleted', 'restored'])
                    ->map(fn (string $event) => [
                        'value' => $event,
                        'label' => ActivityLogTranslator::eventLabel($event),
                    ])
                    ->all(),
                'subjectTypes' => $this->subjectTypeOptions(),
            ],
            'urls' => [
                'index' => route('activity-logs.index'),
                'show' => url('/activity-logs'),
            ],
        ]);
    }

    public function show(Activity $activity): Response
    {
        $this->authorize('view', $activity);

        $activity->load(['causer:id,name,last_name,email']);

        return Inertia::render('ActivityLogs/Show', [
            'activity' => $this->transformActivity($activity, detailed: true),
            'urls' => [
                'index' => route('activity-logs.index'),
            ],
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Spatie\Activitylog\Models\Activity>  $query
     */
    private function applySearch($query, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $query->where(function ($builder) use ($search) {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql' && mb_strlen($search) >= 3) {
                $builder->whereFullText('description', $search);
            } else {
                $builder->where('description', 'like', $search.'%')
                    ->orWhere('description', 'like', '% '.$search.'%');
            }

            $builder->orWhere('event', $search)
                ->orWhere('subject_type', 'like', '%'.$search);
        });
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function subjectTypeOptions(): array
    {
        return Cache::remember('activity_log.subject_types', 3600, function () {
            return Activity::query()
                ->select('subject_type')
                ->whereNotNull('subject_type')
                ->distinct()
                ->orderBy('subject_type')
                ->pluck('subject_type')
                ->map(fn (?string $type) => [
                    'value' => (string) $type,
                    'label' => ActivityLogTranslator::subjectTypeLabel($type),
                ])
                ->values()
                ->all();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function transformActivity(Activity $activity, bool $detailed = false): array
    {
        $payload = [
            'id' => $activity->id,
            'description' => $activity->description,
            'event' => $activity->event,
            'event_label' => ActivityLogTranslator::eventLabel((string) $activity->event),
            'log_name' => $activity->log_name,
            'subject_type' => ActivityLogTranslator::subjectTypeLabel($activity->subject_type),
            'subject_id' => $activity->subject_id,
            'causer' => $activity->causer ? [
                'id' => $activity->causer->id,
                'name' => trim(($activity->causer->name ?? '').' '.($activity->causer->last_name ?? '')),
                'email' => $activity->causer->email,
            ] : null,
            'created_at' => $activity->created_at
                ? verta($activity->created_at)->format('Y/m/d H:i')
                : null,
        ];

        if ($detailed) {
            $payload['properties'] = $activity->properties?->toArray() ?? [];
            $payload['subject_type_full'] = $activity->subject_type;
        }

        return $payload;
    }
}
