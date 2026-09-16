<?php

namespace App\Services;

use App\Support\ActivityLogTranslator;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\ActivityLogger;

class ModelActivityLogger
{
    public function log(Model $model, string $event): void
    {
        if (! config('activitylog.enabled', true)) {
            return;
        }

        if ($this->shouldIgnore($model)) {
            return;
        }

        $changes = $this->buildChanges($model, $event);

        if ($event === 'updated' && empty($changes['attributes'] ?? []) && empty($changes['old'] ?? [])) {
            return;
        }

        if ($event === 'created' && empty($changes['attributes'] ?? [])) {
            return;
        }

        if ($event === 'deleted' && empty($changes['old'] ?? [])) {
            return;
        }

        app(ActivityLogger::class)
            ->useLog(config('activitylog.default_log_name', 'default'))
            ->event($event)
            ->performedOn($model)
            ->withProperties($changes)
            ->log(ActivityLogTranslator::eventDescription($model, $event));
    }

    private function shouldIgnore(Model $model): bool
    {
        $ignored = config('activitylog.ignored_models', []);

        foreach ($ignored as $ignoredClass) {
            if ($model instanceof $ignoredClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildChanges(Model $model, string $event): array
    {
        if ($event === 'deleted') {
            return ['old' => $this->filterAttributes($model->getAttributes())];
        }

        if ($event === 'updated') {
            $dirty = $this->filterAttributes($model->getChanges());
            $old = [];

            foreach (array_keys($dirty) as $key) {
                $old[$key] = $this->normalizeValue($model->getOriginal($key));
            }

            return [
                'attributes' => $dirty,
                'old' => $old,
            ];
        }

        // created / restored
        return ['attributes' => $this->filterAttributes($model->getAttributes())];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function filterAttributes(array $attributes): array
    {
        $excluded = config('activitylog.default_except_attributes', [
            'password',
            'remember_token',
        ]);

        return collect($attributes)
            ->except($excluded)
            ->map(fn ($value) => $this->normalizeValue($value))
            ->all();
    }

    private function normalizeValue(mixed $value): mixed
    {
        $maxLength = (int) config('activitylog.max_attribute_length', 500);

        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($encoded === false) {
                return '[unserializable]';
            }

            return $this->truncate($encoded, $maxLength);
        }

        if (is_string($value)) {
            return $this->truncate($value, $maxLength);
        }

        return $value;
    }

    private function truncate(string $value, int $maxLength): string
    {
        if ($maxLength <= 0 || mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength).'…';
    }
}
