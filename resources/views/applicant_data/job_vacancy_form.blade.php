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
            grid-template-columns: minmax(0, 1fr) minmax(220px, 280px) 220px;
            gap: 1rem;
        }

        .job-vacancy-criteria-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .job-vacancy-criteria-tabs {
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .job-vacancy-criteria-tabs .nav-link {
            min-height: 38px;
            border-radius: 0.55rem;
            background: #eef2ff;
            color: #1d4ed8;
            font-weight: 700;
            padding: 0.5rem 0.9rem;
        }

        .job-vacancy-criteria-tabs .nav-link.active {
            background: #2445c2;
            color: #fff;
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
            justify-content: center;
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
    $skillRows = old('skill_criteria', collect($skillCriteria)->values()->all());
    $criteriaRequired = $formMethod === 'POST';

    if (! is_array($criteriaRows) || count($criteriaRows) === 0) {
        $criteriaRows = [['name' => '', 'weight' => '']];
    }

    if (! is_array($skillRows) || count($skillRows) === 0) {
        $skillRows = [['name' => '', 'weight' => '']];
    }

    $hasSkillCriteriaErrors = $errors->has('skill_criteria')
        || $errors->has('skill_criteria.*')
        || $errors->has('skill_criteria.*.name')
        || $errors->has('skill_criteria.*.weight');
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
                    @include('partials.swal-alert', ['type' => 'error', 'message' => $errors->first()])
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
                        <label for="jobVacancyCompany" class="form-label">Company</label>
                        <select name="company_id" id="jobVacancyCompany" class="form-control @error('company_id') is-invalid @enderror" required>
                            <option value="">Select company</option>
                            @foreach ($companyOptions as $companyOption)
                                <option value="{{ $companyOption['id'] }}" @selected((string) old('company_id', $jobVacancy->company_id) === $companyOption['id'])>{{ $companyOption['name'] }}</option>
                            @endforeach
                        </select>
                        @error('company_id')
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

                <ul class="nav nav-pills job-vacancy-criteria-tabs" id="jobVacancyCriteriaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="technicalCriteriaTab" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#technicalCriteriaPane" aria-controls="technicalCriteriaPane" aria-selected="true">
                            Kriteria Tes Teknis
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="skillCriteriaTab" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#skillCriteriaPane" aria-controls="skillCriteriaPane" aria-selected="false">
                            Kriteria Tes Skill
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="jobVacancyCriteriaTabContent">
                    <div class="tab-pane fade show active" id="technicalCriteriaPane" role="tabpanel" aria-labelledby="technicalCriteriaTab" tabindex="0" data-criteria-pane>
                        <div class="job-vacancy-criteria-header">
                            <div>
                                <div class="job-vacancy-criteria-title">Kriteria Tes Teknis</div>
                                @if ($criteriaRequired)
                                    <p class="job-vacancy-criteria-subtitle">Total bobot wajib tepat 100% sebelum lowongan bisa disimpan.</p>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="job-vacancy-total-box" data-criteria-total>Total 0%</span>
                                <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center gap-1" data-add-criterion>
                                    <i class="bi bi-plus-lg"></i>
                                    <span>Tambah Kriteria</span>
                                </button>
                            </div>
                        </div>

                        @error('technical_criteria')
                            @include('partials.swal-alert', ['type' => 'error', 'message' => $message])
                        @enderror

                        <div class="job-vacancy-criteria-list" data-criteria-list data-criteria-prefix="technical_criteria" data-criteria-required="{{ $criteriaRequired ? 'true' : 'false' }}" data-criteria-placeholder="Contoh: Clean Code">
                            @foreach ($criteriaRows as $index => $criterion)
                                <div class="job-vacancy-criteria-row" data-criterion-row>
                                    <input
                                        type="text"
                                        name="technical_criteria[{{ $index }}][name]"
                                        class="form-control"
                                        value="{{ $criterion['name'] ?? '' }}"
                                        placeholder="Contoh: Clean Code"
                                        data-criterion-name
                                        @required($criteriaRequired)
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
                                        @required($criteriaRequired)
                                    >
                                    <button type="button" class="job-vacancy-remove-criteria" title="Hapus kriteria" data-remove-criterion>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="tab-pane fade" id="skillCriteriaPane" role="tabpanel" aria-labelledby="skillCriteriaTab" tabindex="0" data-criteria-pane>
                        <div class="job-vacancy-criteria-header">
                            <div>
                                <div class="job-vacancy-criteria-title">Kriteria Tes Skill</div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="job-vacancy-total-box" data-criteria-total>Total 0%</span>
                                <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center gap-1" data-add-criterion>
                                    <i class="bi bi-plus-lg"></i>
                                    <span>Tambah Kriteria</span>
                                </button>
                            </div>
                        </div>

                        @error('skill_criteria')
                            @include('partials.swal-alert', ['type' => 'error', 'message' => $message])
                        @enderror

                        <div class="job-vacancy-criteria-list" data-criteria-list data-criteria-prefix="skill_criteria" data-criteria-required="false" data-criteria-placeholder="Contoh: Desain Grafis">
                            @foreach ($skillRows as $index => $criterion)
                                <div class="job-vacancy-criteria-row" data-criterion-row>
                                    <input
                                        type="text"
                                        name="skill_criteria[{{ $index }}][name]"
                                        class="form-control"
                                        value="{{ $criterion['name'] ?? '' }}"
                                        placeholder="Contoh: Desain Grafis"
                                        data-criterion-name
                                    >
                                    <input
                                        type="number"
                                        name="skill_criteria[{{ $index }}][weight]"
                                        class="form-control"
                                        value="{{ $criterion['weight'] ?? '' }}"
                                        min="1"
                                        max="100"
                                        placeholder="Bobot %"
                                        data-criterion-weight
                                    >
                                    <button type="button" class="job-vacancy-remove-criteria" title="Hapus kriteria" data-remove-criterion>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
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
            var activeCriteriaPane = @json($hasSkillCriteriaErrors ? '#skillCriteriaPane' : '#technicalCriteriaPane');

            function updateIndexes(list, prefix) {
                Array.from(list.querySelectorAll('[data-criterion-row]')).forEach(function (row, index) {
                    var nameInput = row.querySelector('[data-criterion-name]');
                    var weightInput = row.querySelector('[data-criterion-weight]');

                    if (nameInput) {
                        nameInput.name = prefix + '[' + index + '][name]';
                    }

                    if (weightInput) {
                        weightInput.name = prefix + '[' + index + '][weight]';
                    }
                });
            }

            function updateTotal(list, totalBadge, criteriaRequired) {
                if (!totalBadge) {
                    return;
                }

                var rows = Array.from(list.querySelectorAll('[data-criterion-row]'));
                var hasFilledRow = rows.some(function (row) {
                    var nameInput = row.querySelector('[data-criterion-name]');
                    var weightInput = row.querySelector('[data-criterion-weight]');

                    return String(nameInput ? nameInput.value : '').trim() !== ''
                        || String(weightInput ? weightInput.value : '').trim() !== '';
                });
                var total = rows.reduce(function (sum, row) {
                    var input = row.querySelector('[data-criterion-weight]');

                    return sum + Number(input.value || 0);
                }, 0);
                var shouldValidateTotal = criteriaRequired || hasFilledRow;

                totalBadge.textContent = 'Total ' + total + '%';
                totalBadge.classList.toggle('is-valid', shouldValidateTotal && total === 100);
                totalBadge.classList.toggle('is-invalid', shouldValidateTotal && total !== 100);
            }

            function createRow(placeholder, criteriaRequired) {
                var row = document.createElement('div');
                row.className = 'job-vacancy-criteria-row';
                row.setAttribute('data-criterion-row', '');
                row.innerHTML = ''
                    + '<input type="text" class="form-control" placeholder="' + placeholder + '" data-criterion-name' + (criteriaRequired ? ' required' : '') + '>'
                    + '<input type="number" class="form-control" min="1" max="100" placeholder="Bobot %" data-criterion-weight' + (criteriaRequired ? ' required' : '') + '>'
                    + '<button type="button" class="job-vacancy-remove-criteria" title="Hapus kriteria" data-remove-criterion><i class="bi bi-trash"></i></button>';

                return row;
            }

            document.querySelectorAll('[data-criteria-list]').forEach(function (list) {
                var pane = list.closest('[data-criteria-pane]');
                var totalBadge = pane ? pane.querySelector('[data-criteria-total]') : null;
                var addButton = pane ? pane.querySelector('[data-add-criterion]') : null;
                var prefix = list.dataset.criteriaPrefix || 'technical_criteria';
                var criteriaRequired = list.dataset.criteriaRequired === 'true';
                var placeholder = list.dataset.criteriaPlaceholder || 'Contoh: Clean Code';

                if (!pane || !addButton) {
                    return;
                }

                addButton.addEventListener('click', function () {
                    list.appendChild(createRow(placeholder, criteriaRequired));
                    updateIndexes(list, prefix);
                    updateTotal(list, totalBadge, criteriaRequired);
                });

                list.addEventListener('input', function (event) {
                    if (event.target.matches('[data-criterion-name], [data-criterion-weight]')) {
                        updateTotal(list, totalBadge, criteriaRequired);
                    }
                });

                list.addEventListener('click', function (event) {
                    var removeButton = event.target.closest('[data-remove-criterion]');

                    if (!removeButton || list.querySelectorAll('[data-criterion-row]').length <= 1) {
                        return;
                    }

                    removeButton.closest('[data-criterion-row]').remove();
                    updateIndexes(list, prefix);
                    updateTotal(list, totalBadge, criteriaRequired);
                });

                updateIndexes(list, prefix);
                updateTotal(list, totalBadge, criteriaRequired);
            });

            if (activeCriteriaPane !== '#technicalCriteriaPane' && window.bootstrap && bootstrap.Tab) {
                var activeTab = document.querySelector('[data-bs-target="' + activeCriteriaPane + '"]');

                if (activeTab) {
                    bootstrap.Tab.getOrCreateInstance(activeTab).show();
                }
            }
        })();
    </script>
@endsection
