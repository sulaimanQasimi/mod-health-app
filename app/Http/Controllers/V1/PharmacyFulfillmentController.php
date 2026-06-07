<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class PharmacyFulfillmentController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.pharmacy_fulfillments');
    }

    public function stock()
    {
        return $this->renderPage('global.pharmacy_stock');
    }

}
