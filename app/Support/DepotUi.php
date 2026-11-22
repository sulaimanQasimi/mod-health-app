<?php

namespace App\Support;

class DepotUi
{
    public const LOW_STOCK_THRESHOLD = 10;

    public static function statusBadgeClass(string $status): string
    {
        return match (true) {
            in_array($status, ['completed', 'fulfilled', 'approved'], true) => 'bg-success',
            in_array($status, ['cancelled', 'rejected'], true) => 'bg-danger',
            in_array($status, ['pending', 'draft'], true) => 'bg-warning text-dark',
            in_array($status, ['stock_out', 'depot_to_pharmacy'], true) => 'bg-danger',
            default => 'bg-info',
        };
    }

    public static function typeLabel(string $type): string
    {
        $translated = localize('global.depot.'.$type);

        if ($translated !== 'global.depot.'.$type) {
            return $translated;
        }

        return str_replace('_', ' ', $type);
    }

    public static function requestStatusLabel(string $status): string
    {
        $key = 'global.depot.request_status_'.$status;
        $translated = localize($key);

        if ($translated !== $key) {
            return $translated;
        }

        return str_replace('_', ' ', $status);
    }

    public static function stockLevel(int $quantity): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        }

        if ($quantity <= self::LOW_STOCK_THRESHOLD) {
            return 'low_stock';
        }

        return 'healthy';
    }

    public static function stockLevelBadgeClass(string $level): string
    {
        return match ($level) {
            'healthy' => 'bg-success',
            'low_stock' => 'bg-warning text-dark',
            default => 'bg-danger',
        };
    }

    public static function stockLevelLabel(string $level): string
    {
        return match ($level) {
            'healthy' => localize('global.in_stock'),
            'low_stock' => localize('global.low_stock'),
            default => localize('global.out_of_stock'),
        };
    }

    public static function stockBarClass(string $level): string
    {
        return match ($level) {
            'healthy' => 'bg-success',
            'low_stock' => 'bg-warning',
            default => 'bg-danger',
        };
    }
}
