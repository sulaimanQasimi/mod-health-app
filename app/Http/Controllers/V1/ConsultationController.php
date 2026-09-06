<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConsultationController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        abort_unless(
            $request->user()?->can('show-my-consultations-menu')
                || $request->user()?->hasRole(['super_admin', 'admin']),
            403,
        );

        $departmentId = $request->user()?->department_id;

        $query = Consultation::query()
            ->with([
                'user:id,name,department_id',
                'user.department:id,name',
                'appointment:id,patient_id',
                'appointment.patient:id,name,father_name,id_card',
            ]);

        if ($departmentId !== null && $departmentId !== '') {
            $query->whereRaw('JSON_CONTAINS(department_id, ?)', [json_encode((string) $departmentId)]);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', "%{$search}%")
                    ->orWhereHas('appointment.patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('father_name', 'like', "%{$search}%")
                            ->orWhere('id_card', 'like', "%{$search}%");
                    });
            });
        }

        $paginator = $this->paginateQuery($query->orderByDesc('date')->orderByDesc('id'), $request, 10);

        return Inertia::render('Consultations/Index', [
            'consultations' => $this->paginationPayload($paginator, function (Consultation $consultation) {
                $patient = $consultation->appointment?->patient;

                return [
                    'id' => $consultation->id,
                    'title' => $consultation->title,
                    'date' => $consultation->date ? verta($consultation->date)->format('Y-m-d') : null,
                    'time' => $consultation->time,
                    'consultation_type' => (int) ($consultation->consultation_type ?? 0),
                    'department_name' => $consultation->user?->department?->name,
                    'patient_name' => $patient?->name,
                    'father_name' => $patient?->father_name,
                    'card_number' => $patient?->id_card,
                    'show_url' => route('consultations.show', $consultation),
                ];
            }),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'urls' => [
                'index' => route('consultations.index'),
            ],
        ]);
    }
}
