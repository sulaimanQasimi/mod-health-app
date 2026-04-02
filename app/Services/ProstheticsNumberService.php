<?php

namespace App\Services;

use App\Models\ProstheticCase;
use App\Models\ProstheticReferral;
use App\Models\ProstheticWorkOrder;

class ProstheticsNumberService
{
    public static function nextReferralNumber(): string
    {
        $y = now()->year;
        $n = (int) (ProstheticReferral::query()->max('id') ?? 0) + 1;

        return sprintf('REF-%d-%05d', $y, $n);
    }

    public static function nextCaseNumber(): string
    {
        $y = now()->year;
        $n = (int) (ProstheticCase::query()->max('id') ?? 0) + 1;

        return sprintf('PO-%d-%05d', $y, $n);
    }

    public static function nextWorkOrderNumber(): string
    {
        $y = now()->year;
        $n = (int) (ProstheticWorkOrder::query()->max('id') ?? 0) + 1;

        return sprintf('WO-%d-%05d', $y, $n);
    }
}
