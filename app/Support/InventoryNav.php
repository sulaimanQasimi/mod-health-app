<?php

namespace App\Support;

final class InventoryNav
{
    /**
     * @return array<string, string>
     */
    public static function urls(): array
    {
        return [
            'medicines' => route('medicines.index'),
            'purchases' => route('purchases.index'),
            'suppliers' => route('suppliers.index'),
            'stocks' => route('stocks.index'),
            'movements' => route('stock-movements.index'),
            'transfers' => route('depot-transfers.index'),
            'depots' => route('depots.index'),
            'tools' => route('tools.index'),
        ];
    }
}
