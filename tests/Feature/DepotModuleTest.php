<?php

namespace Tests\Feature;

use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotRequestItem;
use App\Models\DepotTransaction;
use App\Models\Medicine;
use App\Models\Tool;
use App\Models\User;
use App\Services\DepotRequestService;
use App\Services\DepotStockService;
use App\Support\DepotRequestAuthorization;
use App\Support\DepotRolePermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DepotModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_medicine_and_tool_stock_calculation(): void
    {
        $depot = Depot::create(['name' => 'Main', 'is_active' => true]);
        $medicine = Medicine::create(['name' => 'Paracetamol']);
        $tool = Tool::create(['name' => 'Syringe', 'code' => 'SYR-001', 'is_active' => true]);

        DepotTransaction::create([
            'depot_id' => $depot->id,
            'medicine_id' => $medicine->id,
            'type' => DepotTransaction::TYPE_STOCK_IN,
            'transaction_type' => 'in',
            'status' => DepotTransaction::STATUS_COMPLETED,
            'quantity' => 100,
            'transaction_date' => now()->toDateString(),
        ]);

        DepotTransaction::create([
            'depot_id' => $depot->id,
            'tool_id' => $tool->id,
            'type' => DepotTransaction::TYPE_STOCK_IN,
            'transaction_type' => 'in',
            'status' => DepotTransaction::STATUS_COMPLETED,
            'quantity' => 20,
            'transaction_date' => now()->toDateString(),
        ]);

        $service = app(DepotStockService::class);

        $this->assertSame(100, $service->availableMedicineStock($depot->id, $medicine->id));
        $this->assertSame(20, $service->availableToolStock($depot->id, $tool->id));
    }

    public function test_outbound_is_blocked_when_quantity_exceeds_available_stock(): void
    {
        $depot = Depot::create(['name' => 'Main', 'is_active' => true]);
        $tool = Tool::create(['name' => 'Forceps', 'code' => 'FOR-001', 'is_active' => true]);

        DepotTransaction::create([
            'depot_id' => $depot->id,
            'tool_id' => $tool->id,
            'type' => DepotTransaction::TYPE_STOCK_IN,
            'transaction_type' => 'in',
            'status' => DepotTransaction::STATUS_COMPLETED,
            'quantity' => 5,
            'transaction_date' => now()->toDateString(),
        ]);

        $service = app(DepotStockService::class);

        $this->expectException(ValidationException::class);
        $service->ensureAvailable(DepotTransaction::ITEM_TOOL, $depot->id, $tool->id, 10);
    }

    public function test_cancelled_transaction_does_not_affect_stock(): void
    {
        $depot = Depot::create(['name' => 'Main', 'is_active' => true]);
        $medicine = Medicine::create(['name' => 'Ibuprofen']);

        DepotTransaction::create([
            'depot_id' => $depot->id,
            'medicine_id' => $medicine->id,
            'type' => DepotTransaction::TYPE_STOCK_IN,
            'transaction_type' => 'in',
            'status' => DepotTransaction::STATUS_COMPLETED,
            'quantity' => 50,
            'transaction_date' => now()->toDateString(),
        ]);

        DepotTransaction::create([
            'depot_id' => $depot->id,
            'from_depot_id' => $depot->id,
            'medicine_id' => $medicine->id,
            'type' => DepotTransaction::TYPE_STOCK_OUT,
            'transaction_type' => 'out',
            'status' => DepotTransaction::STATUS_CANCELLED,
            'quantity' => 30,
            'transaction_date' => now()->toDateString(),
        ]);

        $service = app(DepotStockService::class);
        $this->assertSame(50, $service->availableMedicineStock($depot->id, $medicine->id));
    }

    public function test_request_approve_and_fulfill_creates_transfer_transaction(): void
    {
        $source = Depot::create(['name' => 'Base', 'is_active' => true, 'is_base' => true]);
        $child = Depot::create(['name' => 'Child', 'is_active' => true, 'parent_depot_id' => $source->id]);
        $medicine = Medicine::create(['name' => 'Amoxicillin']);

        DepotTransaction::create([
            'depot_id' => $source->id,
            'medicine_id' => $medicine->id,
            'type' => DepotTransaction::TYPE_STOCK_IN,
            'transaction_type' => 'in',
            'status' => DepotTransaction::STATUS_COMPLETED,
            'quantity' => 40,
            'transaction_date' => now()->toDateString(),
        ]);

        $depotRequest = DepotRequest::create([
            'requesting_depot_id' => $child->id,
            'source_depot_id' => $source->id,
            'status' => DepotRequest::STATUS_DRAFT,
        ]);

        DepotRequestItem::create([
            'depot_request_id' => $depotRequest->id,
            'medicine_id' => $medicine->id,
            'quantity' => 15,
            'sort_order' => 0,
        ]);

        $service = app(DepotRequestService::class);
        $service->submit($depotRequest);
        $service->approve($depotRequest->fresh());
        $service->fulfill($depotRequest->fresh());

        $depotRequest->refresh();
        $this->assertSame(DepotRequest::STATUS_FULFILLED, $depotRequest->status);
        $this->assertNotNull($depotRequest->items()->first()?->depot_transaction_id);

        $stockService = app(DepotStockService::class);
        $this->assertSame(25, $stockService->availableMedicineStock($source->id, $medicine->id));
        $this->assertSame(15, $stockService->availableMedicineStock($child->id, $medicine->id));
    }

    public function test_only_source_depot_users_can_process_submitted_requests(): void
    {
        $source = Depot::create(['name' => 'Source', 'is_active' => true, 'is_base' => true]);
        $requesting = Depot::create(['name' => 'Requesting', 'is_active' => true, 'parent_depot_id' => $source->id]);

        $requester = User::factory()->create();
        $processor = User::factory()->create();

        $requesting->addUser($requester->id, 'staff');
        $source->addUser($processor->id, 'staff');

        $depotRequest = DepotRequest::create([
            'requesting_depot_id' => $requesting->id,
            'source_depot_id' => $source->id,
            'status' => DepotRequest::STATUS_PENDING,
        ]);

        $this->assertFalse(DepotRequestAuthorization::canProcess(
            $requester,
            $depotRequest,
            DepotRolePermissions::ACTION_REQUEST_APPROVE,
        ));

        $this->assertTrue(DepotRequestAuthorization::canProcess(
            $processor,
            $depotRequest,
            DepotRolePermissions::ACTION_REQUEST_APPROVE,
        ));

        $this->assertTrue(DepotRequestAuthorization::canProcess(
            $processor,
            $depotRequest,
            DepotRolePermissions::ACTION_REQUEST_FULFILL,
        ));
    }
}
