<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class ReciptionStatisticReportController extends Controller
{
    public function __invoke()
    {
        $appointments = DB::table('appointments')
            ->join('patients', 'appointments.patient_id', '=', 'patients.id')
            ->whereBetween('appointments.date', ['2025-09-02', '2025-09-30'])
            ->select('appointments.*', 'patients.*')
            ->get();
        return $appointments;
    }
}