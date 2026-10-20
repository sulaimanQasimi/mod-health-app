<?php

namespace App\Http\Controllers;

use App\Support\ActivityLogTranslator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Prune presets: delete logs older than this period.
     *
     * @var array<string, array{label_key: string, modify: string}>
     */
    public const PRUNE_PERIODS = [
        '1_week' => ['label_key' => 'prune_1_week', 'modify' => '-1 week'],
        '1_month' => ['label_key' => 'prune_1_month', 'modify' => '-1 month'],
        '3_months' => ['label_key' => 'prune_3_months', 'modify' => '-3 months'],
        '6_months' => ['label_key' => 'prune_6_months', 'modify' => '-6 months'],
        '1_year' => ['label_key' => 'prune_1_year', 'modify' => '-1 year'],
    ];

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

        $prunePeriods = collect(self::PRUNE_PERIODS)
            ->map(fn (array $period, string $key) => [
                'value' => $key,
                'label' => localize('activity_log.'.$period['label_key']),
            ])
            ->values()
            ->all();

        return view('pages.activity_logs.index', compact('activities', 'events', 'subjectTypes', 'prunePeriods'));
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

    public function prune(Request $request): RedirectResponse
    {
        $this->authorize('deleteAny', Activity::class);

        $validated = $request->validate([
            'period' => ['required', 'string', Rule::in(array_keys(self::PRUNE_PERIODS))],
        ]);

        $period = self::PRUNE_PERIODS[$validated['period']];
        $cutoff = Carbon::now()->modify($period['modify']);

        $deleted = Activity::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        Cache::forget('activity_log.subject_types');

        return redirect()
            ->route('activity-logs.index')
            ->with('success', str_replace(
                [':count', ':period'],
                [(string) $deleted, localize('activity_log.'.$period['label_key'])],
                localize('activity_log.prune_success')
            ));
    }
}
