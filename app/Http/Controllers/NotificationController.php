<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\Lab;
use App\Models\Prescription;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        return redirect()->back();
    }

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->unreadNotifications->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
            $notificationType = $notification->type;

            if ($notificationType === 'App\Notifications\NewAppointmentNotification') {
                $appointmentId = $notification->data['appointment_id'];
                Appointment::findOrFail($appointmentId);
                return redirect()->route('appointments.show', $appointmentId);

            } 
            elseif ($notificationType === 'App\Notifications\NewAnesthesiaNotification') {
                $anesthesiaId = $notification->data['anesthesia_id'];
                Anesthesia::findOrFail($anesthesiaId);
                return redirect()->route('anesthesias.show', $anesthesiaId);
            } 
            elseif ($notificationType === 'App\Notifications\NewICUNotification') {
                $icuId = $notification->data['icu_id'];
                ICU::findOrFail($icuId);
                return redirect()->route('icus.show', $icuId);
            } 
            elseif ($notificationType === 'App\Notifications\NewOperationNotification') {
                $operationId = $notification->data['operation_id'];
                Anesthesia::findOrFail($operationId);
                return redirect()->route('operations.show', $operationId);
            }
            elseif ($notificationType === 'App\Notifications\NewConsultationNotification') {
                $consultationId = $notification->data['consultation_id'];
                Consultation::findOrFail($consultationId);
                return redirect()->route('consultations.show', $consultationId);
            }
            elseif ($notificationType === 'App\Notifications\NewHospitalizationNotification') {
                $hospitalizationId = $notification->data['consultation_id'];
                Hospitalization::findOrFail($hospitalizationId);
                return redirect()->route('hospitalizations.show', $hospitalizationId);
            }
            elseif ($notificationType === 'App\Notifications\NewLabNotification') {
                $labId = $notification->data['lab_id'];
                Lab::findOrFail($labId);
                return redirect()->route('labs.show', $labId);
            }
            elseif ($notificationType === 'App\Notifications\NewPrescriptionNotification') {
                $prescriptionId = $notification->data['prescription_id'];
                Prescription::findOrFail($prescriptionId);
                return redirect()->route('prescriptions.show', $prescriptionId);
            }
        }

        return redirect()->back();
    }
}
