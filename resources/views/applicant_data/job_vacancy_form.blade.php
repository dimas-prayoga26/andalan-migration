@extends('layouts.main')

@section('title', $formTitle)

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .job-vacancy-form-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 220px;
            gap: 1rem;
        }

        .job-vacancy-criteria-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .job-vacancy-criteria-title {
            color: #111827;
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 0.15rem;
        }

        .job-vacancy-criteria-subtitle {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 0;
        }

        .job-vacancy-criteria-list {
            display: grid;
            gap: 0.65rem;
        }

        .job-vacancy-criteria-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 140px 42px;
            gap: 0.65rem;
            align-items: center;
        }

        .job-vacancy-total-box {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            border: 1px solid #d9dce5;
            border-radius: 0.55rem;
            background: #f8fafc;
            color: #475569;
            font-size: 0.85rem;
            font-weight: 800;
            padding: 0.35rem 0.75rem;
        }

        .job-vacancy-total-box.is-valid {
            border-color: #86efac;
            background: #f0fdf4;
            color: #15803d;
        }

        .job-vacancy-total-box.is-invalid {
            border-color: #fecdd3;
            background: #fff1f2;
            color: #e11d48;
        }

        .job-vacancy-remove-criteria {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 0.45rem;
            background: #ffe1e6;
            color: #c0263d;
        }

        @media only screen and (max-width: 767.98px) {
            .job-vacancy-form-grid,
            .job-vacancy-criteria-row {
                grid-template-columns: 1fr;
            }

            .job-vacancy-remove-criteria {
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', $formTitle)

@section('content')
@php
    $criteriaRows = old('technical_criteria', collect($technicalCriteria)->values()->all());

    if (! is_array($criteriaRows) || count($criteriaRows) === 0) {
        $criteriaRows = [['name' => '', 'weight' => '']];
    }
@endphp

<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>{{ $formTitle }}</h1></li>
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Main</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applicant.job_vacancies') }}">Job Vacancies</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $formTitle }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-xl-12">
        <form method="POST" action="{{ $formAction }}" class="card h-auto">
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif

            <div class="card-header">
                <div>
                    <h4 class="mb-1">{{ $formTitle }}</h4>
                    <p class="mb-0 text-muted">Isi lowongan dan kriteria tes teknis yang dicari.</p>
                </div>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="job-vacancy-form-grid mb-4">
                    <div>
                        <label for="jobVacancyName" class="form-label">Lowongan Pekerjaan</label>
                        <input
                            type="text"
                            name="name"
                            id="jobVacancyName"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $jobVacancy->name) }}"
                            placeholder="Contoh: IT Programmer"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="jobVacancyStatus" class="form-label">Status</label>
                        <select name="status" id="jobVacancyStatus" class="form-control @error('status') is-invalid @enderror" required>
                            @foreach ($jobVacancyStatuses as $value => $label)
                                <option value="{{ $value }}" @selected((int) old('status', $jobVacancy->status) === (int) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="job-vacancy-criteria-header">
                    <div>
                        <div class="job-vacancy-criteria-title">Kriteria Tes Teknis</div>
                        <p class="job-vacancy-criteria-subtitle">Total bobot wajib tepat 100% sebelum lowongan bisa disimpan.</p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="job-vacancy-total-box" data-criteria-total>Total 0%</span>
                        <button type="button" class="btn btn-primary light btn-sm" data-add-criterion>
                            <i class="bi bi-plus-lg me-1"></i>Tambah Kriteria
                        </button>
                    </div>
                </div>

                @error('technical_criteria')
                    <div class="alert alert-danger mb-3" role="alert">{{ $message }}</div>
                @enderror

                <div class="job-vacancy-criteria-list" data-criteria-list>
                    @foreach ($criteriaRows as $index => $criterion)
                        <div class="job-vacancy-criteria-row" data-criterion-row>
                            <input
                                type="text"
                                name="technical_criteria[{{ $index }}][name]"
                                class="form-control"
                                value="{{ $criterion['name'] ?? '' }}"
                                placeholder="Contoh: Clean Code"
                                data-criterion-name
                                required
                            >
                            <input
                                type="number"
                                name="technical_criteria[{{ $index }}][weight]"
                                class="form-control"
                                value="{{ $criterion['weight'] ?? '' }}"
                                min="1"
                                max="100"
                                placeholder="Bobot %"
                                data-criterion-weight
                                required
                            >
                            <button type="button" class="job-vacancy-remove-criteria" title="Hapus kriteria" data-remove-criterion>
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between gap-2">
                <a href="{{ route('applicant.job_vacancies') }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
    <script>
        (function () {
            var list = document.querySelector('[data-criteria-list]');
            var totalBadge = document.querySelector('[data-criteria-total]');
            var addButton = document.querySelector('[data-add-criterion]');

            if (!list || !totalBadge || !addButton) {
                return;
            }

            function updateIndexes() {
                Array.from(list.querySelectorAll('[data-criterion-row]')).forEach(function (row, index) {
                    var nameInput = row.querySelector('[data-criterion-name]');
                    var weightInput = row.querySelector('[data-criterion-weight]');

                    if (nameInput) {
                        nameInput.name = 'technical_criteria[' + index + '][name]';
                    }

                    if (weightInput) {
                        weightInput.name = 'technical_criteria[' + index + '][weight]';
                    }
                });
            }

            function updateTotal() {
                var total = Array.from(list.querySelectorAll('[data-criterion-weight]')).reduce(function (sum, input) {
                    return sum + Number(input.value || 0);
                }, 0);

                totalBadge.textContent = 'Total ' + total + '%';
                totalBadge.classList.toggle('is-valid', total === 100);
                totalBadge.classList.toggle('is-invalid', total !== 100);
            }

            function createRow() {
                var row = document.createElement('div');
                row.className = 'job-vacancy-criteria-row';
                row.setAttribute('data-criterion-row', '');
                row.innerHTML = ''
                    + '<input type="text" class="form-control" placeholder="Contoh: Clean Code" data-criterion-name required>'
                    + '<input type="number" class="form-control" min="1" max="100" placeholder="Bobot %" data-criterion-weight required>'
                    + '<button type="button" class="job-vacancy-remove-criteria" title="Hapus kriteria" data-remove-criterion><i class="bi bi-trash"></i></button>';

                return row;
            }

            addButton.addEventListener('click', function () {
                list.appendChild(createRow());
                updateIndexes();
                updateTotal();
            });

            list.addEventListener('input', function (event) {
                if (event.target.matches('[data-criterion-weight]')) {
                    updateTotal();
                }
            });

            list.addEventListener('click', function (event) {
                var removeButton = event.target.closest('[data-remove-criterion]');

                if (!removeButton || list.querySelectorAll('[data-criterion-row]').length <= 1) {
                    return;
                }

                removeButton.closest('[data-criterion-row]').remove();
                updateIndexes();
                updateTotal();
            });

            updateIndexes();
            updateTotal();
        })();
    </script>
@endsection
