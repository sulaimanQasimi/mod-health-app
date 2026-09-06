<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Jobs\SendNewBloodBankNotification;
use App\Models\Appointment;
use App\Models\BloodBank;
use App\Models\BloodBankTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $items = $appointment->bloodBanks()->with('tests')->latest()->get()->map(fn (BloodBank $item) => [
            'id' => $item->id,
            'group' => $item->group,
            'rh' => $item->rh,
            'type' => $item->type,
            'quantity' => $item->quantity,
            'hemoglobin' => $item->hemoglobin,
            'hematocrit' => $item->hematocrit,
            'factor' => $item->factor,
            'tests' => $item->tests->map(fn (BloodBankTest $test) => [
                'id' => $test->id,
                'test_name' => $test->test_name,
                'result' => $test->result,
            ])->values()->all(),
            'status' => $item->status,
            'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d H:i') : null,
            'urls' => ['show' => route('blood-banks.show', $item)],
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
            'type' => ['nullable', 'string', Rule::in(['RBC', 'PRBC', 'Fresh', 'Platelets', 'Plasma', 'Whole Blood'])],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'hemoglobin' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'hematocrit' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'factor' => ['nullable', 'string', 'max:255'],
            'tests' => ['nullable', 'array'],
            'tests.*.test_name' => ['required', 'string', 'max:255'],
        ]);

        $bloodBank = DB::transaction(function () use ($validated, $appointment, $request) {
            $bloodBank = BloodBank::create([
                'group' => $validated['group'] ?? null,
                'rh' => $validated['rh'] ?? null,
                'type' => $validated['type'] ?? 'Fresh',
                'quantity' => $validated['quantity'] ?? null,
                'hemoglobin' => $validated['hemoglobin'] ?? null,
                'hematocrit' => $validated['hematocrit'] ?? null,
                'factor' => $validated['factor'] ?? null,
                'branch_id' => $appointment->branch_id,
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'department_id' => $appointment->department_id,
                'created_by' => $request->user()->id,
            ]);

            foreach ($validated['tests'] ?? [] as $test) {
                $name = trim((string) ($test['test_name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                BloodBankTest::create([
                    'blood_bank_id' => $bloodBank->id,
                    'test_name' => $name,
                ]);
            }

            return $bloodBank;
        });

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
