<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Jobs\SendNewBloodBankNotification;
use App\Models\Appointment;
use App\Models\BloodBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodBankController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $user->can('show-blood-request-menu')) {
            return $this->sectionIndexResponse([], $appointment, [
                'view' => false,
                'create' => false,
                'delete' => false,
            ]);
        }

        $items = $appointment->bloodBanks()->latest()->get()->map(fn (BloodBank $item) => [
            'id' => $item->id,
            'group' => $item->group,
            'rh' => $item->rh,
            'type' => $item->type,
            'quantity' => $item->quantity,
            'status' => $item->status,
            'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d H:i') : null,
            'urls' => ['show' => route('react.blood-banks.show', $item)],
        ])->values()->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => $this->canMutateAppointment($appointment) && $user->can('add-blood-request'),
            'delete' => $this->canMutateAppointment($appointment) && $user->can('delete-blood-request'),
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($request->user()->can('add-blood-request'), 403);
        $this->assertAppointmentMutable($appointment);

        $validated = $request->validate([
            'group' => ['nullable', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh' => ['nullable', 'string', Rule::in(['+', '-'])],
            'type' => ['required', 'string', Rule::in(['RBC', 'PRBC', 'Fresh', 'Platelets', 'Plasma', 'Whole Blood'])],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $bloodBank = BloodBank::create([
            'group' => $validated['group'] ?? null,
            'rh' => $validated['rh'] ?? null,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'branch_id' => $appointment->branch_id,
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'department_id' => $appointment->department_id,
            'created_by' => $request->user()->id,
        ]);

        SendNewBloodBankNotification::dispatch($bloodBank->created_by, $bloodBank->id);

        return response()->json([
            'success' => true,
            'message' => localize('global.blood_request_created_successfully'),
        ]);
    }

    public function destroy(Appointment $appointment, BloodBank $bloodBank): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-blood-request'), 403);
        $this->assertAppointmentMutable($appointment);
        abort_unless((int) $bloodBank->appointment_id === (int) $appointment->id, 404);
        $bloodBank->delete();

        return response()->json(['success' => true]);
    }
}
