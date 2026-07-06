<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesPrescriptionReport;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\PrescriptionAlternativeItem;
use App\Models\PrescriptionItem;
use App\Models\PrintedNumber;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionController extends Controller
{
    use ManagesPrescriptionReport;
    use PaginatesInertiaIndex;

    private const INDEX_FILTER_KEYS = [
        'patient_name',
        'card_number',
        'father_name',
        'patient_id',
        'token_filter',
        'doctor_id',
        'status',
        'date_from',
        'date_to',
        'sort_by',
        'sort_order',
        'per_page',
    ];

    public function scanCode(Request $request): Response
    {
        $this->authorize('viewAny', Prescription::class);

        return Inertia::render('Prescriptions/ScanCode', [
            'urls' => [
                'scan' => route('react.prescriptions.scan'),
                'index' => route('react.prescriptions.index'),
            ],
            'error' => session('error'),
        ]);
    }

    public function scan(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Prescription::class);

        $request->validate(['qrCodeData' => 'required|string']);

        $prescription = Prescription::query()
            ->where('id', $request->input('qrCodeData'))
            ->where('branch_id', $request->user()->branch_id)
            ->visibleToClinicType($request->user()->clinic_type)
            ->first();

        if (! $prescription) {
            return redirect()
                ->route('react.prescriptions.scan-code')
                ->with('error', localize('global.prescription_not_found'));
        }

        return redirect()->route('react.prescriptions.show', $prescription);
    }

    public function index(Request $request): Response
    {
        return $this->renderIndex($request, 'undelivered');
    }

    public function delivered(Request $request): Response
    {
        return $this->renderIndex($request, 'delivered');
    }

    public function report(Request $request): Response
    {
        $this->authorize('viewAny', Prescription::class);

        $user = $request->user();
        $hasSearch = $this->prescriptionReportHasSearch($request);

        $prescriptions = [
            'data' => [],
            'links' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 25,
                'total' => 0,
                'from' => null,
                'to' => null,
            ],
        ];
        $summary = [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
        ];

        if ($hasSearch) {
            $query = $this->prescriptionReportBaseQuery($request, $user->clinic_type);
            $summary = $this->prescriptionReportSummary($query);

            $perPage = $request->input('per_page', '25');
            if ($perPage === 'all') {
                $items = $query->get();
                $prescriptions = [
                    'data' => $items->map(fn ($item) => $this->transformPrescriptionReportItem($item))->values()->all(),
                    'links' => [],
                    'meta' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => $items->count(),
                        'total' => $items->count(),
                        'from' => $items->count() > 0 ? 1 : null,
                        'to' => $items->count() > 0 ? $items->count() : null,
                    ],
                ];
            } else {
                $perPage = (int) $request->input('per_page', 25);
                $allowed = [10, 15, 25, 50, 100];
                if (! in_array($perPage, $allowed, true)) {
                    $perPage = 25;
                }

                $paginator = $query->paginate($perPage)->withQueryString();
                $prescriptions = [
                    'data' => collect($paginator->items())
                        ->map(fn ($item) => $this->transformPrescriptionReportItem($item))
                        ->values()
                        ->all(),
                    'links' => $paginator->linkCollection()->toArray(),
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                    ],
                ];
            }
        }

        return Inertia::render('Prescriptions/Report', [
            'prescriptions' => $prescriptions,
            'summary' => $summary,
            'hasSearch' => $hasSearch,
            'filters' => $this->collectFilters($request, $this->prescriptionReportFilterKeys()),
            'filterOptions' => [
                'pharmacies' => Pharmacy::query()->orderBy('name')->get(['id', 'name']),
            ],
            'urls' => [
                'current' => route('react.prescriptions.report'),
                'index' => route('react.prescriptions.index'),
                'pharmacyUsers' => url('/react/prescriptions/report/pharmacies'),
                'export' => url('/prescriptions/report-search'),
            ],
        ]);
    }

    public function reportPharmacyUsers(Pharmacy $pharmacy): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Prescription::class);

        $users = $pharmacy->activeUsers()
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.last_name'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => trim($user->name.' '.($user->last_name ?? '')),
            ])
            ->values();

        return response()->json($users);
    }

    public function show(Request $request, Prescription $prescription): Response
    {
        $this->authorize('view', $prescription);

        $prescription->load([
            'patient:id,name,last_name,father_name,id_card',
            'doctor:id,name',
            'pharmacy:id,name',
            'appointment.doctor:id,name',
            'prescriptionItems.medicine:id,name',
            'prescriptionItems.medicineType:id,type',
            'prescriptionItems.usageType:id,name',
            'prescriptionItems.selectedAlternative.medicine:id,name',
            'prescriptionItems.selectedAlternative.medicineType:id,type',
            'prescriptionItems.selectedAlternative.usageType:id,name',
            'prescriptionItems.alternativeItems.medicine:id,name',
            'prescriptionItems.alternativeItems.medicineType:id,type',
            'prescriptionItems.alternativeItems.usageType:id,name',
        ]);

        $user = $request->user();
        $canEdit = $user->can('update', $prescription) && ! $prescription->is_completed;

        return Inertia::render('Prescriptions/Show', [
            'prescription' => $this->transformPrescriptionForShow($prescription),
            'formOptions' => $canEdit ? [
                'medicines' => Medicine::query()->orderBy('name')->get(['id', 'name']),
                'medicineTypes' => MedicineType::query()->orderBy('type')->get(['id', 'type']),
                'medicineUsageTypes' => MedicineUsageType::query()->orderBy('name')->get(['id', 'name']),
            ] : null,
            'permissions' => [
                'edit' => $user->can('update', $prescription),
                'delete' => $user->can('delete', $prescription),
                'manageItems' => $canEdit,
            ],
            'urls' => $this->buildShowUrls($prescription),
        ]);
    }

    public function updateStatus(Request $request, Prescription $prescription): RedirectResponse
    {
        $this->authorize('update', $prescription);

        $validated = $request->validate([
            'is_completed' => 'required|boolean',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
        ]);

        $prescription->update($validated);

        return back()->with('success', localize('global.prescription_status_updated_successfully'));
    }

    public function markAllDelivered(Request $request, Prescription $prescription): RedirectResponse
    {
        $this->authorize('manageItems', $prescription);

        $prescription->load([
            'prescriptionItems.selectedAlternative',
        ]);

        foreach ($prescription->prescriptionItems as $item) {
            if (! $item->selectedAlternative && ! $item->is_delivered) {
                $item->update(['is_delivered' => true]);
            }

            if ($item->selectedAlternative && $item->selectedAlternative->is_delivered != '1') {
                $item->selectedAlternative->update(['is_delivered' => '1']);
            }
        }

        return back()->with('success', localize('global.items_marked_as_delivered'));
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Prescription::class);

        $validated = $request->validate([
            'prescription_ids' => 'required|array|min:1',
            'prescription_ids.*' => 'integer|exists:prescriptions,id',
            'is_completed' => 'required|boolean',
        ]);

        if (! $request->user()->hasRole(['super_admin', 'admin']) && ! $request->user()->can('edit-prescriptions')) {
            abort(403);
        }

        Prescription::query()
            ->whereIn('id', $validated['prescription_ids'])
            ->where('branch_id', $request->user()->branch_id)
            ->visibleToClinicType($request->user()->clinic_type)
            ->update(['is_completed' => $validated['is_completed']]);

        return back()->with('success', localize('global.bulk_status_updated_successfully'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Prescription::class);

        $validated = $request->validate([
            'prescription_ids' => 'required|array|min:1',
            'prescription_ids.*' => 'integer|exists:prescriptions,id',
        ]);

        if (! $request->user()->hasRole(['super_admin', 'admin']) && ! $request->user()->can('delete-prescriptions')) {
            abort(403);
        }

        $prescriptions = Prescription::query()
            ->whereIn('id', $validated['prescription_ids'])
            ->where('branch_id', $request->user()->branch_id)
            ->visibleToClinicType($request->user()->clinic_type)
            ->get();

        foreach ($prescriptions as $prescription) {
            $this->authorize('delete', $prescription);
            $prescription->delete();
        }

        return back()->with('success', localize('global.bulk_delete_successful'));
    }

    public function updateItemStatus(Request $request, PrescriptionItem $prescriptionItem): RedirectResponse
    {
        $prescriptionItem->load('prescription');
        $prescription = $prescriptionItem->prescription;
        $this->authorize('manageItems', $prescription);

        $validated = $request->validate([
            'is_delivered' => 'required|boolean',
        ]);

        $prescriptionItem->update(['is_delivered' => $validated['is_delivered']]);

        return back()->with('success', localize('global.item_status_updated_successfully'));
    }

    public function updateItemAmount(Request $request, PrescriptionItem $prescriptionItem): RedirectResponse
    {
        $prescriptionItem->load('prescription');
        $prescription = $prescriptionItem->prescription;
        $this->authorize('manageItems', $prescription);

        $validated = $request->validate([
            'amount' => 'required|string|max:255',
        ]);

        $prescriptionItem->update(['amount' => $validated['amount']]);

        return back()->with('success', localize('global.amount_updated_successfully'));
    }

    public function addAlternative(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prescription_id' => 'required|exists:prescriptions,id',
            'prescription_item_id' => 'required|exists:prescription_items,id',
            'medicine_id' => 'required|exists:medicines,id',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'usage_type_id' => 'required|exists:medicine_usage_types,id',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'amount' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $prescription = Prescription::query()->findOrFail($validated['prescription_id']);
        $this->authorize('manageItems', $prescription);

        PrescriptionItem::query()
            ->whereKey($validated['prescription_item_id'])
            ->where('prescription_id', $prescription->id)
            ->firstOrFail();

        PrescriptionAlternativeItem::create([
            ...$validated,
            'is_delivered' => '0',
            'is_selected' => '0',
        ]);

        return back()->with('success', localize('global.alternative_added_successfully'));
    }

    public function selectAlternative(Request $request, PrescriptionAlternativeItem $alternativeItem): RedirectResponse
    {
        $prescription = $alternativeItem->prescription;
        $this->authorize('manageItems', $prescription);

        $alternativeItem->is_selected = $alternativeItem->is_selected ? '0' : '1';
        $alternativeItem->save();

        if ($alternativeItem->is_selected) {
            PrescriptionAlternativeItem::query()
                ->where('prescription_item_id', $alternativeItem->prescription_item_id)
                ->whereKeyNot($alternativeItem->id)
                ->update(['is_selected' => '0']);
        }

        return back()->with('success', localize('global.alternative_selection_updated_successfully'));
    }

    public function updateAlternativeStatus(Request $request, PrescriptionAlternativeItem $alternativeItem): RedirectResponse
    {
        $prescription = $alternativeItem->prescription;
        $this->authorize('manageItems', $prescription);

        $validated = $request->validate([
            'is_delivered' => 'required|in:0,1',
        ]);

        $alternativeItem->update(['is_delivered' => $validated['is_delivered']]);

        return back()->with('success', localize('global.alternative_status_updated_successfully'));
    }

    public function deleteAlternative(Request $request, PrescriptionAlternativeItem $alternativeItem): RedirectResponse
    {
        $prescription = $alternativeItem->prescription;
        $this->authorize('manageItems', $prescription);

        $alternativeItem->delete();

        return back()->with('success', localize('global.alternative_deleted_successfully'));
    }

    public function destroy(Request $request, Prescription $prescription): RedirectResponse
    {
        $this->authorize('delete', $prescription);
        $prescription->delete();

        return redirect()
            ->route('react.prescriptions.index')
            ->with('success', localize('global.prescription_deleted_successfully'));
    }

    private function renderIndex(Request $request, string $mode): Response
    {
        $this->authorize('viewAny', Prescription::class);

        $user = $request->user();
        $query = $this->buildIndexQuery($request, $mode);

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 15, 20, 50], true) ? $perPage : 10;

        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->getCollection()->transform(function (Prescription $prescription) {
            if ($prescription->appointment) {
                $token = PrintedNumber::query()
                    ->where('patient_id', $prescription->patient_id)
                    ->where('department_id', $prescription->appointment->department_id)
                    ->whereDate('date', $prescription->appointment->date)
                    ->first();

                $prescription->setAttribute('token_number', $token?->number);
                $prescription->setAttribute('token_date', $token?->date);
            }

            $prescription->setAttribute(
                'doctor_name',
                $prescription->doctor?->name ?? $prescription->appointment?->doctor?->name ?? '—',
            );

            return $prescription;
        });

        $filters = [];
        foreach (self::INDEX_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        if ($mode === 'delivered') {
            $filters['status'] = '1';
        }

        return Inertia::render('Prescriptions/Index', [
            'mode' => $mode,
            'prescriptions' => [
                'data' => collect($paginator->items())
                    ->map(fn (Prescription $prescription) => $this->transformPrescriptionForIndex($prescription))
                    ->values()
                    ->all(),
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
            'filters' => $filters,
            'filterOptions' => [
                'doctors' => Doctor::query()
                    ->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
            'permissions' => $this->prescriptionPermissions($user),
            'urls' => $this->buildIndexUrls($mode),
        ]);
    }

    private function buildIndexQuery(Request $request, string $mode)
    {
        $user = $request->user();

        $query = Prescription::query()
            ->where('branch_id', $user->branch_id)
            ->with([
                'patient:id,name,last_name,father_name,id_card',
                'doctor:id,name',
                'appointment.doctor:id,name',
                'appointment.department:id,name',
            ]);

        $query->visibleToClinicType($user->clinic_type);

        if ($request->filled('patient_name')) {
            $patientName = $request->patient_name;
            $query->whereHas('patient', function ($q) use ($patientName) {
                $q->where('name', 'like', '%'.$patientName.'%')
                    ->orWhere('last_name', 'like', '%'.$patientName.'%');
            });
        }

        if ($request->filled('card_number')) {
            $cardNumber = $request->card_number;
            $query->whereHas('patient', fn ($q) => $q->where('id_card', 'like', '%'.$cardNumber.'%'));
        }

        if ($request->filled('father_name')) {
            $fatherName = $request->father_name;
            $query->whereHas('patient', fn ($q) => $q->where('father_name', 'like', '%'.$fatherName.'%'));
        }

        $patientIdInput = trim((string) $request->input('patient_id', ''));
        if ($patientIdInput !== '' && is_numeric($patientIdInput)) {
            $query->where('prescriptions.patient_id', (int) $patientIdInput);
        }

        if ($request->filled('token_filter')) {
            $tokenFilter = $request->token_filter;
            $query->whereHas('appointment', function ($q) use ($tokenFilter) {
                $q->whereHas('patient', function ($patientQuery) use ($tokenFilter) {
                    $patientQuery->whereHas('printedNumbers', function ($tokenQuery) use ($tokenFilter) {
                        $tokenQuery->where('number', 'like', '%'.$tokenFilter.'%')
                            ->whereColumn('printed_numbers.department_id', 'appointments.department_id')
                            ->whereColumn('printed_numbers.date', 'appointments.date');
                    });
                });
            });
        }

        if ($mode === 'delivered') {
            $query->where('is_completed', true);
        } elseif ($request->filled('status')) {
            $query->where('is_completed', $request->status);
        } else {
            $query->where('is_completed', false);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Verta::parse($request->date_from)->datetime());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Verta::parse($request->date_to)->datetime());
        }

        if ($request->filled('doctor_id')) {
            $query->whereRaw(
                'COALESCE(prescriptions.doctor_id, (SELECT doctor_id FROM appointments WHERE appointments.id = prescriptions.appointment_id LIMIT 1)) = ?',
                [$request->doctor_id],
            );
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'patient_name') {
            $query->join('patients', 'prescriptions.patient_id', '=', 'patients.id')
                ->orderBy('patients.name', $sortOrder)
                ->select('prescriptions.*');
        } elseif ($sortBy === 'doctor_name') {
            $query->leftJoin('appointments as app_doc_sort', 'prescriptions.appointment_id', '=', 'app_doc_sort.id')
                ->leftJoin('doctors as doc_resolved', 'doc_resolved.id', '=', DB::raw('COALESCE(prescriptions.doctor_id, app_doc_sort.doctor_id)'))
                ->orderBy('doc_resolved.name', $sortOrder)
                ->select('prescriptions.*');
        } elseif (in_array($sortBy, ['created_at', 'is_completed'], true)) {
            $query->orderBy('prescriptions.'.$sortBy, $sortOrder);
        } else {
            $query->orderBy('prescriptions.created_at', 'desc');
        }

        return $query;
    }

    private function transformPrescriptionForIndex(Prescription $prescription): array
    {
        return [
            'id' => $prescription->id,
            'patient_id' => $prescription->patient_id,
            'patient_name' => trim(($prescription->patient?->name ?? '').' '.($prescription->patient?->last_name ?? '')) ?: '—',
            'card_number' => $prescription->patient?->id_card,
            'father_name' => $prescription->patient?->father_name,
            'doctor_name' => $prescription->doctor_name ?? '—',
            'token_number' => $prescription->token_number ?? null,
            'token_date' => $prescription->token_date ?? null,
            'department_name' => $prescription->appointment?->department?->name,
            'is_completed' => (bool) $prescription->is_completed,
            'created_at' => $this->formatCreatedAt($prescription->created_at),
        ];
    }

    private function transformPrescriptionForShow(Prescription $prescription): array
    {
        return [
            'id' => $prescription->id,
            'patient_id' => $prescription->patient_id,
            'patient_name' => trim(($prescription->patient?->name ?? '').' '.($prescription->patient?->last_name ?? '')) ?: '—',
            'doctor_name' => $prescription->doctor?->name ?? $prescription->appointment?->doctor?->name ?? '—',
            'pharmacy_name' => $prescription->pharmacy?->name,
            'is_completed' => (bool) $prescription->is_completed,
            'created_at' => $this->formatCreatedAt($prescription->created_at),
            'items' => $prescription->prescriptionItems->map(fn (PrescriptionItem $item) => [
                'id' => $item->id,
                'medicine_name' => $item->medicine?->name ?? '—',
                'medicine_type' => $item->medicineType?->type,
                'usage_type_name' => $item->usageType?->name,
                'dosage' => $item->dosage,
                'frequency' => $item->frequency,
                'amount' => $item->amount,
                'is_delivered' => (bool) $item->is_delivered,
                'medicine_type_id' => $item->medicine_type_id,
                'usage_type_id' => $item->usage_type_id,
                'medicine_id' => $item->medicine_id,
                'selected_alternative' => $item->selectedAlternative ? [
                    'id' => $item->selectedAlternative->id,
                    'medicine_name' => $item->selectedAlternative->medicine?->name ?? '—',
                    'medicine_type' => $item->selectedAlternative->medicineType?->type,
                    'usage_type_name' => $item->selectedAlternative->usageType?->name,
                    'dosage' => $item->selectedAlternative->dosage,
                    'frequency' => $item->selectedAlternative->frequency,
                    'amount' => $item->selectedAlternative->amount,
                    'notes' => $item->selectedAlternative->notes,
                    'is_delivered' => $item->selectedAlternative->is_delivered == '1',
                ] : null,
                'alternatives_count' => $item->alternativeItems->count(),
                'alternatives' => $item->alternativeItems->map(fn (PrescriptionAlternativeItem $alt) => [
                    'id' => $alt->id,
                    'medicine_name' => $alt->medicine?->name ?? '—',
                    'medicine_type' => $alt->medicineType?->type,
                    'usage_type_name' => $alt->usageType?->name,
                    'dosage' => $alt->dosage,
                    'frequency' => $alt->frequency,
                    'amount' => $alt->amount,
                    'notes' => $alt->notes,
                    'is_delivered' => $alt->is_delivered == '1',
                    'is_selected' => $alt->is_selected == '1',
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    private function formatCreatedAt(?\Illuminate\Support\Carbon $createdAt): ?string
    {
        if (! $createdAt) {
            return null;
        }

        try {
            return verta($createdAt)->format('Y/m/d H:i');
        } catch (\Throwable) {
            return $createdAt->toDateTimeString();
        }
    }

    private function prescriptionPermissions($user): array
    {
        return [
            'view' => $user->can('viewAny', Prescription::class),
            'edit' => $user->hasRole(['super_admin', 'admin']) || $user->can('edit-prescriptions'),
            'delete' => $user->hasRole(['super_admin', 'admin']) || $user->can('delete-prescriptions'),
            'export' => $user->can('export', Prescription::class),
        ];
    }

    private function buildIndexUrls(string $mode): array
    {
        return [
            'index' => route('react.prescriptions.index'),
            'delivered' => route('react.prescriptions.delivered'),
            'show' => url('/react/prescriptions'),
            'bulkUpdateStatus' => route('react.prescriptions.bulk-update-status'),
            'bulkDelete' => route('react.prescriptions.bulk-delete'),
            'export' => url('/prescriptions/export-prescriptions'),
            'thermalReceipt' => url('/prescriptions/thermal-receipt'),
            'scanCode' => route('react.prescriptions.scan-code'),
            'current' => $mode === 'delivered'
                ? route('react.prescriptions.delivered')
                : route('react.prescriptions.index'),
        ];
    }

    private function buildShowUrls(Prescription $prescription): array
    {
        return [
            'index' => route('react.prescriptions.index'),
            'delivered' => route('react.prescriptions.delivered'),
            'updateStatus' => route('react.prescriptions.update-status', $prescription),
            'markAllDelivered' => route('react.prescriptions.mark-all-delivered', $prescription),
            'destroy' => route('react.prescriptions.destroy', $prescription),
            'thermalReceipt' => url("/prescriptions/thermal-receipt/{$prescription->id}"),
            'itemsBase' => url('/react/prescriptions/items'),
            'alternativesBase' => url('/react/prescriptions/alternatives'),
            'addAlternative' => route('react.prescriptions.alternatives.store'),
        ];
    }
}
