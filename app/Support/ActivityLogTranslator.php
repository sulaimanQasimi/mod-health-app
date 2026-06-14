<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ActivityLogTranslator
{
    public static function locale(): string
    {
        return 'dr';
    }

    public static function modelLabel(Model $model): string
    {
        return self::basenameLabel(class_basename($model));
    }

    public static function eventDescription(Model $model, string $event): string
    {
        $key = "activity_log.events.{$event}";
        $template = Lang::get($key, [], self::locale());

        if ($template === $key) {
            $template = Lang::get('activity_log.events.default', [], self::locale());
        }

        return str_replace(
            [':model', ':id'],
            [self::modelLabel($model), (string) $model->getKey()],
            $template
        );
    }

    public static function eventLabel(string $event): string
    {
        $key = "activity_log.event_labels.{$event}";
        $label = Lang::get($key, [], self::locale());

        return $label === $key ? $event : $label;
    }

    public static function subjectTypeLabel(?string $subjectType): string
    {
        if (! $subjectType) {
            return '';
        }

        return self::basenameLabel(class_basename($subjectType));
    }

    private static function basenameLabel(string $basename): string
    {
        $activityKey = "activity_log.models.{$basename}";
        $label = Lang::get($activityKey, [], self::locale());

        if ($label !== $activityKey) {
            return $label;
        }

        $globalKey = 'global.'.Str::snake($basename);
        $globalLabel = Lang::get($globalKey, [], self::locale());

        if ($globalLabel !== $globalKey) {
            return $globalLabel;
        }

        return Lang::get('activity_log.model_fallback', ['name' => $basename], self::locale());
    }
}
