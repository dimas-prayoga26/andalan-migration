<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class BusinessTripController extends Controller
{
    public function index(): View
    {
        return view('attendance.business-trips.index');
    }
}
