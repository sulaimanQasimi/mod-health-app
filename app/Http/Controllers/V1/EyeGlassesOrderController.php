<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\EyeGlassesOrder;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EyeGlassesOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAccess($request);

        $filters = $request->only(['search', 'status', 'examiner_id', 'date_from', 'date_to', 'per_page']);
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

        $requestedPerPage = (int) ($filters['per_page'] ?? 25);
        $perPage = in_array($requestedPerPage, [10, 25, 50], true) ? $requestedPerPage : 25;
        $paginator = $query->paginate($perPage)->withQueryString();

        $statsQuery = EyeGlassesOrder::query()
            ->when($request->user()->branch_id, fn (Builder $builder, $branchId) => $builder->where('branch_id', $branchId));

        return Inertia::render('EyeGlassesOrders/Index', [
            'orders' => [
                'data' => collect($paginator->items())->map(fn (EyeGlassesOrder $item) => $this->listItem($item))->values()->all(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'requested' => (clone $statsQuery)->where('status', 'requested')->count(),
                'processing' => (clone $statsQuery)->where('status', 'processing')->count(),
                'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
                'delivered' => (clone $statsQuery)->where('status', 'delivered')->count(),
            ],
            'filters' => array_merge([
                'search' => '',
                'status' => '',
                'examiner_id' => '',
                'date_from' => '',
                'date_to' => '',
                'per_page' => '25',
            ], $filters),
            'doctors' => $this->eyeDoctors($request->user()->branch_id)->get(['id', 'name']),
            'permissions' => [
                'delete' => $request->user()->can('delete-ophthalmology-registrations'),
            ],
            'urls' => [
                'current' => route('react.eye-glasses-orders.index'),
            ],
        ]);
    }

    public function show(Request $request, EyeGlassesOrder $eyeGlassesOrder): Response
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        $this->authorize('view', $eyeGlassesOrder->appointment);

        return Inertia::render('EyeGlassesOrders/Show', [
            'order' => $this->transform($eyeGlassesOrder),
            'formOptions' => [
                'doctors' => $this->eyeDoctors($eyeGlassesOrder->branch_id)->get(['id', 'name']),
                'frameTypes' => EyeGlassesOrder::FRAME_TYPES,
                'lensTypes' => EyeGlassesOrder::LENS_TYPES,
                'lensMaterials' => EyeGlassesOrder::LENS_MATERIALS,
                'paymentMethods' => EyeGlassesOrder::PAYMENT_METHODS,
            ],
            'permissions' => $this->actionPermissions($request, $eyeGlassesOrder),
            'urls' => [
                'update' => route('react.eye-glasses-orders.update', $eyeGlassesOrder),
                'process' => route('react.eye-glasses-orders.process', $eyeGlassesOrder),
                'payment' => route('react.eye-glasses-orders.payment', $eyeGlassesOrder),
                'deliver' => route('react.eye-glasses-orders.deliver', $eyeGlassesOrder),
                'cancel' => route('react.eye-glasses-orders.cancel', $eyeGlassesOrder),
                'destroy' => route('react.eye-glasses-orders.destroy', $eyeGlassesOrder),
                'print' => route('react.eye-glasses-orders.print', $eyeGlassesOrder),
                'appointment' => route('react.appointments.show', $eyeGlassesOrder->appointment_id),
                'index' => route('react.eye-glasses-orders.index'),
                'patient' => $eyeGlassesOrder->appointment?->patient_id
                    ? route('react.patients.show', $eyeGlassesOrder->appointment->patient_id)
                    : null,
                'ophthalmology' => $eyeGlassesOrder->ophthalmology_registration_id
                    ? route('react.ophthalmology-registrations.show', $eyeGlassesOrder->ophthalmology_registration_id)
                    : null,
            ],
        ]);
    }

    public function print(Request $request, EyeGlassesOrder $eyeGlassesOrder): Response
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        $this->authorize('view', $eyeGlassesOrder->appointment);

        return Inertia::render('EyeGlassesOrders/Print', [
            'order' => $this->transform($eyeGlassesOrder),
            'assets' => [
                'leftLogo' => asset('images/logos/لوگو قومنداني.JPG'),
                'rightLogo' => asset('images/logos/لوگوی جدید وزارت دفاع ملی.png'),
            ],
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
            ->route('react.eye-glasses-orders.show', $eyeGlassesOrder)
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
            ->route('react.eye-glasses-orders.show', $eyeGlassesOrder)
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
            ->route('react.eye-glasses-orders.show', $eyeGlassesOrder)
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
            ->route('react.eye-glasses-orders.show', $eyeGlassesOrder)
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
            ->route('react.eye-glasses-orders.show', $eyeGlassesOrder)
            ->with('success', localize('global.eye_glasses_order_cancelled_successfully'));
    }

    public function destroy(Request $request, EyeGlassesOrder $eyeGlassesOrder): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $eyeGlassesOrder);
        abort_unless($request->user()->can('delete-ophthalmology-registrations'), 403);

        $eyeGlassesOrder->delete();

        return redirect()
            ->route('react.eye-glasses-orders.index')
            ->with('success', localize('global.eye_glasses_order_deleted_successfully'));
    }

    private function listItem(EyeGlassesOrder $item): array
    {
        return [
            'id' => $item->id,
            'ref_no' => $item->ref_no,
            'patient_name' => trim(($item->appointment?->patient?->name ?? '').' '.($item->appointment?->patient?->last_name ?? '')),
            'id_card' => $item->appointment?->patient?->id_card,
            'examiner_name' => $item->examiner?->name,
            'request_date' => $item->request_date ? verta($item->request_date)->format('Y-m-d') : null,
            'status' => $item->status,
            'frame_type' => $item->frame_type,
            'lens_type' => $item->lens_type,
            'amount' => $item->amount,
            'show_url' => route('react.eye-glasses-orders.show', $item),
            'delete_url' => route('react.eye-glasses-orders.destroy', $item),
        ];
    }

    private function transform(EyeGlassesOrder $order): array
    {
        $order->load([
            'appointment.patient:id,name,last_name,father_name,id_card,age,gender,phone,job',
            'appointment:id,patient_id,doctor_id,branch_id,date,is_completed',
            'examiner:id,name',
            'branch:id,name',
            'ophthalmologyRegistration:id,ref_no',
            'processedByUser:id,name',
            'paidByUser:id,name',
            'deliveredByUser:id,name',
            'cancelledByUser:id,name',
        ]);

        $patient = $order->appointment?->patient;

        return [
            'id' => $order->id,
            'appointment_id' => $order->appointment_id,
            'ophthalmology_registration_id' => $order->ophthalmology_registration_id,
            'ophthalmology_ref_no' => $order->ophthalmologyRegistration?->ref_no,
            'ref_no' => $order->ref_no,
            'examiner_id' => $order->examiner_id,
            'examiner_name' => $order->examiner?->name,
            'branch_name' => $order->branch?->name,
            'request_date' => $order->request_date ? verta($order->request_date)->format('Y-m-d') : '',
            'appointment_date' => $order->appointment?->date ? verta($order->appointment->date)->format('Y-m-d') : null,
            'appointment_completed' => (bool) $order->appointment?->is_completed,
            'status' => $order->status,
            'frame_type' => $order->frame_type,
            'lens_type' => $order->lens_type,
            'lens_material' => $order->lens_material,
            'tint' => $order->tint ?? '',
            'quantity' => $order->quantity ?? 1,
            'prescription' => $order->prescription ?? ['od' => [], 'os' => [], 'ipd' => ''],
            'notes' => $order->notes ?? '',
            'processed_at' => $order->processed_at ? verta($order->processed_at)->format('Y-m-d H:i') : null,
            'processed_by_name' => $order->processedByUser?->name,
            'process_notes' => $order->process_notes ?? '',
            'amount' => $order->amount,
            'paid_amount' => $order->paid_amount,
            'paid_at' => $order->paid_at ? verta($order->paid_at)->format('Y-m-d H:i') : null,
            'paid_by_name' => $order->paidByUser?->name,
            'payment_method' => $order->payment_method,
            'payment_notes' => $order->payment_notes ?? '',
            'delivered_at' => $order->delivered_at ? verta($order->delivered_at)->format('Y-m-d H:i') : null,
            'delivered_by_name' => $order->deliveredByUser?->name,
            'received_by' => $order->received_by ?? '',
            'delivery_notes' => $order->delivery_notes ?? '',
            'cancelled_at' => $order->cancelled_at ? verta($order->cancelled_at)->format('Y-m-d H:i') : null,
            'cancelled_by_name' => $order->cancelledByUser?->name,
            'cancellation_reason' => $order->cancellation_reason ?? '',
            'patient' => [
                'id' => $patient?->id,
                'name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')),
                'father_name' => $patient?->father_name,
                'id_card' => $patient?->id_card,
                'age' => $patient?->age,
                'gender' => $patient?->gender,
                'phone' => $patient?->phone,
                'occupation' => $patient?->job,
            ],
        ];
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
