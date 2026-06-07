<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class BloodBankController extends Controller
{
    use RendersInertiaPage;

    public function dashboard()
    {
        return $this->renderPage('global.blood_bank_dashboard');
    }

    public function new()
    {
        return $this->renderPage('global.new_blood_requests');
    }

    public function approved()
    {
        return $this->renderPage('global.approved_blood_requests');
    }

    public function delivered()
    {
        return $this->renderPage('global.delivered_blood_requests');
    }

    public function rejected()
    {
        return $this->renderPage('global.rejected_blood_requests');
    }

    public function inventory()
    {
        return $this->renderPage('global.blood_inventory');
    }

    public function movements()
    {
        return $this->renderPage('global.stock_movement_audit');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
