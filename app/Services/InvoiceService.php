<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabType;
use App\Models\PatientTestRegistration;
use App\Models\Payment;
use App\Models\PaymentReceipt;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceService
{
    /**
     * Ensure an open (draft/issued) invoice exists for any billable parent (appointment, hospitalization, …).
     *
     * @param  array{patient_id:int, branch_id:int}  $context
     */
    public function openInvoiceFor(Model $invoiceable, array $context): Invoice
    {
        $invoice = Invoice::query()
            ->where('invoiceable_type', $invoiceable->getMorphClass())
            ->where('invoiceable_id', $invoiceable->getKey())
            ->whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED])
            ->latest('id')
            ->first();

        if ($invoice) {
            return $invoice;
        }

        return Invoice::create([
            'patient_id' => $context['patient_id'],
            'invoiceable_type' => $invoiceable->getMorphClass(),
            'invoiceable_id' => $invoiceable->getKey(),
            'branch_id' => $context['branch_id'],
            'status' => Invoice::STATUS_DRAFT,
            'subtotal' => 0,
            'total' => 0,
            'paid_amount' => 0,
        ]);
    }

    public function openInvoiceForAppointment(Appointment $appointment): Invoice
    {
        return $this->openInvoiceFor($appointment, [
            'patient_id' => (int) $appointment->patient_id,
            'branch_id' => (int) $appointment->branch_id,
        ]);
    }

    /**
     * Add / refresh appointment department fee line when appointment is created or department changes.
     */
    public function syncAppointmentFee(Appointment $appointment): Invoice
    {
        return DB::transaction(function () use ($appointment) {
            $invoice = $this->openInvoiceForAppointment($appointment);
            $price = max(0, (float) ($appointment->price ?? 0));

            $item = $invoice->items()
                ->where('item_type', InvoiceItem::TYPE_APPOINTMENT_FEE)
                ->where('billable_type', $appointment->getMorphClass())
                ->where('billable_id', $appointment->getKey())
                ->first();

            if (! $appointment->relationLoaded('department')) {
                $appointment->load('department:id,name');
            }
            $departmentName = $appointment->department?->name ?? '#'.$appointment->id;
            $description = localize('global.appointment_fee').' — '.$departmentName;

            if ($price <= 0) {
                if ($item && $item->payment_status === InvoiceItem::PAYMENT_UNPAID) {
                    $item->delete();
                    $invoice->recalculateTotals();
                }

                return $invoice->fresh(['items']);
            }

            if ($item && $item->payment_status === InvoiceItem::PAYMENT_PAID) {
                return $invoice;
            }

            $payload = [
                'item_type' => InvoiceItem::TYPE_APPOINTMENT_FEE,
                'description' => $description,
                'unit_price' => $price,
                'quantity' => 1,
                'amount' => $price,
                'billable_type' => $appointment->getMorphClass(),
                'billable_id' => $appointment->getKey(),
                'payment_status' => InvoiceItem::PAYMENT_UNPAID,
            ];

            if ($item) {
                $item->update($payload);
            } else {
                $invoice->items()->create($payload);
            }

            $invoice->recalculateTotals();

            return $invoice->fresh(['items']);
        });
    }

    /**
     * Add a lab test charge and keep registration unpaid until invoice is paid.
     * Invoice parent is the registration's testable (appointment today; hospitalization/ICU later).
     */
    public function addLabTestCharge(PatientTestRegistration $registration, ?LabType $labType = null): Invoice
    {
        return DB::transaction(function () use ($registration, $labType) {
            $labType = $labType ?: $registration->labType;
            $invoiceable = $registration->testable;

            if (! $invoiceable instanceof Model) {
                throw new InvalidArgumentException('Lab registration must belong to a billable parent.');
            }

            $patientId = (int) ($invoiceable->getAttribute('patient_id') ?? 0);
            $branchId = (int) ($invoiceable->getAttribute('branch_id') ?? $registration->branch_id ?? 0);

            if ($patientId <= 0 || $branchId <= 0) {
                throw new InvalidArgumentException('Lab registration parent is missing patient or branch.');
            }

            $invoice = $this->openInvoiceFor($invoiceable, [
                'patient_id' => $patientId,
                'branch_id' => $branchId,
            ]);
            $price = max(0, (float) ($labType?->price ?? 0));

            $exists = $invoice->items()
                ->where('billable_type', $registration->getMorphClass())
                ->where('billable_id', $registration->getKey())
                ->exists();

            if (! $exists) {
                $invoice->items()->create([
                    'item_type' => InvoiceItem::TYPE_LAB_TEST,
                    'description' => localize('global.lab_test').' — '.($labType?->name ?? '#'.$registration->id),
                    'unit_price' => $price,
                    'quantity' => 1,
                    'amount' => $price,
                    'billable_type' => $registration->getMorphClass(),
                    'billable_id' => $registration->getKey(),
                    'lab_type_id' => $labType?->id,
                    'payment_status' => InvoiceItem::PAYMENT_UNPAID,
                ]);
                $invoice->recalculateTotals();
            }

            $registration->forceFill([
                'payment_status' => InvoiceItem::PAYMENT_UNPAID,
            ])->save();

            return $invoice->fresh(['items']);
        });
    }

    /**
     * Generic line for future billables (hospitalization extras, procedures, …).
     */
    public function addCustomItem(
        Invoice $invoice,
        string $description,
        float $unitPrice,
        int $quantity = 1,
        ?Model $billable = null,
        string $itemType = InvoiceItem::TYPE_CUSTOM
    ): InvoiceItem {
        $unitPrice = max(0, $unitPrice);
        $quantity = max(1, $quantity);

        $item = $invoice->items()->create([
            'item_type' => $itemType,
            'description' => $description,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'amount' => $unitPrice * $quantity,
            'billable_type' => $billable?->getMorphClass(),
            'billable_id' => $billable?->getKey(),
            'payment_status' => InvoiceItem::PAYMENT_UNPAID,
        ]);

        $invoice->recalculateTotals();

        return $item;
    }

    public function issue(Invoice $invoice): Invoice
    {
        if ($invoice->status === Invoice::STATUS_CANCELLED) {
            throw new InvalidArgumentException(localize('global.invoice_cancelled'));
        }

        if ($invoice->items()->count() === 0) {
            throw new InvalidArgumentException(localize('global.invoice_has_no_items'));
        }

        if ($invoice->status === Invoice::STATUS_PAID) {
            return $invoice;
        }

        $invoice->forceFill([
            'status' => Invoice::STATUS_ISSUED,
            'issued_at' => $invoice->issued_at ?? now(),
        ])->save();

        return $invoice->fresh();
    }

    /**
     * Mark invoice fully paid, unlock linked registrations, create UUID receipt.
     */
    public function markPaid(Invoice $invoice, array $data = []): PaymentReceipt
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->refresh();

            if ($invoice->status === Invoice::STATUS_CANCELLED) {
                throw new InvalidArgumentException(localize('global.invoice_cancelled'));
            }

            if ($invoice->items()->count() === 0) {
                throw new InvalidArgumentException(localize('global.invoice_has_no_items'));
            }

            if ($invoice->status === Invoice::STATUS_DRAFT) {
                $this->issue($invoice);
                $invoice->refresh();
            }

            $amount = isset($data['amount'])
                ? max(0, (float) $data['amount'])
                : (float) $invoice->total;

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $data['method'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
                'paid_at' => now(),
                'paid_by' => Auth::id(),
            ]);

            $invoice->items()
                ->where('payment_status', InvoiceItem::PAYMENT_UNPAID)
                ->update(['payment_status' => InvoiceItem::PAYMENT_PAID]);

            $registrationMorph = (new PatientTestRegistration)->getMorphClass();
            $registrationIds = $invoice->items()
                ->where('billable_type', $registrationMorph)
                ->whereNotNull('billable_id')
                ->pluck('billable_id');

            if ($registrationIds->isNotEmpty()) {
                PatientTestRegistration::query()
                    ->whereIn('id', $registrationIds)
                    ->update(['payment_status' => InvoiceItem::PAYMENT_PAID]);
            }

            $invoice->forceFill([
                'status' => Invoice::STATUS_PAID,
                'paid_amount' => (float) $invoice->paid_amount + $amount,
                'paid_at' => now(),
            ])->save();

            $invoice->load([
                'patient:id,name,last_name',
                'invoiceable',
                'items',
                'branch:id,name',
            ]);

            $departmentName = null;
            if ($invoice->invoiceable instanceof Appointment) {
                $invoice->invoiceable->loadMissing('department:id,name');
                $departmentName = $invoice->invoiceable->department?->name;
            }

            return PaymentReceipt::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'snapshot' => [
                    'invoice_uuid' => $invoice->uuid,
                    'invoiceable_type' => class_basename((string) $invoice->invoiceable_type),
                    'invoiceable_id' => $invoice->invoiceable_id,
                    'patient_name' => trim(($invoice->patient?->name ?? '').' '.($invoice->patient?->last_name ?? '')),
                    'branch_name' => $invoice->branch?->name,
                    'department_name' => $departmentName,
                    'amount' => $payment->amount,
                    'method' => $payment->method,
                    'paid_at' => optional($payment->paid_at)->toDateTimeString(),
                    'items' => $invoice->items->map(fn (InvoiceItem $item) => [
                        'description' => $item->description,
                        'amount' => $item->amount,
                    ])->values()->all(),
                ],
            ]);
        });
    }
}
