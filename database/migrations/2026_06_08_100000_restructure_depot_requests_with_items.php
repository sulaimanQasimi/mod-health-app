<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depot_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_request_id')->constrained('depot_requests')->cascadeOnDelete();
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->foreignId('tool_id')->nullable()->constrained('tools')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('batch_number')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('depot_transaction_id')->nullable()->constrained('depot_transactions')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('depot_transactions', function (Blueprint $table) {
            $table->foreignId('depot_request_id')->nullable()->after('to_depot_id')->constrained('depot_requests')->nullOnDelete();
        });

        $requests = DB::table('depot_requests')->get();

        foreach ($requests as $request) {
            if ($request->medicine_id || $request->tool_id) {
                $itemId = DB::table('depot_request_items')->insertGetId([
                    'depot_request_id' => $request->id,
                    'medicine_id' => $request->medicine_id,
                    'tool_id' => $request->tool_id,
                    'unit_id' => $request->unit_id,
                    'quantity' => $request->quantity,
                    'batch_number' => $request->batch_number,
                    'sort_order' => 0,
                    'depot_transaction_id' => $request->depot_transaction_id,
                    'created_at' => $request->created_at,
                    'updated_at' => $request->updated_at,
                ]);

                if ($request->depot_transaction_id) {
                    DB::table('depot_transactions')
                        ->where('id', $request->depot_transaction_id)
                        ->update(['depot_request_id' => $request->id]);
                }

                unset($itemId);
            }
        }

        Schema::table('depot_requests', function (Blueprint $table) {
            $table->dropForeign(['medicine_id']);
            $table->dropForeign(['tool_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['depot_transaction_id']);
            $table->dropColumn([
                'medicine_id',
                'tool_id',
                'unit_id',
                'quantity',
                'batch_number',
                'depot_transaction_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('depot_requests', function (Blueprint $table) {
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->foreignId('tool_id')->nullable()->constrained('tools')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('batch_number')->nullable();
            $table->foreignId('depot_transaction_id')->nullable()->constrained('depot_transactions')->nullOnDelete();
        });

        $items = DB::table('depot_request_items')->orderBy('sort_order')->get()->groupBy('depot_request_id');

        foreach ($items as $requestId => $requestItems) {
            $first = $requestItems->first();

            DB::table('depot_requests')->where('id', $requestId)->update([
                'medicine_id' => $first->medicine_id,
                'tool_id' => $first->tool_id,
                'unit_id' => $first->unit_id,
                'quantity' => $first->quantity,
                'batch_number' => $first->batch_number,
                'depot_transaction_id' => $first->depot_transaction_id,
            ]);
        }

        Schema::table('depot_transactions', function (Blueprint $table) {
            $table->dropForeign(['depot_request_id']);
            $table->dropColumn('depot_request_id');
        });

        Schema::dropIfExists('depot_request_items');
    }
};
