@extends('layouts.main')

@section('title', 'Director Attendance')

@section('navbarTitle', 'Director')

@section('content')
@include('director_attendance.layout.navbar')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Attendance</h5>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 align-items-center">
        <h4 class="card-title m-0">Attendance Logs</h4>
    </div>
    <div class="card-body table-card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 table-bottom-borderless">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Note</th>
                        <th>Working Hours</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No attendance data available.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
