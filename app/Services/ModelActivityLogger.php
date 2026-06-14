<?php

namespace App\Services;

use App\Support\ActivityLogTranslator;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\ActivityLogger;

class ModelActivityLogger
{
    private const EXCLUDED_ATTRIBUTES = [
        'password',
        'remember_token',
    ];

    public function log(Model $model, string $event): void
    {
        if (! config('activitylog.enabled', true)) {
            return;
        }

        $changes = $this->buildChanges($model, $event);

        if ($event === 'updated' && empty($changes['attributes'] ?? []) && empty($changes['old'] ?? [])) {
            return;
        }

        app(ActivityLogger::class)
            ->useLog('default')
            ->event($event)
            ->performedOn($model)
            ->withProperties($changes)
            ->log(ActivityLogTranslator::eventDescription($model, $event));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildChanges(Model $model, string $event): array
    {
        $attributes = $this->filterAttributes($model->getAttributes());

        if ($event === 'deleted') {
            return ['old' => $attributes];
        }

        if ($event === 'updated') {
            $dirty = $this->filterAttributes($model->getChanges());
            $old = [];

            foreach (array_keys($dirty) as $key) {
                $old[$key] = $model->getOriginal($key);
            }

            return [
                'attributes' => $dirty,
                'old' => $old,
            ];
        }

        return ['attributes' => $attributes];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function filterAttributes(array $attributes): array
    {
        return collect($attributes)
            ->except(self::EXCLUDED_ATTRIBUTES)
            ->all();
    }
}
