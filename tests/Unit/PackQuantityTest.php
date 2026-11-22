<?php

namespace Tests\Unit;

use App\Support\PackQuantity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PackQuantityTest extends TestCase
{
    public function test_converts_packs_and_units_to_base_units(): void
    {
        $this->assertSame(23, PackQuantity::toUnits(2, 3, 10));
        $this->assertSame(1, PackQuantity::toUnits(1, 0, 1));
    }

    public function test_splits_base_units_into_packs_and_units(): void
    {
        $this->assertSame(['packs' => 2, 'units' => 3], PackQuantity::fromUnits(23, 10));
    }

    public function test_rejects_zero_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PackQuantity::toUnits(0, 0, 10);
    }
}
