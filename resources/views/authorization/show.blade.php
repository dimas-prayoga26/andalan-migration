@extends('layouts.main')

@section('title', 'Employee Details')
@section('navbarTitle', 'Employee Details')

@section('css')
    <style>
        .employee-detail-avatar {
            object-fit: cover;
        }
    </style>
    @include('employee_data.documents.styles')
@endsection

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Employee Details',
    'current' => $employee->profile?->name ?: ($employee->profile?->nickname ?: 'Employee'),
    'homeRoute' => 'dashboard',
])

@php
    $positionNames = collect([$employee->deployment?->position?->name])
        ->merge($employee->deployment?->positions?->pluck('name') ?? [])
        ->map(fn ($positionName) => trim((string) $positionName))
        ->filter()
        ->unique()
        ->values();
    $positionLabel = $positionNames->implode(', ') ?: '-';
    $employeeName = collect([$employee->profile?->name, $employee->profile?->nickname, $employee->user?->email])
        ->map(fn ($nameCandidate) => trim((string) $nameCandidate))
        ->first(fn ($nameCandidate) => $nameCandidate !== '') ?? 'Employee';
    $portfolioUrl = trim((string) ($employee->profile?->portfolio_url ?? ''));
@endphp

@include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])

<div class="row">
    <div class="col-xl-3 col-md-4">
        <div class="card h-auto">
            <div class="card-body py-sm-5">
                <div class="text-center">
                    <div class="avatar avatar-xl rounded-circle overflow-hidden mx-auto">
                        <img class="w-100 h-100 employee-detail-avatar" src="{{ $employeeAvatarUrl }}" alt="{{ $employeeName }}">
                    </div>
                    <div class="clearfix mt-3">
                        <h6 class="mb-0">{{ $employeeName }}</h6>
                        <span>{{ $positionLabel }}</span> <br>
                        <span>{{ $employee->deployment?->department?->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between py-3">
                    <span>Attendance ({{ $employeeMonthlySummary['month_label'] }})</span>
                    <span>{{ $employeeMonthlySummary['attended_days'] }}/{{ $employeeMonthlySummary['working_days'] }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3">
                    <span>Report ({{ $employeeMonthlySummary['month_label'] }})</span>
                    <span>{{ $employeeMonthlySummary['completed_tasks'] }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3">
                    <span>KPI</span>
                    <span>-</span>
                </li>
            </ul>
            <div class="card-footer p-4">
                <span class="btn btn-outline-light w-100 pe-none">{{ $employee->deployment?->company?->name ?? '-' }}</span>
                @if ($portfolioUrl !== '')
                    <a href="{{ $portfolioUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary w-100 mt-2"><span class="overflow-hidden">{{ $portfolioUrl }}</span></a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-9 col-md-8">
        <div class="card h-auto">
            <div class="card-header">
                <ul class="nav nav-underline card-header-tabs" id="employeeDetailTabs" role="tablist">
                    @foreach ($employeeDataSections as $dataSection)
                        <li class="nav-item" role="presentation">
                            <button @class(['nav-link', 'active' => $loop->first]) id="employee-detail-{{ $dataSection['key'] }}-tab" data-bs-toggle="tab" data-bs-target="#employee-detail-{{ $dataSection['key'] }}" type="button" role="tab" aria-controls="employee-detail-{{ $dataSection['key'] }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $dataSection['title'] }}</button>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="employeeDetailTabContent">
                    @foreach ($employeeDataSections as $dataSection)
                        <div @class(['tab-pane fade', 'show active' => $loop->first]) id="employee-detail-{{ $dataSection['key'] }}" role="tabpanel" aria-labelledby="employee-detail-{{ $dataSection['key'] }}-tab" tabindex="0">
                            @foreach ($dataSection['groups'] as $dataGroup)
                                <h4 @class(['text-primary mb-3', 'mt-4' => ! $loop->first])>{{ $dataGroup['title'] }}</h4>
                                <div class="text-dark rounded">
                                    @foreach ($dataGroup['fields'] as $dataField)
                                        <div class="row mb-2">
                                            <div class="col-md-4 col-sm-4">
                                                <h5>{{ $dataField['label'] }}@if ($dataField['is_required'])<span class="text-danger">*</span>@endif <span class="float-sm-end">:</span></h5>
                                            </div>
                                            <div class="col-md-8 col-sm-8">
                                                @if ($dataField['value'] !== null)
                                                    <span>{{ $dataField['value'] }}</span>
                                                @elseif ($dataField['is_required'])
                                                    <span class="text-danger fst-italic">Not filled yet</span>
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    @foreach ($dataGroup['tables'] as $dataTable)
                                        <div class="row mb-2">
                                            <div class="col-md-4 col-sm-4">
                                                <h5>{{ $dataTable['title'] }}@if ($dataTable['is_required'])<span class="text-danger">*</span>@endif <span class="float-sm-end">:</span></h5>
                                            </div>
                                        </div>
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm table-sm-responsive table-bottom-borderless mb-0">
                                                <thead class="text-nowrap">
                                                    <tr>
                                                        @foreach ($dataTable['columns'] as $tableColumn)
                                                            <th class="mw-150">{{ $tableColumn }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="text-nowrap">
                                                    @forelse ($dataTable['rows'] as $tableRow)
                                                        <tr>
                                                            @foreach ($tableRow as $tableCell)
                                                                <td>{{ $tableCell }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="{{ count($dataTable['columns']) }}" @class(['text-center', 'text-danger fst-italic' => $dataTable['is_required'], 'text-muted' => ! $dataTable['is_required']])>
                                                                {{ $dataTable['is_required'] ? 'Not filled yet' : 'No data available.' }}
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            @if ($dataSection['key'] === 'attachments')
                                @include('employee_data.documents.vault', ['documentVaultMode' => 'view', 'employeeDocumentFiles' => $dataSection['document_files'] ?? []])
                            @endif
                        </div>
                    @endforeach
                </div>

                <small class="d-block mt-3 text-muted"><span class="text-danger">*</span> Required data filled in by the employee from the Profile page.</small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    @include('employee_data.documents.script')
@endsection
