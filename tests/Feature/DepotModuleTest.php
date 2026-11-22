<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @deprecated Replaced by StandardStockFlowTest (purchase → depot → pharmacy → sale).
 */
class DepotModuleTest extends TestCase
{
    #[Test]
    public function legacy_depot_module_replaced_by_standard_stock_flow(): void
    {
        $this->assertTrue(class_exists(StandardStockFlowTest::class));
    }
}
