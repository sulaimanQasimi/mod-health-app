<?php

namespace App\Support;

use InvalidArgumentException;

final class PackQuantity
{
    public static function toUnits(int $packs, int $units, int $unitsPerPack = 1): int
    {
        $unitsPerPack = max(1, $unitsPerPack);
        $packs = max(0, $packs);
        $units = max(0, $units);
        $total = ($packs * $unitsPerPack) + $units;

        if ($total < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1 unit.');
        }

        return $total;
    }

    /**
     * @return array{packs: int, units: int}
     */
    public static function fromUnits(int $qtyUnits, int $unitsPerPack = 1): array
    {
        $unitsPerPack = max(1, $unitsPerPack);
        $qtyUnits = max(0, $qtyUnits);

        return [
            'packs' => intdiv($qtyUnits, $unitsPerPack),
            'units' => $qtyUnits % $unitsPerPack,
        ];
    }
}
