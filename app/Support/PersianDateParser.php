<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;
use Hekmatinasser\Verta\Verta;
use Illuminate\Validation\ValidationException;

final class PersianDateParser
{
    public static function parseDate(?string $value, string $errorKey = 'date'): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::instance(Verta::parse(trim($value))->datetime());
        } catch (\Throwable) {
            try {
                return Carbon::parse($value, config('app.timezone'));
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    $errorKey => localize('global.expires_date_required'),
                ]);
            }
        }
    }

    public static function parseDateTime(
        ?string $date,
        ?string $time,
        string $defaultTime = '00:00',
        string $dateErrorKey = 'date',
        string $timeErrorKey = 'time',
    ): ?Carbon {
        $parsedDate = self::parseDate($date, $dateErrorKey);
        if ($parsedDate === null) {
            return null;
        }

        $timeValue = ($time === null || trim($time) === '') ? $defaultTime : trim($time);
        if (! preg_match('/^\d{2}:\d{2}$/', $timeValue)) {
            throw ValidationException::withMessages([
                $timeErrorKey => localize('global.expires_time_invalid'),
            ]);
        }

        return Carbon::parse($parsedDate->format('Y-m-d').' '.$timeValue.':00', config('app.timezone'));
    }

    /**
     * Prefer split Persian date + time; fall back to legacy single datetime string (Blade datetime-local).
     */
    public static function parseDateTimeOrLegacy(
        ?string $date,
        ?string $time,
        ?string $legacyDateTime,
        string $defaultTime = '00:00',
        string $dateErrorKey = 'date',
        string $timeErrorKey = 'time',
    ): ?Carbon {
        if ($date !== null && trim($date) !== '') {
            return self::parseDateTime($date, $time, $defaultTime, $dateErrorKey, $timeErrorKey);
        }

        if ($legacyDateTime === null || trim($legacyDateTime) === '') {
            return null;
        }

        try {
            return Carbon::parse($legacyDateTime, config('app.timezone'));
        } catch (\Throwable) {
            return self::parseDateTime($legacyDateTime, null, $defaultTime, $dateErrorKey, $timeErrorKey);
        }
    }

    public static function queryDate(?string $value): ?DateTimeInterface
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Verta::parse(trim($value))->datetime();
        } catch (\Throwable) {
            return Carbon::parse($value, config('app.timezone'));
        }
    }
}
