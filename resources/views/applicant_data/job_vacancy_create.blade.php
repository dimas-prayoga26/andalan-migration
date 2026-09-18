@extends('layouts.main')

@section('title', 'Create Job Vacancy')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .talent-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            gap: 0.25rem;
            white-space: nowrap;
        }

        .talent-tabs .nav-link {
            border: 0;
            background: transparent;
            white-space: nowrap;
        }

        .talent-header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid #eef0f4;
        }

        .talent-table-title {
            color: #25314c;
            font-size: 1rem;
            font-weight: 700;
        }

        .talent-form {
            max-width: 720px;
        }

        .talent-form label {
            color: #5f6b7a;
            font-size: 0.86rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .talent-form .form-control,
        .talent-form .form-select {
            min-height: 42px;
            border: 1px solid #d9dce5;
            border-radius: 0.45rem;
            color: #27334a;
        }

        .talent-form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 1.25rem;
        }

        .talent-form-actions .btn {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        @media only screen and (max-width: 767.98px) {
            .talent-header-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .talent-form-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Create Job Vacancy')

@section('content')
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Create Job Vacancy</h1></li>
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Main</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applicant.job_vacancies') }}">Job Vacancies</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card h-auto">
            <div class="card-header">
                <ul class="nav nav-underline card-header-tabs talent-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="{{ route('applicant') }}" class="nav-link {{ request()->routeIs('applicant') ? 'active' : '' }}">Applicants</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('applicant.job_vacancies') }}" class="nav-link {{ request()->routeIs('applicant.job_vacancies*') ? 'active' : '' }}">Job Vacancies</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="talent-header-bar">
                    <div class="talent-table-title">Tambah Lowongan Pekerjaan</div>
                </div>

                <form method="POST" action="{{ route('applicant.job_vacancies.store') }}" class="talent-form">
                    @csrf
                    <div class="mb-3">
                        <label for="jobVacancyName">Lowongan Pekerjaan</label>
                        <input
                            type="text"
                            id="jobVacancyName"
                            name="name"
                            class="form-control"
                            value="{{ old('name') }}"
                            maxlength="255"
                            required
                            autofocus
                        >
                    </div>
                    <div class="mb-3">
                        <label for="jobVacancyStatus">Status</label>
                        <select id="jobVacancyStatus" name="status" class="form-select" required>
                            @foreach ($jobVacancyStatuses as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected(old('status', \App\Models\JobVacancy::STATUS_ACTIVE) === $statusValue)>
                                    {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="talent-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i>
                            Tambah
                        </button>
                        <a href="{{ route('applicant.job_vacancies') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </form>
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
