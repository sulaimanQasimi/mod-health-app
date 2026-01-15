<?php

namespace App\Http\Controllers;

use App\Models\LabTestParameter;
use App\Models\LabTest;
use App\Models\LabType;
use App\Models\Patient;
use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use App\Models\TestCategory;
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
    public function getTestList()
    {
        $tests = PatientTestRegistration::with(['testable.patient', 'labTest', 'doctor', 'branch'])
            ->orderBy('id', 'desc')
            ->get();

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
     * Display test registration report grouped by test type and date
     */
    public function report(Request $request)
    {
        $items = null;
        
        // Only query if there are search parameters
        if ($request->hasAny(['from', 'to', 'per_page'])) {
            $perPage = $request->get('per_page', 15);
            
            // Build base query
            $query = PatientTestRegistration::with(['labType'])
                ->select([
                    'patient_test_registrations.id',
                    'patient_test_registrations.lab_type_id',
                    'patient_test_registrations.registration_date',
                    DB::raw('DATE(patient_test_registrations.registration_date) as date_only')
                ]);

            // Apply date range filter - Convert Persian to Gregorian
            if ($request->filled('from') && $request->filled('to')) {
                try {
                    $fromDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->from)
                        ->toCarbon()
                        ->format('Y-m-d');
                    $toDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->to)
                        ->toCarbon()
                        ->format('Y-m-d');
                    
                    $query->whereDate('registration_date', '>=', $fromDate)
                          ->whereDate('registration_date', '<=', $toDate);
                } catch (\Exception $e) {
                    // If conversion fails, try as Gregorian date
                    try {
                        $query->whereDate('registration_date', '>=', $request->from)
                              ->whereDate('registration_date', '<=', $request->to);
                    } catch (\Exception $e2) {
                        // Invalid date format, skip date filter
                    }
                }
            } elseif ($request->filled('from')) {
                try {
                    $fromDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->from)
                        ->toCarbon()
                        ->format('Y-m-d');
                    $query->whereDate('registration_date', '>=', $fromDate);
                } catch (\Exception $e) {
                    try {
                        $query->whereDate('registration_date', '>=', $request->from);
                    } catch (\Exception $e2) {
                        // Invalid date format, skip date filter
                    }
                }
            } elseif ($request->filled('to')) {
                try {
                    $toDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->to)
                        ->toCarbon()
                        ->format('Y-m-d');
                    $query->whereDate('registration_date', '<=', $toDate);
                } catch (\Exception $e) {
                    try {
                        $query->whereDate('registration_date', '<=', $request->to);
                    } catch (\Exception $e2) {
                        // Invalid date format, skip date filter
                    }
                }
            }

            // Get all results first to group them
            $allRegistrations = $query->get();
            
            // Group by lab_type_id and date
            $groupedData = $allRegistrations->groupBy(function ($item) {
                return $item->lab_type_id . '_' . $item->date_only;
            })->map(function ($group) {
                $first = $group->first();
                return [
                    'lab_type_id' => $first->lab_type_id,
                    'lab_type_name' => $first->labType ? $first->labType->name : 'Unknown',
                    'date' => $first->date_only,
                    'count' => $group->count(),
                ];
            })->values();

            // Group by test type for pagination
            $groupedByTestType = $groupedData->groupBy('lab_type_id')->map(function ($dates, $labTypeId) {
                $firstDate = $dates->first();
                return [
                    'lab_type_id' => $labTypeId,
                    'lab_type_name' => $firstDate['lab_type_name'],
                    'dates' => $dates->sortBy('date')->values(),
                    'total_count' => $dates->sum('count'),
                ];
            })->values();

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

        return view('pages.laboratory.registrations.report', compact('items'));
    }

    /**
     * Export test registration report to Excel or PDF
     */
    public function exportReport(Request $request)
    {
        $perPage = $request->get('per_page', 'all');
        
        // Build base query (same as report method)
        $query = PatientTestRegistration::with(['labType'])
            ->select([
                'patient_test_registrations.id',
                'patient_test_registrations.lab_type_id',
                'patient_test_registrations.registration_date',
                DB::raw('DATE(patient_test_registrations.registration_date) as date_only')
            ]);

        // Apply date range filter - Convert Persian to Gregorian
        if ($request->filled('from') && $request->filled('to')) {
            try {
                $fromDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->from)
                    ->toCarbon()
                    ->format('Y-m-d');
                $toDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->to)
                    ->toCarbon()
                    ->format('Y-m-d');
                
                $query->whereDate('registration_date', '>=', $fromDate)
                      ->whereDate('registration_date', '<=', $toDate);
            } catch (\Exception $e) {
                try {
                    $query->whereDate('registration_date', '>=', $request->from)
                          ->whereDate('registration_date', '<=', $request->to);
                } catch (\Exception $e2) {
                    // Invalid date format, skip date filter
                }
            }
        } elseif ($request->filled('from')) {
            try {
                $fromDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->from)
                    ->toCarbon()
                    ->format('Y-m-d');
                $query->whereDate('registration_date', '>=', $fromDate);
            } catch (\Exception $e) {
                try {
                    $query->whereDate('registration_date', '>=', $request->from);
                } catch (\Exception $e2) {
                    // Invalid date format, skip date filter
                }
            }
        } elseif ($request->filled('to')) {
            try {
                $toDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->to)
                    ->toCarbon()
                    ->format('Y-m-d');
                $query->whereDate('registration_date', '<=', $toDate);
            } catch (\Exception $e) {
                try {
                    $query->whereDate('registration_date', '<=', $request->to);
                } catch (\Exception $e2) {
                    // Invalid date format, skip date filter
                }
            }
        }

        // Get all results and group them
        $allRegistrations = $query->get();
        
        // Group by lab_type_id and date
        $groupedData = $allRegistrations->groupBy(function ($item) {
            return $item->lab_type_id . '_' . $item->date_only;
        })->map(function ($group) {
            $first = $group->first();
            return [
                'lab_type_id' => $first->lab_type_id,
                'lab_type_name' => $first->labType ? $first->labType->name : 'Unknown',
                'date' => $first->date_only,
                'count' => $group->count(),
            ];
        })->values();

        // Flatten for export - create rows for each date entry
        $exportData = [];
        foreach ($groupedData as $item) {
            try {
                $vertaDate = \Hekmatinasser\Verta\Facades\Verta::createFromFormat('Y-m-d', $item['date']);
                $persianDate = $vertaDate->format('Y/m/d');
            } catch (\Exception $e) {
                $persianDate = $item['date'];
            }
            
            $exportData[] = [
                'test_type' => $item['lab_type_name'],
                'date' => $persianDate,
                'count' => $item['count'],
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
                $sheet->setCellValue('C1', localize('global.date'));
                $sheet->setCellValue('D1', localize('global.count'));
                
                // Style headers
                $sheet->getStyle('A1:D1')->getFont()->setBold(true);
                $sheet->getStyle('A1:D1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                
                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(15);
                
                // Add data
                $row = 2;
                foreach ($exportData as $index => $item) {
                    $sheet->setCellValue('A' . $row, $index + 1);
                    $sheet->setCellValue('B' . $row, $item['test_type']);
                    $sheet->setCellValue('C' . $row, $item['date']);
                    $sheet->setCellValue('D' . $row, $item['count']);
                    $row++;
                }
                
                // Apply text wrapping
                if ($row > 2) {
                    $sheet->getStyle('A2:D' . ($row - 1))
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