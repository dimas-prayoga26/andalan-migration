<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripCashAdvance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BusinessTripCashAdvanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(BusinessTrip $businessTrip): View
    {
        return view('attendance.business-trips.create-cash-advance', [
            'businessTrip' => $businessTrip,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): void
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessTripCashAdvance $businessTripCashAdvance): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessTripCashAdvance $businessTripCashAdvance): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessTripCashAdvance $businessTripCashAdvance): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessTripCashAdvance $businessTripCashAdvance): void
    {
        //
    }
}
