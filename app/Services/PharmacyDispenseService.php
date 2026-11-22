<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\SaleItem;
use App\Support\PackQuantity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyDispenseService
{
    public function __construct(
        private readonly StockService $stocks,
        private readonly InvoiceService $invoices,
    ) {
    }

    public function deliverItem(PrescriptionItem $item, bool $delivered = true, ?int $packs = null, ?int $units = null): PrescriptionItem
    {
        $item->loadMissing([
            'prescription.appointment',
            'prescription.patient',
            'prescription.pharmacy',
            'selectedAlternative.medicine:id,name,units_per_pack,unit_id',
            'medicine:id,name,units_per_pack,unit_id',
            'saleItems',
            'batchAllocations',
        ]);

        if (! $delivered) {
            return tap($item)->update(['is_delivered' => false]);
        }

        if ($item->is_delivered || $item->saleItems->isNotEmpty()) {
            if (! $item->is_delivered) {
                $item->update(['is_delivered' => true]);
            }

            return $item->fresh(['saleItems.stock', 'medicine']);
        }

        return DB::transaction(function () use ($item, $packs, $units) {
            $prescription = $item->prescription;
            $pharmacy = $this->resolvePharmacy($prescription);
            $medicine = $this->resolveMedicine($item);
            $unitsPerPack = $medicine->unitsPerPack();

            // Sales packs default to 1 when not provided.
            $linePacks = $packs ?? 1;
            $lineUnits = $units ?? 0;

            if ($packs === null && $units === null) {
                $requestedUnits = $this->resolveQuantity($item);
                $split = PackQuantity::fromUnits($requestedUnits, $unitsPerPack);
                // Prefer packs=1 default when prescription amount is empty/1 unit and pack size is 1.
                if ($requestedUnits <= 1) {
                    $linePacks = 1;
                    $lineUnits = 0;
                } else {
                    $linePacks = $split['packs'];
                    $lineUnits = $split['units'];
                    if ($linePacks < 1 && $lineUnits < 1) {
                        $linePacks = 1;
                        $lineUnits = 0;
                    }
                }
            }

            $qtyUnits = PackQuantity::toUnits($linePacks, $lineUnits, $unitsPerPack);

            $soldLots = $this->stocks->sellFromPharmacy(
                $pharmacy,
                $medicine,
                $qtyUnits,
                $linePacks,
                $lineUnits,
                null,
                $item
            );

            $invoiceable = $this->resolveInvoiceable($prescription);
            $invoice = $this->invoices->openInvoiceFor($invoiceable, [
                'patient_id' => (int) ($prescription->patient_id ?? $invoiceable->getAttribute('patient_id')),
                'branch_id' => (int) ($prescription->branch_id ?? $invoiceable->getAttribute('branch_id')),
            ]);

            $medicineName = $medicine->name ?? '#'.$item->id;

            foreach ($soldLots as $lot) {
                $saleItem = SaleItem::query()->create([
                    'prescription_item_id' => $item->id,
                    'stock_id' => $lot['stock']->id,
                    'medicine_id' => $medicine->id,
                    'batch_number' => $lot['batch_number'],
                    'packs' => $lot['packs'],
                    'units' => $lot['units'],
                    'qty_units' => $lot['qty_units'],
                    'units_per_pack' => $lot['units_per_pack'],
                    'unit_sale_price' => $lot['unit_sale_price'],
                    'pack_price' => $lot['pack_price'],
                    'amount' => round($lot['unit_sale_price'] * $lot['qty_units'], 2),
                    'user_id' => auth()->id(),
                ]);

                $invoiceItem = $this->invoices->addCustomItem(
                    $invoice,
                    localize('global.pharmacy_medicine').' — '.$medicineName.' ('.$lot['batch_number'].')',
                    (float) $lot['unit_sale_price'],
                    (int) $lot['qty_units'],
                    $item,
                    InvoiceItem::TYPE_PHARMACY_MEDICINE
                );

                $saleItem->forceFill(['invoice_item_id' => $invoiceItem->id])->save();
            }

            $item->update(['is_delivered' => true]);

            return $item->fresh(['saleItems.stock', 'medicine']);
        });
    }

    public function deliverAll(Prescription $prescription): Prescription
    {
        $prescription->load([
            'prescriptionItems.selectedAlternative.medicine:id,name,units_per_pack,unit_id',
            'prescriptionItems.medicine:id,name,units_per_pack,unit_id',
            'prescriptionItems.saleItems',
            'appointment',
            'pharmacy',
        ]);

        foreach ($prescription->prescriptionItems as $item) {
            if ($item->selectedAlternative) {
                if ($item->selectedAlternative->is_delivered != '1') {
                    $this->deliverItem($item, true);
                    $item->selectedAlternative->update(['is_delivered' => '1']);
                }
                continue;
            }

            if (! $item->is_delivered) {
                $this->deliverItem($item, true);
            }
        }

        return $prescription->fresh(['prescriptionItems.saleItems']);
    }

    private function resolveMedicine(PrescriptionItem $item): Medicine
    {
        $medicine = $item->selectedAlternative?->medicine ?? $item->medicine;
        if (! $medicine) {
            throw ValidationException::withMessages([
                'medicine_id' => 'Medicine is required to dispense.',
            ]);
        }

        return $medicine;
    }

    private function resolvePharmacy(Prescription $prescription): Pharmacy
    {
        if ($prescription->pharmacy) {
            return $prescription->pharmacy;
        }

        $userPharmacy = auth()->user()?->activePharmacies()->first();
        if ($userPharmacy) {
            $prescription->forceFill(['pharmacy_id' => $userPharmacy->id])->save();

            return $userPharmacy;
        }

        throw ValidationException::withMessages([
            'pharmacy_id' => localize('global.pharmacy_required_for_dispense') ?: 'Pharmacy is required to dispense.',
        ]);
    }

    private function resolveQuantity(PrescriptionItem $item): int
    {
        $raw = preg_replace('/[^0-9]/', '', (string) ($item->amount ?? ''));
        $qty = (int) $raw;

        return $qty > 0 ? $qty : 1;
    }

    private function resolveInvoiceable(Prescription $prescription): Model
    {
        if ($prescription->appointment_id) {
            return Appointment::query()->findOrFail($prescription->appointment_id);
        }

        if ($prescription->hospitalization_id) {
            return Hospitalization::query()->findOrFail($prescription->hospitalization_id);
        }

        if ($prescription->i_c_u_id) {
            return ICU::query()->findOrFail($prescription->i_c_u_id);
        }

        return $prescription;
    }
}
