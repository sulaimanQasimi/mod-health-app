<?php

namespace Tests\Feature;

use App\Models\Depot;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\User;
use App\Services\DepotTransferService;
use App\Services\PharmacyDispenseService;
use App\Services\PurchaseService;
use App\Services\StockService;
use App\Support\PackQuantity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StandardStockFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pack_quantity_conversion(): void
    {
        $this->assertSame(23, PackQuantity::toUnits(2, 3, 10));
        $this->assertSame(['packs' => 2, 'units' => 3], PackQuantity::fromUnits(23, 10));
    }

    public function test_purchase_receive_creates_depot_stock_with_prices_and_unique_batch(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$depot, $medicine] = $this->seedDepotMedicine();

        $purchase = app(PurchaseService::class)->create([
            'depot_id' => $depot->id,
            'supplier' => 'Supplier A',
            'purchase_date' => now()->toDateString(),
        ], [[
            'medicine_id' => $medicine->id,
            'batch_number' => '0001',
            'expiry_date' => now()->addYear()->toDateString(),
            'packs' => 2,
            'units' => 3,
            'units_per_pack' => 10,
            'purchase_price' => 100,
            'unit_sale_price' => 5,
            'pack_price' => 50,
        ]]);

        $this->assertSame(Purchase::STATUS_DRAFT, $purchase->status);
        $this->assertSame(23, $purchase->items->first()->qty_units);

        $received = app(PurchaseService::class)->receive($purchase);

        $this->assertSame(Purchase::STATUS_RECEIVED, $received->status);

        $stock = Stock::query()->forDepot($depot->id)->forBatch('0001')->first();
        $this->assertNotNull($stock);
        $this->assertSame(23, $stock->qty_on_hand);
        $this->assertSame(0, $stock->qty_sold);
        $this->assertSame(5.0, (float) $stock->unit_sale_price);
        $this->assertSame(50.0, (float) $stock->pack_price);
        $this->assertSame(100.0, (float) $stock->purchase_price);
    }

    public function test_duplicate_batch_number_on_purchase_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$depot, $medicine] = $this->seedDepotMedicine();

        PurchaseItem::query()->create([
            'purchase_id' => Purchase::query()->create([
                'purchase_number' => 'PO-TEST-1',
                'depot_id' => $depot->id,
                'purchase_date' => now()->toDateString(),
                'status' => Purchase::STATUS_DRAFT,
            ])->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'DUP-1',
            'packs' => 1,
            'units' => 0,
            'qty_units' => 1,
            'units_per_pack' => 1,
            'purchase_price' => 0,
            'unit_sale_price' => 0,
            'pack_price' => 0,
        ]);

        $this->expectException(ValidationException::class);

        app(PurchaseService::class)->create([
            'depot_id' => $depot->id,
            'purchase_date' => now()->toDateString(),
        ], [[
            'medicine_id' => $medicine->id,
            'batch_number' => 'DUP-1',
            'packs' => 1,
            'units' => 0,
            'units_per_pack' => 1,
        ]]);
    }

    public function test_same_batch_across_depots_pharmacy_and_sold_in_one_stocks_table(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $unit = Unit::query()->create(['name' => 'Tablet', 'symbol' => 'tab', 'is_active' => true]);
        $medicine = Medicine::query()->create([
            'name' => 'Amoxicillin',
            'unit_id' => $unit->id,
            'units_per_pack' => 10,
        ]);

        $depotA = Depot::query()->create(['name' => 'Depot A', 'is_active' => true]);
        $depotB = Depot::query()->create(['name' => 'Depot B', 'is_active' => true]);
        $pharmacy = Pharmacy::query()->create(['name' => 'Pharmacy X']);

        Stock::query()->create([
            'batch_number' => '0001',
            'medicine_id' => $medicine->id,
            'unit_id' => $unit->id,
            'units_per_pack' => 10,
            'location_type' => Stock::LOCATION_DEPOT,
            'location_id' => $depotA->id,
            'qty_on_hand' => 30,
            'qty_sold' => 0,
            'unit_sale_price' => 2,
            'pack_price' => 20,
        ]);
        Stock::query()->create([
            'batch_number' => '0001',
            'medicine_id' => $medicine->id,
            'unit_id' => $unit->id,
            'units_per_pack' => 10,
            'location_type' => Stock::LOCATION_DEPOT,
            'location_id' => $depotB->id,
            'qty_on_hand' => 60,
            'qty_sold' => 0,
            'unit_sale_price' => 2,
            'pack_price' => 20,
        ]);
        Stock::query()->create([
            'batch_number' => '0001',
            'medicine_id' => $medicine->id,
            'unit_id' => $unit->id,
            'units_per_pack' => 10,
            'location_type' => Stock::LOCATION_PHARMACY,
            'location_id' => $pharmacy->id,
            'qty_on_hand' => 90,
            'qty_sold' => 98,
            'unit_sale_price' => 2,
            'pack_price' => 20,
        ]);

        $rows = app(StockService::class)->listByBatchNumber('0001');

        $this->assertCount(3, $rows);
        $this->assertSame(30, (int) $rows->firstWhere('location_id', $depotA->id)->qty_on_hand);
        $this->assertSame(60, (int) $rows->firstWhere('location_id', $depotB->id)->qty_on_hand);
        $pharmacyRow = $rows->first(
            fn (Stock $s) => $s->location_type === Stock::LOCATION_PHARMACY && (int) $s->location_id === (int) $pharmacy->id
        );
        $this->assertSame(90, (int) $pharmacyRow->qty_on_hand);
        $this->assertSame(98, (int) $pharmacyRow->qty_sold);
    }

    public function test_depot_to_pharmacy_transfer_moves_same_batch_and_keeps_prices(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$depot, $medicine] = $this->seedDepotMedicine(unitsPerPack: 10);
        $pharmacy = Pharmacy::query()->create(['name' => 'Main Pharmacy']);

        Stock::query()->create([
            'batch_number' => 'BATCH-9',
            'medicine_id' => $medicine->id,
            'unit_id' => $medicine->unit_id,
            'units_per_pack' => 10,
            'location_type' => Stock::LOCATION_DEPOT,
            'location_id' => $depot->id,
            'qty_on_hand' => 50,
            'qty_sold' => 0,
            'purchase_price' => 10,
            'unit_sale_price' => 3,
            'pack_price' => 30,
        ]);

        $transfer = app(DepotTransferService::class)->create([
            'source_depot_id' => $depot->id,
            'pharmacy_id' => $pharmacy->id,
        ], [[
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-9',
            'packs' => 1,
            'units' => 5,
            'units_per_pack' => 10,
        ]]);

        app(DepotTransferService::class)->submit($transfer);
        app(DepotTransferService::class)->approve($transfer);
        app(DepotTransferService::class)->fulfill($transfer->fresh());

        $depotStock = Stock::query()->forDepot($depot->id)->forBatch('BATCH-9')->first();
        $pharmacyStock = Stock::query()->forPharmacy($pharmacy->id)->forBatch('BATCH-9')->first();

        $this->assertSame(35, (int) $depotStock->qty_on_hand); // 50 - 15
        $this->assertSame(15, (int) $pharmacyStock->qty_on_hand);
        $this->assertSame(3.0, (float) $pharmacyStock->unit_sale_price);
        $this->assertSame(30.0, (float) $pharmacyStock->pack_price);
    }

    public function test_sale_with_default_packs_one_updates_qty_sold(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $unit = Unit::query()->create(['name' => 'Capsule', 'symbol' => 'cap', 'is_active' => true]);
        $medicine = Medicine::query()->create([
            'name' => 'Vitamin C',
            'unit_id' => $unit->id,
            'units_per_pack' => 1,
        ]);
        $pharmacy = Pharmacy::query()->create(['name' => 'Clinic Pharmacy']);

        Stock::query()->create([
            'batch_number' => 'SALE-1',
            'medicine_id' => $medicine->id,
            'unit_id' => $unit->id,
            'units_per_pack' => 1,
            'location_type' => Stock::LOCATION_PHARMACY,
            'location_id' => $pharmacy->id,
            'qty_on_hand' => 20,
            'qty_sold' => 0,
            'unit_sale_price' => 4,
            'pack_price' => 4,
        ]);

        $sold = app(StockService::class)->sellFromPharmacy(
            $pharmacy,
            $medicine,
            PackQuantity::toUnits(1, 0, 1), // default packs=1
            1,
            0,
            'SALE-1'
        );

        $this->assertCount(1, $sold);
        $stock = Stock::query()->forPharmacy($pharmacy->id)->forBatch('SALE-1')->first();
        $this->assertSame(19, (int) $stock->qty_on_hand);
        $this->assertSame(1, (int) $stock->qty_sold);
    }

    /**
     * @return array{0: Depot, 1: Medicine}
     */
    private function seedDepotMedicine(int $unitsPerPack = 1): array
    {
        $unit = Unit::query()->create(['name' => 'Unit', 'symbol' => 'u', 'is_active' => true]);
        $medicine = Medicine::query()->create([
            'name' => 'Paracetamol',
            'unit_id' => $unit->id,
            'units_per_pack' => $unitsPerPack,
        ]);
        $depot = Depot::query()->create(['name' => 'Main Depot', 'is_active' => true]);

        return [$depot, $medicine];
    }
}
