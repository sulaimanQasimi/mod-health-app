<?php

namespace App\Http\Controllers;

use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhysiotherapyReportController extends Controller
{
    public function index()
    {
        return view('pages.physiotherapy.reports.index');
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'required|in:summary,detailed,by_type,by_physiotherapist',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $reportType = $request->report_type;

        $data = [];

        switch ($reportType) {
            case 'summary':
                $data = $this->generateSummaryReport($startDate, $endDate);
                break;
            case 'detailed':
                $data = $this->generateDetailedReport($startDate, $endDate);
                break;
            case 'by_type':
                $data = $this->generateByTypeReport($startDate, $endDate);
                break;
            case 'by_physiotherapist':
                $data = $this->generateByPhysiotherapistReport($startDate, $endDate);
                break;
        }

        return view('pages.physiotherapy.reports.result', compact('data', 'startDate', 'endDate', 'reportType'));
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
        return User::whereHas('physiotherapyProcedures', function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        })
        ->with(['physiotherapyProcedures' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }])
        ->get()
        ->map(function($physiotherapist) {
            $procedures = $physiotherapist->physiotherapyProcedures;
            return [
                'physiotherapist' => $physiotherapist,
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

    public function exportReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'required|in:summary,detailed,by_type,by_physiotherapist',
            'format' => 'required|in:pdf,excel',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $reportType = $request->report_type;
        $format = $request->format;

        $data = [];

        switch ($reportType) {
            case 'summary':
                $data = $this->generateSummaryReport($startDate, $endDate);
                break;
            case 'detailed':
                $data = $this->generateDetailedReport($startDate, $endDate);
                break;
            case 'by_type':
                $data = $this->generateByTypeReport($startDate, $endDate);
                break;
            case 'by_physiotherapist':
                $data = $this->generateByPhysiotherapistReport($startDate, $endDate);
                break;
        }

        if ($format === 'pdf') {
            return $this->exportToPdf($data, $startDate, $endDate, $reportType);
        } else {
            return $this->exportToExcel($data, $startDate, $endDate, $reportType);
        }
    }

    private function exportToPdf($data, $startDate, $endDate, $reportType)
    {
        // Implementation for PDF export
        // You can use packages like dompdf or mPDF
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }

    private function exportToExcel($data, $startDate, $endDate, $reportType)
    {
        // Implementation for Excel export
        // You can use packages like Maatwebsite Excel
        return response()->json(['message' => 'Excel export functionality to be implemented']);
    }
}
