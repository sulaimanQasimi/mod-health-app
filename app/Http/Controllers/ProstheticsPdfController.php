<?php

namespace App\Http\Controllers;

use App\Models\ProstheticCase;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class ProstheticsPdfController extends Controller
{
    public function caseSummary(ProstheticCase $prosthetic_case)
    {
        $prosthetic_case->load([
            'patient',
            'referral',
            'assessment',
            'measurementSets.measurements',
            'prescriptions.lines.catalogItem',
            'estimates',
            'workOrders',
            'fittingSessions',
            'deliveries',
            'followUps',
        ]);

        $latestMeasurementSet = $prosthetic_case->measurementSets->sortByDesc('version')->first();
        $activePrescription = $prosthetic_case->prescriptions->sortByDesc('id')->first();
        $latestEstimate = $prosthetic_case->estimates->sortByDesc('id')->first();
        $activeWorkOrder = $prosthetic_case->workOrders->sortByDesc('id')->first();
        $latestFitting = $prosthetic_case->fittingSessions->sortByDesc('id')->first();
        $latestDelivery = $prosthetic_case->deliveries->sortByDesc('id')->first();
        $upcomingFollowUp = $prosthetic_case->followUps->sortBy('scheduled_at')->first();

        $html = view('pages.prosthetics.pdfs.case_summary', [
            'prosthetic_case' => $prosthetic_case,
            'latestMeasurementSet' => $latestMeasurementSet,
            'activePrescription' => $activePrescription,
            'latestEstimate' => $latestEstimate,
            'activeWorkOrder' => $activeWorkOrder,
            'latestFitting' => $latestFitting,
            'latestDelivery' => $latestDelivery,
            'upcomingFollowUp' => $upcomingFollowUp,
        ])->render();

        $mpdf = new Mpdf(['format' => 'A4-L']);
        $mpdf->WriteHTML($html);
        $fileName = 'prosthetics_case_' . ($prosthetic_case->case_number ?? $prosthetic_case->id) . '_summary.pdf';
        $mpdf->Output($fileName, 'D');
    }
}

