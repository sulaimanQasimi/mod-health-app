<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\InvoiceItem;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyDispenseService
{
    public function __construct(
        private readonly StockBatchService $batches,
        private readonly InvoiceService $invoices,
    ) {
    }

    public function deliverItem(PrescriptionItem $item, bool $delivered = true): PrescriptionItem
    {
        $item->loadMissing([
            'prescription.appointment',
            'prescription.patient',
            'prescription.pharmacy',
            'selectedAlternative.medicine:id,name',
            'medicine:id,name',
            'batchAllocations',
        ]);

        if (! $delivered) {
            return tap($item)->update(['is_delivered' => false]);
        }

        if ($item->is_delivered || $item->batchAllocations->isNotEmpty()) {
            if (! $item->is_delivered) {
                $item->update(['is_delivered' => true]);
            }

            return $item->fresh(['batchAllocations.stockBatch', 'medicine']);
        }

        return DB::transaction(function () use ($item) {
            $prescription = $item->prescription;
            $pharmacy = $this->resolvePharmacy($prescription);

            $quantity = $this->resolveQuantity($item);
            $allocations = $this->batches->allocatePrescriptionItem($pharmacy, $item, $quantity);

            $invoiceable = $this->resolveInvoiceable($prescription);
            $invoice = $this->invoices->openInvoiceFor($invoiceable, [
                'patient_id' => (int) ($prescription->patient_id ?? $invoiceable->getAttribute('patient_id')),
                'branch_id' => (int) ($prescription->branch_id ?? $invoiceable->getAttribute('branch_id')),
            ]);

            $medicineName = $item->selectedAlternative?->medicine?->name
                ?? $item->medicine?->name
                ?? '#'.$item->id;

            foreach ($allocations as $allocation) {
                $invoiceItem = $this->invoices->addCustomItem(
                    $invoice,
                    localize('global.pharmacy_medicine').' — '.$medicineName.' ('.$allocation->stockBatch?->batch_code.')',
                    (float) $allocation->unit_sale_price,
                    (int) $allocation->quantity,
                    $item,
                    InvoiceItem::TYPE_PHARMACY_MEDICINE
                );

                $allocation->forceFill(['invoice_item_id' => $invoiceItem->id])->save();
            }

            $item->update(['is_delivered' => true]);

            return $item->fresh(['batchAllocations.stockBatch', 'medicine']);
        });
    }

    public function deliverAll(Prescription $prescription): Prescription
    {
        $prescription->load([
            'prescriptionItems.selectedAlternative.medicine:id,name',
            'prescriptionItems.medicine:id,name',
            'prescriptionItems.batchAllocations',
            'appointment',
            'pharmacy',
        ]);

        foreach ($prescription->prescriptionItems as $item) {
            if ($item->selectedAlternative) {
                if ($item->selectedAlternative->is_delivered != '1') {
                    // Alternative delivery still flags the alt row; stock/invoice use the parent item medicine override.
                    $this->deliverItem($item, true);
                    $item->selectedAlternative->update(['is_delivered' => '1']);
                }
                continue;
            }

            if (! $item->is_delivered) {
                $this->deliverItem($item, true);
            }
        }

        return $prescription->fresh(['prescriptionItems.batchAllocations']);
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

        // Fallback invoiceable: prescription itself (morphable invoices already support any model).
        return $prescription;
    }
}
