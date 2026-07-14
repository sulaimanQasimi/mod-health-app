<?php

namespace App\Http\Controllers\V1\PatientSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\PatientSections\Concerns\AuthorizesPatientAccess;
use App\Models\Doctor;
use App\Models\ForeignCountryReferral;
use App\Models\ForeignCountryReferralAttachment;
use App\Models\ForeignCountryReferralItem;
use App\Models\Patient;
use App\Support\ForeignCountryReferralCountries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ForeignCountryReferralController extends Controller
{
    use AuthorizesPatientAccess;

    public function meta(Patient $patient): JsonResponse
    {
        $this->authorizePatientView($patient);

        $locale = app()->getLocale();

        return response()->json([
            'success' => true,
            'data' => [
                'countries' => collect(ForeignCountryReferralCountries::options())
                    ->map(fn (array $country) => [
                        'value' => $country['value'],
                        'label' => match ($locale) {
                            'dr' => $country['name_dr'],
                            'ps' => $country['name_ps'],
                            default => $country['name_en'],
                        },
                    ])
                    ->values()
                    ->all(),
                'doctors' => $this->branchDoctors($patient->branch_id),
            ],
        ]);
    }

    public function index(Patient $patient): JsonResponse
    {
        $this->authorizePatientView($patient);
        $user = request()->user();

        abort_unless($user->can('viewAny', ForeignCountryReferral::class), 403);

        $items = $patient->foreignCountryReferrals()
            ->with(['country:id,name_dr,name_en', 'items.doctor:id,name', 'attachments'])
            ->latest()
            ->get()
            ->map(fn (ForeignCountryReferral $item) => [
                'id' => $item->id,
                'country_name' => $this->resolveCountryName($item),
                'city' => $item->city,
                'hospital' => $item->hospital,
                'passport_no' => $item->passport_no,
                'visa' => $item->visa,
                'time_interval' => $item->time_interval,
                'doctor_names' => $item->items
                    ->map(fn (ForeignCountryReferralItem $referralItem) => $referralItem->doctor?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode(', '),
                'items_count' => $item->items->count(),
                'attachments_count' => $item->attachments->count(),
                'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d H:i') : null,
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $patient, [
            'create' => $user->can('create', ForeignCountryReferral::class),
            'edit' => $user->hasRole(['super_admin', 'admin']) || $user->can('edit-refer-to-foreign-country'),
            'delete' => $user->hasRole(['super_admin', 'admin']) || $user->can('delete-refer-to-foreign-country'),
        ]);
    }

    public function show(Patient $patient, ForeignCountryReferral $foreignCountryReferral): JsonResponse
    {
        $this->authorizePatientView($patient);
        abort_unless((int) $foreignCountryReferral->patient_id === (int) $patient->id, 404);
        $this->authorize('view', $foreignCountryReferral);

        $foreignCountryReferral->load([
            'country:id,name_dr,name_en',
            'items.doctor:id,name',
            'attachments',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->transformReferral($foreignCountryReferral),
        ]);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizePatientView($patient);
        $this->authorize('create', ForeignCountryReferral::class);

        $branchDoctorRule = Rule::exists('doctors', 'id')->where(fn ($query) => $query
            ->where('branch_id', $patient->branch_id)
            ->where('active_status', true));

        $validated = $request->validate([
            'country' => ['required', Rule::in(ForeignCountryReferralCountries::values())],
            'city' => 'nullable|string|max:255',
            'hospital' => 'nullable|string|max:255',
            'passport_no' => 'nullable|string|max:255',
            'visa' => 'nullable|string|max:255',
            'time_interval' => 'nullable|string|max:255',
            'referral_items' => 'required|array|min:1',
            'referral_items.*.doctor_id' => ['required', $branchDoctorRule],
            'referral_items.*.diagnosis' => 'required|string',
            'referral_items.*.doctor_comment' => 'nullable|string',
            'referral_items.*.issue_date' => 'nullable|date',
            'referral_items.*.expire_date' => 'nullable|date',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $referral = ForeignCountryReferral::create([
                'branch_id' => $patient->branch_id,
                'patient_id' => $patient->id,
                'destination_country' => $validated['country'],
                'city' => $validated['city'] ?? null,
                'hospital' => $validated['hospital'] ?? null,
                'passport_no' => $validated['passport_no'] ?? null,
                'visa' => $validated['visa'] ?? null,
                'time_interval' => $validated['time_interval'] ?? null,
            ]);

            foreach ($validated['referral_items'] as $item) {
                ForeignCountryReferralItem::create([
                    'foreign_country_referral_id' => $referral->id,
                    'doctor_id' => $item['doctor_id'],
                    'diagnosis' => $item['diagnosis'],
                    'doctor_comment' => $item['doctor_comment'] ?? null,
                    'issue_date' => $item['issue_date'] ?? null,
                    'expire_date' => $item['expire_date'] ?? null,
                ]);
            }

            $this->persistAttachments($request, $referral);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => localize('global.foreign_country_referral_created_successfully'),
                'data' => ['referral_id' => $referral->id],
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function storeAttachments(Request $request, Patient $patient, ForeignCountryReferral $foreignCountryReferral): JsonResponse
    {
        $this->authorizePatientView($patient);
        abort_unless((int) $foreignCountryReferral->patient_id === (int) $patient->id, 404);
        $this->authorize('update', $foreignCountryReferral);

        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240',
        ]);

        $this->persistAttachments($request, $foreignCountryReferral);

        return response()->json([
            'success' => true,
            'message' => localize('global.files_uploaded_successfully'),
        ]);
    }

    public function destroyItem(
        Patient $patient,
        ForeignCountryReferralItem $foreignCountryReferralItem,
    ): JsonResponse {
        $this->authorizePatientView($patient);
        $foreignCountryReferralItem->loadMissing('referral');
        $referral = $foreignCountryReferralItem->referral;
        abort_unless($referral && (int) $referral->patient_id === (int) $patient->id, 404);
        $this->authorize('update', $referral);

        $foreignCountryReferralItem->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.foreign_country_referral_item_deleted_successfully'),
        ]);
    }

    public function destroyAttachment(
        Patient $patient,
        ForeignCountryReferralAttachment $foreignCountryReferralAttachment,
    ): JsonResponse {
        $this->authorizePatientView($patient);
        $foreignCountryReferralAttachment->loadMissing('referral');
        $referral = $foreignCountryReferralAttachment->referral;
        abort_unless($referral && (int) $referral->patient_id === (int) $patient->id, 404);
        $this->authorize('update', $referral);

        $foreignCountryReferralAttachment->deleteFile();
        $foreignCountryReferralAttachment->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.file_deleted_successfully'),
        ]);
    }

    public function destroy(Patient $patient, ForeignCountryReferral $foreignCountryReferral): JsonResponse
    {
        $this->authorizePatientView($patient);
        abort_unless((int) $foreignCountryReferral->patient_id === (int) $patient->id, 404);
        $this->authorize('delete', $foreignCountryReferral);

        $foreignCountryReferral->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.foreign_country_referral_deleted_successfully'),
        ]);
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function branchDoctors(?int $branchId): array
    {
        return Doctor::query()
            ->where('active_status', true)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Doctor $doctor) => ['id' => $doctor->id, 'name' => $doctor->name])
            ->values()
            ->all();
    }

    private function resolveCountryName(ForeignCountryReferral $referral): ?string
    {
        if ($referral->destination_country) {
            return ForeignCountryReferralCountries::label($referral->destination_country);
        }

        return $referral->country?->name_dr ?? $referral->country?->name_en;
    }

    private function persistAttachments(Request $request, ForeignCountryReferral $referral): void
    {
        foreach ($request->file('files', []) as $file) {
            $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('foreign_country_referral_attachments', $filename, 'public');

            ForeignCountryReferralAttachment::create([
                'foreign_country_referral_id' => $referral->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function transformReferral(ForeignCountryReferral $referral): array
    {
        return [
            'id' => $referral->id,
            'country' => $referral->destination_country,
            'country_name' => $this->resolveCountryName($referral),
            'city' => $referral->city,
            'hospital' => $referral->hospital,
            'passport_no' => $referral->passport_no,
            'visa' => $referral->visa,
            'time_interval' => $referral->time_interval,
            'created_at' => $referral->created_at ? verta($referral->created_at)->format('Y-m-d H:i') : null,
            'items' => $referral->items->map(fn (ForeignCountryReferralItem $item) => [
                'id' => $item->id,
                'doctor_id' => $item->doctor_id,
                'doctor_name' => $item->doctor?->name,
                'diagnosis' => $item->diagnosis,
                'doctor_comment' => $item->doctor_comment,
                'issue_date' => $item->issue_date?->format('Y-m-d'),
                'expire_date' => $item->expire_date?->format('Y-m-d'),
            ])->values()->all(),
            'attachments' => $referral->attachments->map(fn (ForeignCountryReferralAttachment $attachment) => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_url' => $attachment->file_url,
                'file_type' => $attachment->file_type,
            ])->values()->all(),
        ];
    }
}
