@extends('layouts.main')

@section('title', 'Setup Meeting')

@section('navbarTitle', 'Setup Meeting')

@section('content')
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Setup Meeting</h1></li>
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">
                    <svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Setup Meeting</li>
        </ol>
    </nav>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Meeting</h5>
    <a href="{{ route('hr-meetings.create') }}" class="btn btn-sm btn-success mb-1">+ New Meeting</a>
</div>

<div class="card">
    <div class="card-header border-0 align-items-center gap-2 flex-wrap">
        <h4 class="card-title m-0">Meeting</h4>
        <div class="clearfix d-flex align-items-center">
            <div class="clearfix me-1">
                <select class="selectpicker form-select form-select-sm">
                    <option value="May">May</option>
                    <option value="June">June</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                </select>
            </div>
            <div class="clearfix">
                <select class="selectpicker form-select form-select-sm">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body table-card-body px-0 pt-0 pb-2">
        <div class="table-responsive">
            <table class="table table-sm table-sm-responsive text-nowrap" id="tableLicenseUsage">
                <thead>
                    <tr>
                        <th class="mw-10">No</th>
                        <th class="mw-80">Date</th>
                        <th class="mw-80">Meeting Type</th>
                        <th class="mw-80">Time</th>
                        <th class="mw-80">Joined</th>
                        <th class="mw-80">Task</th>
                        <th class="mw-80">Status</th>
                        <th class="mw-100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1.</td>
                        <td>Monday, 07 September 2025</td>
                        <td>BOD Meeting</td>
                        <td>10:00 WIB</td>
                        <td>4 People</td>
                        <td>5 Task</td>
                        <td><span class="badge badge-sm badge-success light fw-semibold">Completed</span></td>
                        <td>
                            <a href="{{ route('hr-meetings.details') }}">
                                <div class="btn btn-square btn-secondary light btn-xs">
                                    <i class="fa-regular fa-file-lines"></i>
                                </div>
                            </a>
                            <a href="{{ route('hr-meetings.update') }}">
                                <div class="btn btn-square btn-warning light btn-xs">
                                    <i class="fa-solid fa-pencil"></i>
                                </div>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>Friday, 04 September 2025</td>
                        <td>Weekly Meeting</td>
                        <td>10:00 WIB</td>
                        <td>12 People</td>
                        <td>18 Task</td>
                        <td><span class="badge badge-sm badge-info light fw-semibold">Scheduled</span></td>
                        <td>
                            <a href="{{ route('hr-meetings.details') }}">
                                <div class="btn btn-square btn-secondary light btn-xs">
                                    <i class="fa-regular fa-file-lines"></i>
                                </div>
                            </a>
                            <a href="{{ route('hr-meetings.update') }}">
                                <div class="btn btn-square btn-warning light btn-xs">
                                    <i class="fa-solid fa-pencil"></i>
                                </div>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
