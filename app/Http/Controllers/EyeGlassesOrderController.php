<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\EyeGlassesOrder;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EyeGlassesOrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $query = EyeGlassesOrder::query()
            ->with([
                'appointment.patient:id,name,last_name,id_card',
                'appointment:id,patient_id,date',
                'examiner:id,name',
            ])
            ->when($request->user()->branch_id, fn (Builder $builder, $branchId) => $builder->where('branch_id', $branchId))
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $search = $request->string('search')->toString();
                $builder->where(function (Builder $nested) use ($search) {
                    $nested->where('ref_no', 'like', "%{$search}%")
                        ->orWhere('received_by', 'like', "%{$search}%")
                        ->orWhereHas('appointment.patient', function (Builder $patient) use ($search) {
                            $patient->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('id_card', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->status))
            ->when($request->filled('examiner_id'), fn (Builder $builder) => $builder->where('examiner_id', $request->examiner_id))
            ->when($request->filled('date_from'), fn (Builder $builder) => $builder->whereDate('request_date', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn (Builder $builder) => $builder->whereDate('request_date', '<=', Verta::parse($request->date_to)->datetime()))
            ->latest();

        $orders = $query->paginate(25)->withQueryString();

        $statsQuery = EyeGlassesOrder::query()
            ->when($request->user()->branch_id, fn (Builder $builder, $branchId) => $builder->where('branch_id', $branchId));

        $doctors = $this->eyeDoctors($request->user()->branch_id)->get(['id', 'name']);

        return view('pages.ophthalmology.eye-glasses.index', [
            'orders' => $orders,
            'doctors' => $doctors,
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'requested' => (clone $statsQuery)->where('status', 'requested')->count(),
                'processing' => (clone $statsQuery)->where('status', 'processing')->count(),
                'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
                'delivered' => (clone $statsQuery)->where('status', 'delivered')->count(),
            ],
        ]);
    }

    public function show(Request $request, EyeGlassesOrder $eyeGlassesOrder): View
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        $this->authorize('view', $eyeGlassesOrder->appointment);

        $eyeGlassesOrder->load([
            'appointment.patient',
            'appointment',
            'examiner:id,name',
            'branch:id,name',
            'ophthalmologyRegistration:id,ref_no',
            'processedByUser:id,name',
            'paidByUser:id,name',
            'deliveredByUser:id,name',
            'cancelledByUser:id,name',
        ]);

        return view('pages.ophthalmology.eye-glasses.show', [
            'order' => $eyeGlassesOrder,
            'doctors' => $this->eyeDoctors($eyeGlassesOrder->branch_id)->get(['id', 'name']),
            'permissions' => $this->actionPermissions($request, $eyeGlassesOrder),
        ]);
    }

    public function print(Request $request, EyeGlassesOrder $eyeGlassesOrder): View
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        $this->authorize('view', $eyeGlassesOrder->appointment);

        $eyeGlassesOrder->load([
            'appointment.patient',
            'examiner:id,name',
            'branch:id,name',
        ]);

        return view('pages.ophthalmology.eye-glasses.print', [
            'order' => $eyeGlassesOrder,
            'leftLogo' => asset('images/logos/لوگو قومنداني.JPG'),
            'rightLogo' => asset('images/logos/لوگوی جدید وزارت دفاع ملی.png'),
            'generatedAt' => verta()->format('Y/m/d H:i'),
        ]);
    }

    public function update(Request $request, EyeGlassesOrder $eyeGlassesOrder): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        $this->authorize('view', $eyeGlassesOrder->appointment);
        abort_if($eyeGlassesOrder->isLocked(), 403);
        abort_unless($request->user()->can('edit-ophthalmology-registrations'), 403);
        abort_unless(in_array($eyeGlassesOrder->status, ['requested', 'processing'], true), 403);

        $doctorIds = $this->eyeDoctors($eyeGlassesOrder->branch_id)->pluck('id')->all();
        $validated = $request->validate([
            'examiner_id' => ['nullable', Rule::in($doctorIds)],
            'request_date' => ['sometimes', 'required', 'string'],
            'frame_type' => ['nullable', Rule::in(EyeGlassesOrder::FRAME_TYPES)],
            'lens_type' => ['nullable', Rule::in(EyeGlassesOrder::LENS_TYPES)],
            'lens_material' => ['nullable', Rule::in(EyeGlassesOrder::LENS_MATERIALS)],
            'tint' => ['nullable', 'string', 'max:100'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
            'prescription' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);

        if (array_key_exists('request_date', $validated)) {
            $validated['request_date'] = Verta::parse($validated['request_date'])->datetime();
        }

        $eyeGlassesOrder->update($validated);

        return redirect()
            ->route('eye-glasses-orders.show', $eyeGlassesOrder)
            ->with('success', localize('global.eye_glasses_order_updated_successfully'));
    }

    public function process(Request $request, EyeGlassesOrder $eyeGlassesOrder): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        abort_if($eyeGlassesOrder->isLocked(), 403);
        abort_unless($request->user()->can('edit-ophthalmology-registrations'), 403);
        if ($eyeGlassesOrder->status !== 'requested') {
            return redirect()
                ->back()
                ->with('error', localize('global.eye_glasses_invalid_status_transition'));
        }

        $validated = $request->validate([
            'process_notes' => ['nullable', 'string'],
        ]);

        $eyeGlassesOrder->update([
            'status' => 'processing',
            'processed_at' => now(),
            'processed_by' => $request->user()->id,
            'process_notes' => $validated['process_notes'] ?? $eyeGlassesOrder->process_notes,
        ]);

        return redirect()
            ->route('eye-glasses-orders.show', $eyeGlassesOrder)
            ->with('success', localize('global.eye_glasses_order_processed_successfully'));
    }

    public function payment(Request $request, EyeGlassesOrder $eyeGlassesOrder): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        abort_if($eyeGlassesOrder->isLocked(), 403);
        abort_unless($request->user()->can('change-ophthalmology-status'), 403);
        if ($eyeGlassesOrder->status !== 'processing') {
            return redirect()
                ->back()
                ->with('error', localize('global.eye_glasses_invalid_status_transition'));
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(EyeGlassesOrder::PAYMENT_METHODS)],
            'payment_notes' => ['nullable', 'string'],
        ]);

        $eyeGlassesOrder->update([
            'status' => 'paid',
            'amount' => $validated['amount'],
            'paid_amount' => $validated['paid_amount'] ?? $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_notes' => $validated['payment_notes'] ?? null,
            'paid_at' => now(),
            'paid_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('eye-glasses-orders.show', $eyeGlassesOrder)
            ->with('success', localize('global.eye_glasses_payment_recorded_successfully'));
    }

    public function deliver(Request $request, EyeGlassesOrder $eyeGlassesOrder): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        abort_if($eyeGlassesOrder->isLocked(), 403);
        abort_unless($request->user()->can('change-ophthalmology-status'), 403);
        if ($eyeGlassesOrder->status !== 'paid') {
            return redirect()
                ->back()
                ->with('error', localize('global.eye_glasses_invalid_status_transition'));
        }

        $validated = $request->validate([
            'received_by' => ['nullable', 'string', 'max:255'],
            'delivery_notes' => ['nullable', 'string'],
        ]);

        $eyeGlassesOrder->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'delivered_by' => $request->user()->id,
            'received_by' => $validated['received_by'] ?? $eyeGlassesOrder->received_by,
            'delivery_notes' => $validated['delivery_notes'] ?? $eyeGlassesOrder->delivery_notes,
        ]);

        return redirect()
            ->route('eye-glasses-orders.show', $eyeGlassesOrder)
            ->with('success', localize('global.eye_glasses_order_delivered_successfully'));
    }

    public function cancel(Request $request, EyeGlassesOrder $eyeGlassesOrder): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        abort_if($eyeGlassesOrder->isLocked(), 403);
        abort_unless($request->user()->can('change-ophthalmology-status'), 403);
        if (! in_array($eyeGlassesOrder->status, ['requested', 'processing'], true)) {
            return redirect()
                ->back()
                ->with('error', localize('global.eye_glasses_invalid_status_transition'));
        }

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string'],
        ]);

        $eyeGlassesOrder->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ]);

        return redirect()
            ->route('eye-glasses-orders.show', $eyeGlassesOrder)
            ->with('success', localize('global.eye_glasses_order_cancelled_successfully'));
    }

    public function destroy(Request $request, EyeGlassesOrder $eyeGlassesOrder): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        abort_unless($request->user()->can('delete-ophthalmology-registrations'), 403);

        $eyeGlassesOrder->delete();

        return redirect()
            ->route('eye-glasses-orders.index')
            ->with('success', localize('global.eye_glasses_order_deleted_successfully'));
    }

    private function actionPermissions(Request $request, EyeGlassesOrder $order): array
    {
        $locked = $order->isLocked();
        $user = $request->user();

        return [
            'edit' => ! $locked
                && in_array($order->status, ['requested', 'processing'], true)
                && $user->can('edit-ophthalmology-registrations'),
            'process' => ! $locked
                && $order->status === 'requested'
                && $user->can('edit-ophthalmology-registrations'),
            'pay' => ! $locked
                && $order->status === 'processing'
                && $user->can('change-ophthalmology-status'),
            'deliver' => ! $locked
                && $order->status === 'paid'
                && $user->can('change-ophthalmology-status'),
            'cancel' => ! $locked
                && in_array($order->status, ['requested', 'processing'], true)
                && $user->can('change-ophthalmology-status'),
            'delete' => $user->can('delete-ophthalmology-registrations'),
        ];
    }

    private function eyeDoctors(int|string|null $branchId)
    {
        return Doctor::query()
            ->where('active_status', true)
            ->where('is_eye_doctor', true)
            ->when($branchId, fn (Builder $builder, $id) => $builder->where('branch_id', $id))
            ->orderBy('name');
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->can('access-ophthalmology-registrations'), 403);
    }

    private function authorizeOrderAccess(Request $request, EyeGlassesOrder $order): void
    {
        $this->authorizeAccess($request);
        if ($request->user()->branch_id
            && (int) $request->user()->branch_id !== (int) $order->branch_id) {
            abort(404);
        }
    }
}
