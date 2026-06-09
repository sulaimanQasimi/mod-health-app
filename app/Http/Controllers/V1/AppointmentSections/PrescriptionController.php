<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Jobs\SendNewPrescriptionNotification;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\MedicineUsageType;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $doctorAppointments = Appointment::query()
            ->where('processed_by', request()->user()->id)
            ->where('is_completed', '0')
            ->where('id', '!=', $appointment->id)
            ->with(['patient:id,name,last_name,id_card'])
            ->latest()
            ->get()
            ->map(fn (Appointment $item) => [
                'id' => $item->id,
                'patient_id' => $item->patient_id,
                'branch_id' => $item->branch_id,
                'time' => $item->time,
                'under_review_id' => $item->under_review_id,
                'patient_name' => trim(($item->patient?->name ?? '').' '.($item->patient?->last_name ?? '')),
                'patient_id_card' => $item->patient?->id_card,
            ])
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'medicines' => Medicine::query()->orderBy('name')->get(['id', 'name']),
                'usage_types' => MedicineUsageType::query()->orderBy('name')->get(['id', 'name']),
                'doctor_appointments' => $doctorAppointments,
            ],
        ]);
    }

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $items = $appointment->prescription()
            ->with(['doctor:id,name', 'patient:id,name,last_name', 'prescriptionItems'])
            ->latest()
            ->get()
            ->map(fn (Prescription $item) => [
                'id' => $item->id,
                'patient_name' => trim(($item->patient?->name ?? '').' '.($item->patient?->last_name ?? '')),
                'doctor_name' => $item->doctor?->name,
                'items_count' => $item->prescriptionItems->count(),
                'is_completed' => (bool) $item->is_completed,
                'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d H:i') : null,
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('add-prescription'),
            'edit' => $user->can('edit-prescriptions'),
            'delete' => $user->can('delete-prescriptions'),
        ]);
    }

    public function show(Appointment $appointment, Prescription $prescription): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless((int) $prescription->appointment_id === (int) $appointment->id, 404);

        $prescription->load([
            'patient:id,name,last_name',
            'doctor:id,name',
            'prescriptionItems.medicine:id,name',
            'prescriptionItems.usageType:id,name',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $prescription->id,
                'patient_name' => trim(($prescription->patient?->name ?? '').' '.($prescription->patient?->last_name ?? '')),
                'doctor_name' => $prescription->doctor?->name,
                'is_completed' => (bool) $prescription->is_completed,
                'items' => $prescription->prescriptionItems->map(fn (PrescriptionItem $item) => [
                    'id' => $item->id,
                    'medicine_id' => $item->medicine_id,
                    'medicine_name' => $item->medicine?->name,
                    'usage_type_id' => $item->usage_type_id,
                    'usage_type_name' => $item->usageType?->name,
                    'dosage' => $item->dosage,
                    'frequency' => $item->frequency,
                    'amount' => $item->amount,
                    'is_delivered' => (bool) $item->is_delivered,
                ])->values()->all(),
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_if($appointment->is_completed, 403);
        abort_unless($request->user()->can('add-prescription'), 403);

        $validated = $request->validate([
            'target_appointment_id' => 'nullable|exists:appointments,id',
            'under_review_id' => 'nullable|exists:under_reviews,id',
            'prescription_items' => 'required|array|min:1',
            'prescription_items.*.medicine_id' => 'required|exists:medicines,id',
            'prescription_items.*.usage_type_id' => 'required|exists:medicine_usage_types,id',
            'prescription_items.*.dosage' => 'required|string',
            'prescription_items.*.frequency' => 'required|string',
            'prescription_items.*.amount' => 'required|string',
        ]);

        $targetAppointment = $appointment;
        if (! empty($validated['target_appointment_id'])) {
            $targetAppointment = Appointment::query()->findOrFail($validated['target_appointment_id']);
            abort_unless($this->belongsToAppointmentBranch($request->user(), $targetAppointment), 403);
        }

        DB::beginTransaction();

        try {
            $prescription = Prescription::create([
                'branch_id' => $targetAppointment->branch_id,
                'appointment_id' => $targetAppointment->id,
                'patient_id' => $targetAppointment->patient_id,
                'doctor_id' => $targetAppointment->doctor_id,
                'under_review_id' => $validated['under_review_id'] ?? null,
                'is_completed' => false,
                'created_by' => $request->user()->id,
            ]);

            foreach ($validated['prescription_items'] as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $item['medicine_id'],
                    'usage_type_id' => $item['usage_type_id'],
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'amount' => $item['amount'],
                    'is_delivered' => false,
                ]);
            }

            SendNewPrescriptionNotification::dispatch($prescription->created_by, $prescription->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => localize('global.prescription_created_successfully'),
                'data' => ['prescription_id' => $prescription->id],
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function updateItemStatus(Request $request, Appointment $appointment, PrescriptionItem $prescriptionItem): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($request->user()->can('edit-prescriptions'), 403);
        $prescriptionItem->loadMissing('prescription');
        abort_unless((int) $prescriptionItem->prescription?->appointment_id === (int) $appointment->id, 404);

        $validated = $request->validate([
            'is_delivered' => 'required|boolean',
        ]);

        $prescriptionItem->update(['is_delivered' => $validated['is_delivered']]);

        return response()->json([
            'success' => true,
            'message' => localize('global.prescription_item_status_updated_successfully'),
        ]);
    }

    public function destroyItem(Appointment $appointment, PrescriptionItem $prescriptionItem): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('edit-prescriptions'), 403);
        $prescriptionItem->loadMissing('prescription');
        abort_unless((int) $prescriptionItem->prescription?->appointment_id === (int) $appointment->id, 404);
        abort_if($prescriptionItem->is_delivered, 403);

        $prescriptionItem->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.prescription_item_deleted_successfully'),
        ]);
    }

    public function destroy(Appointment $appointment, Prescription $prescription): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-prescriptions'), 403);
        abort_unless((int) $prescription->appointment_id === (int) $appointment->id, 404);
        $prescription->delete();

        return response()->json(['success' => true]);
    }
}
