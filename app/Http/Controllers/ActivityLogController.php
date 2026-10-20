<?php

namespace App\Http\Controllers;

use App\Support\ActivityLogTranslator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::query()
            ->with(['causer:id,name,last_name,email'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', $search.'%')
                    ->orWhere('description', 'like', '% '.$search.'%')
                    ->orWhere('event', $search)
                    ->orWhere('subject_type', 'like', '%'.$search)
                    ->orWhere('properties->ip', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->string('event')->toString());
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->string('subject_type')->toString());
        }

        $activities = $query->paginate(20)->appends($request->query());

        $events = collect(['created', 'updated', 'deleted', 'restored'])
            ->map(fn (string $event) => [
                'value' => $event,
                'label' => ActivityLogTranslator::eventLabel($event),
            ]);

        $subjectTypes = Cache::remember('activity_log.subject_types', 3600, function () {
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

        return view('pages.activity_logs.index', compact('activities', 'events', 'subjectTypes'));
    }

    public function show(Activity $activity): View
    {
        $this->authorize('view', $activity);

        $activity->load(['causer:id,name,last_name,email']);

        $properties = $activity->properties?->toArray() ?? [];
        $old = $properties['old'] ?? [];
        $attributes = $properties['attributes'] ?? [];
        $ip = $properties['ip'] ?? null;
        $userAgent = $properties['user_agent'] ?? null;
        $url = $properties['url'] ?? null;
        $method = $properties['method'] ?? null;

        $changeKeys = collect(array_unique(array_merge(array_keys($old), array_keys($attributes))))
            ->reject(fn ($key) => in_array($key, ['ip', 'user_agent', 'url', 'method'], true))
            ->values();

        return view('pages.activity_logs.show', compact(
            'activity',
            'properties',
            'old',
            'attributes',
            'changeKeys',
            'ip',
            'userAgent',
            'url',
            'method'
        ));
    }
}
