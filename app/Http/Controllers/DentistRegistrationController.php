<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistRegistration;
use App\Models\Doctor;
use App\Models\Branch;
use Illuminate\Http\Request;

class DentistRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DentistRegistration::with(['appointment.patient', 'dentist', 'branch']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by dentist
        if ($request->filled('dentist_id')) {
            $query->where('dentist_id', $request->dentist_id);
        }

        // Search by patient name
        if ($request->filled('patient_name')) {
            $query->whereHas('appointment.patient', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->patient_name . '%')
                  ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
            });
        }

        $registrations = $query->latest()->paginate(25)->withQueryString();
        $branches = Branch::all();
        // Load all doctors (not just dentists)
        $dentists = Doctor::where('active_status', true)->get();

        return view('pages.dentist.registrations.index', compact('registrations', 'branches', 'dentists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Appointment $appointment)
    {
        // Load all active doctors (not just dentists)
        $dentists = Doctor::where('active_status', true)->get();

        return view('pages.dentist.registrations.create', compact('appointment', 'dentists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Appointment $appointment)
    {
        $validatedData = $request->validate([
            'dentist_id' => 'nullable|exists:doctors,id',
            'registration_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validatedData['appointment_id'] = $appointment->id;
        $validatedData['branch_id'] = $appointment->branch_id ?? auth()->user()->branch_id;

        $registration = DentistRegistration::create($validatedData);

        return redirect()->route('dentist-registrations.show', $registration)
            ->with('success', localize('global.dentist_registration_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->load([
            'appointment.patient',
            'dentist',
            'branch',
            'examinations',
            'treatments',
            'xrays',
            'dentalNotes',
            'dentalCharts.measurements',
            'dentalCharts.images',
            'dentalCharts.periodontalMeasurements'
        ]);

        // Get all teeth data for the visual chart
        $allTeeth = [];
        for ($i = 11; $i <= 18; $i++) $allTeeth[$i] = null; // Upper right
        for ($i = 21; $i <= 28; $i++) $allTeeth[$i] = null; // Upper left
        for ($i = 31; $i <= 38; $i++) $allTeeth[$i] = null; // Lower left
        for ($i = 41; $i <= 48; $i++) $allTeeth[$i] = null; // Lower right

        // Get latest chart entry for each tooth
        $latestCharts = $dentistRegistration->dentalCharts()
            ->with(['images', 'periodontalMeasurements'])
            ->orderBy('chart_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('tooth_number')
            ->keyBy('tooth_number');

        foreach ($latestCharts as $toothNumber => $chart) {
            $allTeeth[$toothNumber] = $chart;
        }

        return view('pages.dentist.registrations.show', compact('dentistRegistration', 'allTeeth', 'latestCharts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DentistRegistration $dentistRegistration)
    {
        // Load all active doctors (not just dentists)
        $dentists = Doctor::where('active_status', true)->get();

        return view('pages.dentist.registrations.edit', compact('dentistRegistration', 'dentists'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'dentist_id' => 'nullable|exists:doctors,id',
            'registration_date' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $dentistRegistration->update($validatedData);

        return redirect()->route('dentist-registrations.show', $dentistRegistration)
            ->with('success', localize('global.dentist_registration_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->delete();

        return redirect()->route('dentist-registrations.index')
            ->with('success', localize('global.dentist_registration_deleted_successfully'));
    }

    /**
     * Mark registration as completed
     */
    public function markCompleted(DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->markCompleted();

        return redirect()->back()->with('success', localize('global.registration_marked_completed'));
    }

    /**
     * Mark registration as in progress
     */
    public function markInProgress(DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->markInProgress();

        return redirect()->back()->with('success', localize('global.registration_marked_in_progress'));
    }

    /**
     * Cancel registration
     */
    public function cancel(DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->cancel();

        return redirect()->back()->with('success', localize('global.registration_cancelled'));
    }
}
