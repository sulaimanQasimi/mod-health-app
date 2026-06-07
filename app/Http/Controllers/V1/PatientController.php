<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PatientController as LegacyPatientController;
use App\Models\District;
use App\Models\Patient;
use App\Services\PatientFormDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    public function index()
    {
        return Inertia::render('Placeholder', [
            'title' => 'global.patients_list',
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Patient::class);

        return Inertia::render('Patients/Create', [
            'formData' => app(PatientFormDataService::class)->createFormData($request->user()),
            'urls' => [
                'store' => route('react.patients.store'),
                'districts' => url('/react/patients/districts'),
                'doctorsByDepartment' => url('/react/patients/doctors-by-department'),
                'back' => route('react.patients.index'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);

        $proxy = $request->duplicate();
        $proxy->headers->set('X-Requested-With', 'XMLHttpRequest');
        $proxy->headers->set('Accept', 'application/json');

        return app(LegacyPatientController::class)->store($proxy);
    }

    public function districts(int $provinceId): JsonResponse
    {
        $this->authorize('create', Patient::class);

        $districts = District::query()
            ->where('province_id', $provinceId)
            ->orderBy('name_dr')
            ->get(['id', 'name_dr']);

        return response()->json([
            'success' => true,
            'districts' => $districts,
        ]);
    }

    public function doctorsByDepartment(int $departmentId, Request $request): JsonResponse
    {
        $this->authorize('create', Patient::class);

        return app(LegacyPatientController::class)->getDoctorsByDepartment($departmentId, $request);
    }

    public function report()
    {
        return Inertia::render('Placeholder', [
            'title' => 'global.reports',
        ]);
    }
}
