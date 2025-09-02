<?php

namespace App\Http\Controllers;

use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyType;
use App\Models\User;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PhysiotherapyReportExport;


class PhysiotherapyReportController extends Controller
{
    public function index()
    {
        return view('pages.physiotherapy.reports.index');
    }

    public function generateReport(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            $startDate = Verta::parse($request->start_date)->datetime();
            $endDate = Verta::parse($request->end_date)->datetime();

            \Log::info('Generating physiotherapy report', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'request_start' => $request->start_date,
                'request_end' => $request->end_date
            ]);

            // Generate all report types for the date range
            $data = [
                'summary' => $this->generateSummaryReport($startDate, $endDate),
                'detailed' => $this->generateDetailedReport($startDate, $endDate),
                'by_type' => $this->generateByTypeReport($startDate, $endDate),
                'by_physiotherapist' => $this->generateByPhysiotherapistReport($startDate, $endDate)
            ];

            \Log::info('Generated report data structure', [
                'data_keys' => array_keys($data),
                'summary_count' => isset($data['summary']['procedures']) ? $data['summary']['procedures']->count() : 0,
                'detailed_count' => $data['detailed']->count(),
                'by_type_count' => $data['by_type']->count(),
                'by_physiotherapist_count' => isset($data['by_physiotherapist']['physiotherapists']) ? $data['by_physiotherapist']['physiotherapists']->count() : 0
            ]);

            return view('pages.physiotherapy.reports.result', compact('data', 'startDate', 'endDate'));
            
        } catch (\Exception $e) {
            \Log::error('Error generating physiotherapy report: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Error generating report: ' . $e->getMessage()]);
        }
    }

    public function generateSummaryReport($startDate, $endDate)
    {
        $procedures = PhysiotherapyProcedure::whereBetween('start_date', [$startDate, $endDate])
            ->with(['appointment.patient', 'physiotherapyType', 'physiotherapist'])
            ->get();

        $totalProcedures = $procedures->count();
        $completedProcedures = $procedures->where('status', 'completed')->count();
        $inProgressProcedures = $procedures->where('status', 'in_progress')->count();
        $pendingProcedures = $procedures->where('status', 'pending')->count();
        $cancelledProcedures = $procedures->where('status', 'cancelled')->count();

        $totalDuration = $procedures->sum('duration');
        $averageDuration = $totalProcedures > 0 ? $totalDuration / $totalProcedures : 0;

        $completionRate = $totalProcedures > 0 ? ($completedProcedures / $totalProcedures) * 100 : 0;

        return [
            'total_procedures' => $totalProcedures,
            'completed_procedures' => $completedProcedures,
            'in_progress_procedures' => $inProgressProcedures,
            'pending_procedures' => $pendingProcedures,
            'cancelled_procedures' => $cancelledProcedures,
            'total_duration' => $totalDuration,
            'average_duration' => round($averageDuration, 2),
            'completion_rate' => round($completionRate, 2),
            'procedures' => $procedures
        ];
    }

    public function generateDetailedReport($startDate, $endDate)
    {
        return PhysiotherapyProcedure::whereBetween('start_date', [$startDate, $endDate])
            ->with(['appointment.patient', 'physiotherapyType', 'physiotherapist'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function generateByTypeReport($startDate, $endDate)
    {
        return PhysiotherapyType::with(['physiotherapyProcedures' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }])
        ->get()
        ->map(function($type) {
            $procedures = $type->physiotherapyProcedures;
            return [
                'type' => $type,
                'total_procedures' => $procedures->count(),
                'completed_procedures' => $procedures->where('status', 'completed')->count(),
                'in_progress_procedures' => $procedures->where('status', 'in_progress')->count(),
                'pending_procedures' => $procedures->where('status', 'pending')->count(),
                'cancelled_procedures' => $procedures->where('status', 'cancelled')->count(),
                'total_duration' => $procedures->sum('duration'),
                'average_duration' => $procedures->count() > 0 ? $procedures->sum('duration') / $procedures->count() : 0,
                'completion_rate' => $procedures->count() > 0 ? ($procedures->where('status', 'completed')->count() / $procedures->count()) * 100 : 0
            ];
        });
    }

    private function generateByPhysiotherapistReport($startDate, $endDate)
    {
        // Get all users who have physiotherapy procedures in the date range
        $physiotherapists = User::whereHas('physiotherapyProcedures', function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        })
        ->with(['physiotherapyProcedures' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }])
        ->get()
        ->map(function($physiotherapist) {
            $procedures = $physiotherapist->physiotherapyProcedures;
            $completedProcedures = $procedures->where('status', 'completed')->count();
            $totalProcedures = $procedures->count();
            
            return [
                'name' => $physiotherapist->name,
                'email' => $physiotherapist->email,
                'total_procedures' => $totalProcedures,
                'completed_procedures' => $completedProcedures,
                'in_progress_procedures' => $procedures->where('status', 'in_progress')->count(),
                'pending_procedures' => $procedures->where('status', 'pending')->count(),
                'cancelled_procedures' => $procedures->where('status', 'cancelled')->count(),
                'total_duration' => $procedures->sum('duration'),
                'average_duration' => $totalProcedures > 0 ? round($procedures->sum('duration') / $totalProcedures, 2) : 0,
                'completion_rate' => $totalProcedures > 0 ? round(($completedProcedures / $totalProcedures) * 100, 2) : 0,
                'performance_score' => $totalProcedures > 0 ? round(($completedProcedures / $totalProcedures) * 100, 1) : 0,
                'recent_procedures' => $procedures->sortByDesc('start_date')->take(5)
            ];
        });

        return [
            'physiotherapists' => $physiotherapists
        ];
    }

    public function exportReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel',
        ]);
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $format = $request->format;

        // Generate all report types for the date range
        $data = [
            'summary' => $this->generateSummaryReport($startDate, $endDate),
            'detailed' => $this->generateDetailedReport($startDate, $endDate),
            'by_type' => $this->generateByTypeReport($startDate, $endDate),
            'by_physiotherapist' => $this->generateByPhysiotherapistReport($startDate, $endDate)
        ];

        if ($format === 'pdf') {
            return $this->showPrintPage($data, $startDate, $endDate);
        } else {
            return $this->exportToExcel($data, $startDate, $endDate);
        }
    }

    private function showPrintPage($data, $startDate, $endDate)
    {
        // Return the print page view
        return view('pages.physiotherapy.reports.pdf', compact('data', 'startDate', 'endDate'));
    }

    private function exportToExcel($data, $startDate, $endDate)
    {
        // Generate filename with date range
        $filename = 'physiotherapy_report_' . verta($startDate)->format('Y-m-d') . '_to_' . verta($endDate)->format('Y-m-d') . '.xlsx';
        
        // Export to Excel using the PhysiotherapyReportExport class
        return Excel::download(new PhysiotherapyReportExport($data, $startDate, $endDate), $filename);
    }
}
