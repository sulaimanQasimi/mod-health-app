<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\UnderReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnderReviewController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $items = $appointment->under_reviews()
            ->with(['room:id,name', 'bed:id,number'])
            ->latest()
            ->get()
            ->map(fn (UnderReview $item) => [
                'id' => $item->id,
                'reason' => $item->reason,
                'remarks' => $item->remarks,
                'room_name' => $item->room?->name,
                'bed_number' => $item->bed?->number,
                'is_active' => ! (bool) $item->is_discharged,
                'urls' => [
                    'show' => route('react.under-reviews.show', $item->id),
                    'edit' => route('react.under-reviews.edit', $item->id),
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('patient-under-review'),
            'edit' => $user->can('edit-under-reviews'),
            'delete' => $user->can('delete-under-reviews'),
        ], [
            'urls' => ['store' => route('under_reviews.store')],
        ]);
    }

    public function destroy(Appointment $appointment, UnderReview $underReview): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-under-reviews'), 403);
        abort_unless((int) $underReview->appointment_id === (int) $appointment->id, 404);
        $underReview->delete();

        return response()->json(['success' => true]);
    }
}
