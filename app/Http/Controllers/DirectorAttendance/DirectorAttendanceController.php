<?php

namespace App\Http\Controllers\DirectorAttendance;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DirectorAttendanceController extends Controller
{
    public function index(): View
    {
        return view('director_attendance.attendance.index');
    }
}
