<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\LabTestParameter;
use App\Models\LabTest;
use App\Models\LabType;
use App\Models\Patient;
use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use App\Models\Section;
use App\Models\TestCategory;
use App\Models\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Patient Test Registration Controller
 * 
 * Handles patient test registration operations
 */
class PatientTestRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:register-patient-tests');
    }


    /**
     * Get test list for display
     */
    public function getTestList(Request $request)
    {
        $query = PatientTestRegistration::with(['testable.patient', 'labTest', 'doctor', 'branch'])
            ->orderBy('id', 'desc');

        if ($request->filled('patient_id')) {
            $query->whereHas('testable', function ($q) use ($request) {
                $q->whereHas('patient', function ($patientQ) use ($request) {
                    $patientQ->where('id', $request->patient_id);
                });
            });
        }

        $tests = $query->get();

        return view('pages.laboratory.registrations.index', compact('tests'));
    }



    /**
     * Mark registration as in progress
     */
    public function markInProgress($id)
    {
        $registration = PatientTestRegistration::findOrFail($id);
        $registration->markInProgress();
        
        return redirect()->back()->with('success', localize('global.test_registration_marked_in_progress'));
    }

    /**
     * Mark registration as completed
     */
    public function markCompleted($id)
    {
        $registration = PatientTestRegistration::findOrFail($id);
        $registration->markCompleted();
        
        return redirect()->back()->with('success', localize('global.test_registration_marked_completed'));
    }

    /**
     * Cancel registration
     */
    public function cancel($id)
    {
        $registration = PatientTestRegistration::findOrFail($id);
        $registration->cancel();
        
        return redirect()->back()->with('success', localize('global.test_registration_cancelled'));
    }

    /**
     * Display test registration report grouped by test type (counts only, no date breakdown)
     */
    public function report(Request $request)
    {
        $items = null;
        
        // Get all lab types for dropdown
        $labTypes = LabType::orderBy('name')->get();
        
        // Only query if there are search parameters
        if ($request->hasAny(['from', 'to', 'test_type', 'per_page', 'patient_id'])) {
            $perPage = $request->get('per_page', 15);
            
            // Build base query
            $query = PatientTestRegistration::with(['labType'])
                ->select([
                    'patient_test_registrations.id',
                    'patient_test_registrations.lab_type_id',
                ]);

            // Apply patient_id filter
            if ($request->filled('patient_id')) {
                $query->whereHas('testable', function ($q) use ($request) {
                    $q->whereHas('patient', function ($patientQ) use ($request) {
                        $patientQ->where('id', $request->patient_id);
                    });
                });
            }

            // Apply test type filter
            if ($request->filled('test_type')) {
                $query->where('lab_type_id', $request->test_type);
            }

            // Apply date range filter - Parse Jalali/Dari from datepicker_dari (Verta handles Persian numerals and common formats)
            if ($request->filled('from') && $request->filled('to')) {
                try {
                    $fromDate = Verta::parse($request->from)->datetime();
                    $toDate = Verta::parse($request->to)->datetime();
                    $query->whereDate('registration_date', '>=', $fromDate)
                          ->whereDate('registration_date', '<=', $toDate);
                } catch (\Exception $e) {
                    // If Verta parse fails, try as Gregorian Y-m-d
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->to)) {
                        $query->whereDate('registration_date', '>=', $request->from)
                              ->whereDate('registration_date', '<=', $request->to);
                    }
                }
            } elseif ($request->filled('from')) {
                try {
                    $fromDate = Verta::parse($request->from)->datetime();
                    $query->whereDate('registration_date', '>=', $fromDate);
                } catch (\Exception $e) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->from)) {
                        $query->whereDate('registration_date', '>=', $request->from);
                    }
                }
            } elseif ($request->filled('to')) {
                try {
                    $toDate = Verta::parse($request->to)->datetime();
                    $query->whereDate('registration_date', '<=', $toDate);
                } catch (\Exception $e) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->to)) {
                        $query->whereDate('registration_date', '<=', $request->to);
                    }
                }
            }

            // Group by lab_type_id and count (no date breakdown)
            $groupedByTestType = $query->get()
                ->groupBy('lab_type_id')
                ->map(function ($group, $labTypeId) {
                    $first = $group->first();
                    return [
                        'lab_type_id' => $labTypeId,
                        'lab_type_name' => $first->labType ? $first->labType->name : 'Unknown',
                        'total_count' => $group->count(),
                    ];
                })
                ->values()
                ->sortBy('lab_type_name')
                ->values();

            // Handle pagination with "all" option
            if ($perPage === 'all') {
                $items = $groupedByTestType;
            } else {
                $currentPage = $request->get('page', 1);
                $perPageInt = (int) $perPage;
                $total = $groupedByTestType->count();
                $offset = ($currentPage - 1) * $perPageInt;
                $items = $groupedByTestType->slice($offset, $perPageInt)->values();
                
                // Create paginator manually
                $items = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $total,
                    $perPageInt,
                    $currentPage,
                    [
                        'path' => $request->url(),
                        'pageName' => 'page',
                    ]
                );
                
                // Preserve query parameters in pagination links
                $items->appends($request->query());
            }
        }

        return view('pages.laboratory.registrations.report', compact('items', 'labTypes'));
    }

    /**
     * Build base query with filters for report (shared by report and reportDetailed).
     */
    private function reportQuery(Request $request)
    {
        $query = PatientTestRegistration::query();

        if ($request->filled('patient_id')) {
            $query->whereHas('testable', function ($q) use ($request) {
                $q->whereHas('patient', function ($patientQ) use ($request) {
                    $patientQ->where('id', $request->patient_id);
                });
            });
        }

        if ($request->filled('test_type')) {
            $query->where('lab_type_id', $request->test_type);
        }

        if ($request->filled('from') && $request->filled('to')) {
            try {
                $fromDate = Verta::parse($request->from)->datetime();
                $toDate = Verta::parse($request->to)->datetime();
                $query->whereDate('registration_date', '>=', $fromDate)
                      ->whereDate('registration_date', '<=', $toDate);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->to)) {
                    $query->whereDate('registration_date', '>=', $request->from)
                          ->whereDate('registration_date', '<=', $request->to);
                }
            }
        } elseif ($request->filled('from')) {
            try {
                $fromDate = Verta::parse($request->from)->datetime();
                $query->whereDate('registration_date', '>=', $fromDate);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->from)) {
                    $query->whereDate('registration_date', '>=', $request->from);
                }
            }
        } elseif ($request->filled('to')) {
            try {
                $toDate = Verta::parse($request->to)->datetime();
                $query->whereDate('registration_date', '<=', $toDate);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->to)) {
                    $query->whereDate('registration_date', '<=', $request->to);
                }
            }
        }

        // Detailed report filters: doctor, branch, department, created_by, updated_by, completed_by, assigned_to, assigned_section, notes, completed_at, assigned_at
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('assignedSection', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }
        if ($request->filled('updated_by')) {
            $query->where('updated_by', $request->updated_by);
        }
        if ($request->filled('completed_by')) {
            $query->where('completed_by', $request->completed_by);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('assigned_section_id')) {
            $query->where('assigned_section_id', $request->assigned_section_id);
        }
        if ($request->filled('notes')) {
            $query->where(function ($q) use ($request) {
                $q->where('notes', 'like', '%' . $request->notes . '%')
                  ->orWhere('detailed_notes', 'like', '%' . $request->notes . '%');
            });
        }
        // Completed at date range
        if ($request->filled('completed_at_from') && $request->filled('completed_at_to')) {
            try {
                $from = Verta::parse($request->completed_at_from)->datetime();
                $to = Verta::parse($request->completed_at_to)->datetime();
                $query->whereDate('completed_at', '>=', $from)->whereDate('completed_at', '<=', $to);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->completed_at_from ?? '') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->completed_at_to ?? '')) {
                    $query->whereDate('completed_at', '>=', $request->completed_at_from)->whereDate('completed_at', '<=', $request->completed_at_to);
                }
            }
        } elseif ($request->filled('completed_at_from')) {
            try {
                $from = Verta::parse($request->completed_at_from)->datetime();
                $query->whereDate('completed_at', '>=', $from);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->completed_at_from ?? '')) {
                    $query->whereDate('completed_at', '>=', $request->completed_at_from);
                }
            }
        } elseif ($request->filled('completed_at_to')) {
            try {
                $to = Verta::parse($request->completed_at_to)->datetime();
                $query->whereDate('completed_at', '<=', $to);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->completed_at_to ?? '')) {
                    $query->whereDate('completed_at', '<=', $request->completed_at_to);
                }
            }
        }
        // Assigned at date range
        if ($request->filled('assigned_at_from') && $request->filled('assigned_at_to')) {
            try {
                $from = Verta::parse($request->assigned_at_from)->datetime();
                $to = Verta::parse($request->assigned_at_to)->datetime();
                $query->whereDate('assigned_at', '>=', $from)->whereDate('assigned_at', '<=', $to);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->assigned_at_from ?? '') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->assigned_at_to ?? '')) {
                    $query->whereDate('assigned_at', '>=', $request->assigned_at_from)->whereDate('assigned_at', '<=', $request->assigned_at_to);
                }
            }
        } elseif ($request->filled('assigned_at_from')) {
            try {
                $from = Verta::parse($request->assigned_at_from)->datetime();
                $query->whereDate('assigned_at', '>=', $from);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->assigned_at_from ?? '')) {
                    $query->whereDate('assigned_at', '>=', $request->assigned_at_from);
                }
            }
        } elseif ($request->filled('assigned_at_to')) {
            try {
                $to = Verta::parse($request->assigned_at_to)->datetime();
                $query->whereDate('assigned_at', '<=', $to);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->assigned_at_to ?? '')) {
                    $query->whereDate('assigned_at', '<=', $request->assigned_at_to);
                }
            }
        }

        return $query;
    }

    /**
     * Full detailed test registration report: all rows, no grouping, with who processed (created_by, updated_by, completed_by, assigned_to).
     */
    public function reportDetailed(Request $request)
    {
        $labTypes = LabType::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $doctors = Doctor::where('active_status', true)->orderBy('name')->get(['id', 'name']);
        $users = User::orderBy('name')->get(['id', 'name']);
        $sections = Section::with('department')->orderBy('name')->get(['id', 'name', 'department_id']);
        $items = null;

        $filterKeys = ['from', 'to', 'test_type', 'per_page', 'patient_id', 'doctor_id', 'branch_id', 'department_id', 'created_by', 'updated_by', 'completed_by', 'completed_at_from', 'completed_at_to', 'assigned_to', 'assigned_at_from', 'assigned_at_to', 'assigned_section_id', 'notes'];
        if ($request->hasAny($filterKeys)) {
            $query = $this->reportQuery($request)
                ->with([
                    'testable.patient',
                    'labType',
                    'doctor',
                    'branch',
                    'creator',
                    'updater',
                    'completedBy',
                    'assignedTo',
                    'assignedSection.department',
                ])
                ->orderBy('patient_test_registrations.registration_date', 'desc')
                ->orderBy('patient_test_registrations.id', 'desc');

            $perPage = $request->get('per_page', 15);
            if ($perPage === 'all') {
                $items = $query->get();
            } else {
                $perPageInt = (int) $perPage;
                $perPageInt = in_array($perPageInt, [10, 15, 25, 50, 100]) ? $perPageInt : 15;
                $items = $query->paginate($perPageInt)->withQueryString();
            }
        }

        return view('pages.laboratory.registrations.report_detailed', compact('items', 'labTypes', 'branches', 'departments', 'doctors', 'users', 'sections'));
    }

    /**
     * Export detailed test registration report to Excel or PDF (all rows, who processed).
     */
    public function exportReportDetailed(Request $request)
    {
        $query = $this->reportQuery($request)
            ->with([
                'testable.patient',
                'labType',
                'doctor',
                'branch',
                'creator',
                'updater',
                'completedBy',
                'assignedTo',
                'assignedSection.department',
            ])
            ->orderBy('patient_test_registrations.registration_date', 'desc')
            ->orderBy('patient_test_registrations.id', 'desc');

        $items = $query->get();

        if ($items->isEmpty()) {
            return redirect()->route('laboratory.registrations.report-detailed', $request->except(['type']))
                ->with('error', localize('global.no_item_is_found'));
        }

        try {
            $exportType = $request->input('type');
            $exportData = $items->map(function ($row) {
                $patientName = $row->testable && method_exists($row->testable, 'patient') && $row->testable->patient
                    ? $row->testable->patient->name
                    : '—';
                $regDate = $row->registration_date
                    ? \Hekmatinasser\Verta\Verta::instance($row->registration_date)->format('Y/m/d')
                    : '—';
                $completedAt = $row->completed_at
                    ? \Hekmatinasser\Verta\Verta::instance($row->completed_at)->format('Y/m/d H:i')
                    : '—';
                $assignedAt = $row->assigned_at
                    ? \Hekmatinasser\Verta\Verta::instance($row->assigned_at)->format('Y/m/d H:i')
                    : '—';
                return [
                    'ref_no' => $row->ref_no ?? '—',
                    'registration_date' => $regDate,
                    'patient_name' => $patientName,
                    'lab_type' => $row->labType ? $row->labType->name : '—',
                    'status' => $row->status ?? '—',
                    'priority' => $row->priority ?? '—',
                    'doctor' => $row->doctor ? $row->doctor->name : '—',
                    'branch' => $row->branch ? $row->branch->name : '—',
                    'created_by' => $row->creator ? $row->creator->name : '—',
                    'updated_by' => $row->updater ? $row->updater->name : '—',
                    'completed_by' => $row->completedBy ? $row->completedBy->name : '—',
                    'completed_at' => $completedAt,
                    'assigned_to' => $row->assignedTo ? $row->assignedTo->name : '—',
                    'assigned_at' => $assignedAt,
                    'assigned_section' => $row->assignedSection ? $row->assignedSection->name : '—',
                    'department' => $row->assignedSection && $row->assignedSection->department ? $row->assignedSection->department->name : '—',
                    'notes' => $row->notes ?? '—',
                ];
            })->toArray();

            if ($exportType === 'pdf') {
                $html = view('pages.laboratory.registrations.pdf_report_detailed', ['items' => $exportData])->render();
                $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
                $mpdf->WriteHTML($html);
                $mpdf->Output('test_registration_report_detailed.pdf', 'D');
                exit;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'A' => localize('global.ref_no') ?? 'Ref No',
                'B' => localize('global.registration_date') ?? 'Registration Date',
                'C' => localize('global.patient_name') ?? 'Patient',
                'D' => localize('global.test_type') ?? 'Test Type',
                'E' => localize('global.status') ?? 'Status',
                'F' => localize('global.priority') ?? 'Priority',
                'G' => localize('global.doctor') ?? 'Doctor',
                'H' => localize('global.branch') ?? 'Branch',
                'I' => localize('global.department') ?? 'Department',
                'J' => localize('global.created_by') ?? 'Created By',
                'K' => localize('global.updated_by') ?? 'Updated By',
                'L' => 'Completed By',
                'M' => 'Completed At',
                'N' => localize('global.assigned_to') ?? 'Assigned To',
                'O' => 'Assigned At',
                'P' => 'Assigned Section',
                'Q' => localize('global.notes') ?? 'Notes',
            ];
            foreach ($headers as $key => $label) {
                $sheet->setCellValue($key . '1', $label);
            }
            $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
            $sheet->getStyle('A1:Q1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $row = 2;
            foreach ($exportData as $item) {
                $sheet->setCellValue('A' . $row, $item['ref_no']);
                $sheet->setCellValue('B' . $row, $item['registration_date']);
                $sheet->setCellValue('C' . $row, $item['patient_name']);
                $sheet->setCellValue('D' . $row, $item['lab_type']);
                $sheet->setCellValue('E' . $row, $item['status']);
                $sheet->setCellValue('F' . $row, $item['priority']);
                $sheet->setCellValue('G' . $row, $item['doctor']);
                $sheet->setCellValue('H' . $row, $item['branch']);
                $sheet->setCellValue('I' . $row, $item['department']);
                $sheet->setCellValue('J' . $row, $item['created_by']);
                $sheet->setCellValue('K' . $row, $item['updated_by']);
                $sheet->setCellValue('L' . $row, $item['completed_by']);
                $sheet->setCellValue('M' . $row, $item['completed_at']);
                $sheet->setCellValue('N' . $row, $item['assigned_to']);
                $sheet->setCellValue('O' . $row, $item['assigned_at']);
                $sheet->setCellValue('P' . $row, $item['assigned_section']);
                $sheet->setCellValue('Q' . $row, $item['notes']);
                $row++;
            }
            foreach (range('A', 'Q') as $c) {
                $sheet->getColumnDimension($c)->setWidth(18);
            }
            if ($row > 2) {
                $sheet->getStyle('A2:Q' . ($row - 1))->getAlignment()->setWrapText(true);
            }
            return $this->exportResponseDetailed($spreadsheet);
        } catch (\Exception $e) {
            \Log::error('Test registration detailed export error: ' . $e->getMessage());
            return redirect()->route('laboratory.registrations.report-detailed', $request->except(['type']))
                ->with('error', 'خطا در صادرات گزارش: ' . $e->getMessage());
        }
    }

    private function exportResponseDetailed($spreadsheet)
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $filename = 'test_registration_report_detailed_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    /**
     * Export test registration report to Excel or PDF
     */
    public function exportReport(Request $request)
    {
        // Build base query (same as report method)
        $query = PatientTestRegistration::with(['labType'])
            ->select([
                'patient_test_registrations.id',
                'patient_test_registrations.lab_type_id',
            ]);

        // Apply patient_id filter
        if ($request->filled('patient_id')) {
            $query->whereHas('testable', function ($q) use ($request) {
                $q->whereHas('patient', function ($patientQ) use ($request) {
                    $patientQ->where('id', $request->patient_id);
                });
            });
        }

        // Apply test type filter
        if ($request->filled('test_type')) {
            $query->where('lab_type_id', $request->test_type);
        }

        // Apply date range filter - Parse Jalali/Dari from datepicker_dari (Verta handles Persian numerals and common formats)
        if ($request->filled('from') && $request->filled('to')) {
            try {
                $fromDate = Verta::parse($request->from)->datetime();
                $toDate = Verta::parse($request->to)->datetime();
                $query->whereDate('registration_date', '>=', $fromDate)
                      ->whereDate('registration_date', '<=', $toDate);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->to)) {
                    $query->whereDate('registration_date', '>=', $request->from)
                          ->whereDate('registration_date', '<=', $request->to);
                }
            }
        } elseif ($request->filled('from')) {
            try {
                $fromDate = Verta::parse($request->from)->datetime();
                $query->whereDate('registration_date', '>=', $fromDate);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->from)) {
                    $query->whereDate('registration_date', '>=', $request->from);
                }
            }
        } elseif ($request->filled('to')) {
            try {
                $toDate = Verta::parse($request->to)->datetime();
                $query->whereDate('registration_date', '<=', $toDate);
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->to)) {
                    $query->whereDate('registration_date', '<=', $request->to);
                }
            }
        }

        // Group by lab_type_id and count (no date breakdown)
        $groupedByTestType = $query->get()
            ->groupBy('lab_type_id')
            ->map(function ($group, $labTypeId) {
                $first = $group->first();
                return [
                    'lab_type_id' => $labTypeId,
                    'lab_type_name' => $first->labType ? $first->labType->name : 'Unknown',
                    'total_count' => $group->count(),
                ];
            })
            ->values()
            ->sortBy('lab_type_name')
            ->values();

        // Prepare export data - only test type and count
        $exportData = [];
        foreach ($groupedByTestType as $item) {
            $exportData[] = [
                'test_type' => $item['lab_type_name'],
                'count' => $item['total_count'],
            ];
        }

        // Check if there are any items to export
        if (empty($exportData)) {
            return redirect()->route('laboratory.registrations.report', $request->except(['type']))
                ->with('error', localize('global.no_item_is_found'));
        }

        try {
            $exportType = $request->input('type');
            
            if ($exportType === 'pdf') {
                $html = view('pages.laboratory.registrations.pdf_report', ['items' => $exportData])->render();
                $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
                $mpdf->WriteHTML($html);
                $mpdf->Output('test_registration_report.pdf', 'D');
                exit;
            } else {
                // Excel export
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                
                // Set headers
                $sheet->setCellValue('A1', localize('global.number'));
                $sheet->setCellValue('B1', localize('global.test_type'));
                $sheet->setCellValue('C1', localize('global.count'));
                
                // Style headers
                $sheet->getStyle('A1:C1')->getFont()->setBold(true);
                $sheet->getStyle('A1:C1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                
                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(15);
                
                // Add data
                $row = 2;
                foreach ($exportData as $index => $item) {
                    $sheet->setCellValue('A' . $row, $index + 1);
                    $sheet->setCellValue('B' . $row, $item['test_type']);
                    $sheet->setCellValue('C' . $row, $item['count']);
                    $row++;
                }
                
                // Apply text wrapping
                if ($row > 2) {
                    $sheet->getStyle('A2:C' . ($row - 1))
                        ->getAlignment()
                        ->setWrapText(true);
                }
                
                return $this->exportResponse($spreadsheet);
            }
        } catch (\Exception $e) {
            \Log::error('Test registration export error: ' . $e->getMessage());
            return redirect()->route('laboratory.registrations.report', $request->except(['type']))
                ->with('error', 'خطا در صادرات گزارش: ' . $e->getMessage());
        }
    }

    /**
     * Generate Excel export response
     */
    private function exportResponse($spreadsheet)
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $filename = 'test_registration_report_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
}