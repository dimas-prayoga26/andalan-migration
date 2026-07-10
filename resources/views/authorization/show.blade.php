@extends('layouts.main')

@section('title', 'Employee Details')
@section('navbarTitle', 'Employee Details')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Employee Details',
    'current' => $employee->profile?->name ?? 'Employee',
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
@endphp

<div class="card">
    <div class="card-header border-0 flex-wrap gap-2">
        <div>
            <h4 class="card-title mb-1">{{ $employee->profile?->name ?? $employee->user?->username ?? 'Employee' }}</h4>
            <p class="mb-0 text-muted fs-13">{{ $positionLabel }} - {{ $employee->deployment?->company?->name ?? '-' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('authorization') }}" class="btn btn-light btn-sm">Back</a>
            @if ($canManageDataEmployee)
                <a href="{{ route('authorization.edit', ['employee' => $employee]) }}" class="btn btn-primary btn-sm">Update</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if (session('status'))
            <div class="alert alert-success mb-4" role="alert">{{ session('status') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="mb-3">User</h5>
                <p class="mb-1"><span class="text-muted">Username:</span> {{ $employee->user?->username ?? '-' }}</p>
                <p class="mb-1"><span class="text-muted">Email:</span> {{ $employee->user?->email ?? '-' }}</p>
                <p class="mb-1"><span class="text-muted">Phone:</span> {{ $employee->user?->phone ?? '-' }}</p>
                <p class="mb-1"><span class="text-muted">Status:</span> {{ $employee->status ?? '-' }}</p>
            </div>
            <div class="col-lg-4">
                <h5 class="mb-3">Identity</h5>
                <p class="mb-1"><span class="text-muted">NIK:</span> {{ $employee->identity?->nik ?? '-' }}</p>
                <p class="mb-1"><span class="text-muted">NPWP:</span> {{ $employee->identity?->npwp ?? '-' }}</p>
                <p class="mb-1"><span class="text-muted">Employment BPJS:</span> {{ $employee->identity?->bpjs_ketenagakerjaan ?? '-' }}</p>
                <p class="mb-1"><span class="text-muted">Healthcare BPJS:</span> {{ $employee->identity?->bpjs_kesehatan ?? '-' }}</p>
            </div>
            <div class="col-lg-4">
                <h5 class="mb-3">Deployment</h5>
                <p class="mb-1"><span class="text-muted">Company:</span> {{ $employee->deployment?->company?->name ?? '-' }}</p>
                <p class="mb-1"><span class="text-muted">Division:</span> {{ $employee->deployment?->department?->name ?? '-' }}</p>
                <p class="mb-1"><span class="text-muted">Position:</span> {{ $positionLabel }}</p>
                <p class="mb-1"><span class="text-muted">PIC:</span> {{ $employee->picAssignment?->supervisor?->profile?->name ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
@endsection
