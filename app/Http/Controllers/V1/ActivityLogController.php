<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Support\ActivityLogTranslator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'event', 'subject_type', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::query()
            ->with(['causer:id,name,last_name,email', 'subject'])
            ->latest('created_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('properties', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->string('event')->toString());
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%'.$request->string('subject_type')->toString());
        }

        $paginator = $this->paginateQuery($query, $request, 20);

        $subjectTypes = Activity::query()
            ->select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(fn (?string $type) => [
                'value' => $type,
                'label' => ActivityLogTranslator::subjectTypeLabel($type),
            ])
            ->values()
            ->all();

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
                'subjectTypes' => $subjectTypes,
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

        $activity->load(['causer:id,name,last_name,email', 'subject']);

        return Inertia::render('ActivityLogs/Show', [
            'activity' => $this->transformActivity($activity, detailed: true),
            'urls' => [
                'index' => route('activity-logs.index'),
            ],
        ]);
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
