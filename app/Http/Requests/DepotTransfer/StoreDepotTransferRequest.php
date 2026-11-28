<?php

namespace App\Http\Requests\DepotTransfer;

use App\Models\Stock;
use App\Services\StockService;
use App\Support\PackQuantity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepotTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_depot_id' => ['required', 'integer', 'exists:depots,id'],
            'pharmacy_id' => ['nullable', 'integer', 'exists:pharmacies,id', 'required_without:destination_depot_id'],
            'destination_depot_id' => ['nullable', 'integer', 'exists:depots,id', 'different:source_depot_id', 'required_without:pharmacy_id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'items.*.batch_number' => ['required', 'string', 'max:100'],
            'items.*.packs' => ['nullable', 'integer', 'min:0'],
            'items.*.units' => ['nullable', 'integer', 'min:0'],
            'items.*.units_per_pack' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $depotId = (int) $this->input('source_depot_id');
            $items = $this->input('items', []);
            /** @var StockService $stocks */
            $stocks = app(StockService::class);

            foreach ($items as $index => $row) {
                $medicineId = (int) ($row['medicine_id'] ?? 0);
                $batchNumber = trim((string) ($row['batch_number'] ?? ''));
                $unitsPerPack = max(1, (int) ($row['units_per_pack'] ?? 1));
                $packs = (int) ($row['packs'] ?? 0);
                $units = (int) ($row['units'] ?? 0);

                try {
                    $qtyUnits = PackQuantity::toUnits($packs, $units, $unitsPerPack);
                } catch (\InvalidArgumentException $e) {
                    $validator->errors()->add("items.{$index}.packs", $e->getMessage());

                    continue;
                }

                $available = $stocks->availableForBatch(
                    Stock::LOCATION_DEPOT,
                    $depotId,
                    $medicineId,
                    $batchNumber,
                );

                if ($available < 1) {
                    $validator->errors()->add(
                        "items.{$index}.batch_number",
                        "Batch [{$batchNumber}] has no stock in the selected depot."
                    );

                    continue;
                }

                if ($qtyUnits > $available) {
                    $validator->errors()->add(
                        "items.{$index}.packs",
                        "Requested {$qtyUnits} units exceeds available {$available} for batch [{$batchNumber}]."
                    );
                }
            }
        });
    }
}
