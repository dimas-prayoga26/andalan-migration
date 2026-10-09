@extends('layouts.main')

@section('title', 'Applicant Assessment')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .applicant-assessment-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .applicant-assessment-title {
            color: #111827;
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .applicant-assessment-subtitle {
            color: #64748b;
            margin-bottom: 0;
        }

        .applicant-assessment-shell {
            display: grid;
            gap: 1rem;
        }

        .applicant-assessment-overview {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .applicant-assessment-metric,
        .applicant-assessment-panel,
        .applicant-assessment-note-box {
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
        }

        .applicant-assessment-metric {
            padding: 0.85rem;
        }

        .applicant-assessment-metric-label {
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .applicant-assessment-metric-value {
            color: #172033;
            font-size: 1rem;
            font-weight: 800;
        }

        .applicant-assessment-metric-value.is-uploaded {
            color: #047857;
        }

        .applicant-assessment-metric-value.is-not-uploaded {
            color: #dc2626;
        }

        .applicant-assessment-panel {
            padding: 1rem;
        }

        .applicant-assessment-panel-title {
            color: #111827;
            font-size: 0.96rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
        }

        .applicant-assessment-panel-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }

        .applicant-assessment-panel-header .applicant-assessment-panel-subtitle {
            margin-bottom: 0;
        }

        .applicant-assessment-panel-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .applicant-assessment-panel-subtitle {
            color: #64748b;
            font-size: 0.82rem;
            margin-bottom: 0.85rem;
        }

        .applicant-rating-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.75rem;
            align-items: center;
            padding: 0.65rem 0;
            border-top: 1px solid #f1f5f9;
        }

        .applicant-rating-row:first-of-type {
            border-top: 0;
            padding-top: 0;
        }

        .applicant-rating-label {
            color: #172033;
            font-size: 0.88rem;
            font-weight: 700;
        }

        .applicant-rating-weight {
            color: #94a3b8;
            font-size: 0.78rem;
            font-weight: 700;
            margin-top: 0.1rem;
        }

        .applicant-rating-widget {
            text-align: right;
        }

        .applicant-rating-widget .rating-stars ul {
            display: inline-flex;
            gap: 0.2rem;
            align-items: center;
            padding: 0;
            margin: 0;
            list-style-type: none;
            user-select: none;
        }

        .applicant-rating-widget .rating-stars ul > li.star {
            display: inline-flex;
            color: #d7dce8;
            cursor: default;
        }

        .applicant-rating-row[data-assessment-rating] .rating-stars ul > li.star {
            cursor: pointer;
        }

        .applicant-rating-row.is-saving {
            opacity: 0.65;
            pointer-events: none;
        }

        .applicant-rating-row.is-save-error .applicant-rating-label {
            color: #dc2626;
        }

        .applicant-rating-widget .rating-stars ul > li.star > i.fa {
            color: currentColor;
            font-size: 1.28rem;
            line-height: 1;
        }

        .applicant-rating-widget .rating-stars ul > li.star.selected > i.fa {
            color: #ff912c;
        }

        .applicant-assessment-upload {
            border: 1px dashed #cbd5e1;
            border-radius: 0.75rem;
            background: #f8fafc;
            color: #64748b;
            padding: 1rem;
            text-align: center;
        }

        .applicant-assessment-preview {
            display: flex;
            justify-content: center;
            margin-bottom: 0.85rem;
        }

        .applicant-assessment-preview img {
            width: min(100%, 620px);
            max-height: 420px;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: #fff;
            object-fit: contain;
        }

        .applicant-assessment-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .applicant-assessment-note-box {
            min-height: 72px;
            background: #f8fafc;
            color: #334155;
            font-size: 0.86rem;
            padding: 0.8rem;
        }

        .applicant-assessment-note-input {
            display: block;
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: #fff;
            color: #334155;
            font-family: inherit;
            font-size: 0.86rem;
            min-height: 72px;
            padding: 0.8rem;
            pointer-events: auto;
            resize: vertical;
            user-select: text;
            cursor: text;
        }

        .applicant-assessment-note-input.is-saving {
            opacity: 0.65;
        }

        .applicant-assessment-note-input.is-save-error {
            border-color: #dc2626;
        }

        .applicant-assessment-verdict-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 0.45rem;
            background: #ecfdf5;
            color: #047857;
            font-size: 0.8rem;
            font-weight: 800;
            margin-top: 0.65rem;
            padding: 0.35rem 0.6rem;
        }

        .applicant-final-layout {
            display: grid;
            gap: 1rem;
        }

        .applicant-final-section {
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            background: #fff;
        }

        .applicant-final-content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr);
            gap: 0.75rem;
        }

        .applicant-final-section {
            padding: 0.9rem;
        }

        .applicant-final-section-title {
            color: #111827;
            font-size: 0.9rem;
            font-weight: 900;
            margin-bottom: 0.2rem;
        }

        .applicant-final-section-subtitle {
            color: #64748b;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
        }

        .applicant-summary-field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .applicant-summary-field {
            display: grid;
            gap: 0.25rem;
        }

        .applicant-summary-field label {
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 800;
            margin-bottom: 0;
        }

        .applicant-summary-field textarea,
        .applicant-final-note {
            border: 1px solid #d9dce5;
            border-radius: 0.5rem;
            background: #fff;
            color: #172033;
            font-size: 0.86rem;
            font-weight: 600;
            padding: 0.4rem 0.65rem;
            resize: vertical;
        }

        .applicant-summary-field textarea {
            min-height: 74px;
        }

        .applicant-final-decision-group {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
            margin-bottom: 0.9rem;
        }

        .applicant-final-decision-option {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            border: 1px solid #d9dce5;
            border-radius: 0.55rem;
            background: #fff;
            color: #172033;
            font-size: 0.85rem;
            font-weight: 800;
            min-height: 38px;
            padding: 0.4rem 0.75rem;
        }

        .applicant-final-decision-option input {
            accent-color: #2846c7;
        }

        .applicant-final-decision-option.is-hired {
            border-color: #86efac;
            background: #f0fdf4;
        }

        .applicant-final-decision-option.is-keep {
            border-color: #fde68a;
            background: #fffbeb;
        }

        .applicant-final-decision-option.is-rejected {
            border-color: #fecaca;
            background: #fef2f2;
        }

        .applicant-final-note {
            min-height: 150px;
            width: 100%;
        }

        @media only screen and (max-width: 767.98px) {
            .applicant-assessment-overview,
            .applicant-final-content-grid,
            .applicant-final-decision-group,
            .applicant-assessment-summary-grid {
                grid-template-columns: 1fr;
            }

            .applicant-rating-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Applicant Assessment')

@section('content')
@php
    $hrCriteria = $hrCriteria ?? [];
    $hrInterviewScoreDisplay = number_format((float) ($hrInterviewScore ?? 0), 0) . ' / 100';
    $technicalCriteria = $technicalCriteria ?? [];
    $technicalTestScoreDisplay = number_format((float) ($technicalTestScore ?? 0), 0) . ' / 100';
    $userCriteria = $userCriteria ?? [];
    $userInterviewScoreDisplay = number_format((float) ($userInterviewScore ?? 0), 0) . ' / 100';
    $technicalTestNotes = $technicalTestNotes ?? '';
    $userInterviewNotes = $userInterviewNotes ?? '';
    $ratingLabels = [
        1 => 'Sangat Kurang',
        2 => 'Kurang',
        3 => 'Cukup',
        4 => 'Baik',
        5 => 'Sangat Baik',
    ];
    $generatedAssessmentUploadUrl = session('assessment_upload_url', $assessmentUploadUrl);
    $assessmentUploaded = filled($assessmentFileUrl);
@endphp

<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Applicant Assessment</h1></li>
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Main</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applicant') }}">Applicants</a></li>
            <li class="breadcrumb-item active" aria-current="page">Penilaian</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card h-auto">
            <div class="card-header">
                <div class="applicant-assessment-header w-100">
                    <div>
                        <h4 class="applicant-assessment-title">{{ $applicant->full_name }}</h4>
                        <p class="applicant-assessment-subtitle">{{ $applicant->jobVacancy?->name ?? '-' }} | {{ $applicant->statusLabel() }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('applicant.show', $applicant) }}" class="btn btn-primary light btn-sm">Detail Kandidat</a>
                        <a href="{{ route('applicant') }}" class="btn btn-primary light btn-sm">Back</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="applicant-assessment-shell">
                    <div class="applicant-assessment-overview">
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">HR Interview</div>
                            <div class="applicant-assessment-metric-value" data-assessment-score="hr-interview">{{ $hrInterviewScoreDisplay }}</div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Assessment Test</div>
                            <div class="applicant-assessment-metric-value {{ $assessmentUploaded ? 'is-uploaded' : 'is-not-uploaded' }}">
                                {{ $assessmentUploaded ? 'Sudah Upload' : 'Belum Upload' }}
                            </div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Technical Test</div>
                            <div class="applicant-assessment-metric-value" data-assessment-score="technical-test">{{ $technicalTestScoreDisplay }}</div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Interview User</div>
                            <div class="applicant-assessment-metric-value" data-assessment-score="user-interview">{{ $userInterviewScoreDisplay }}</div>
                        </div>
                    </div>

                    <ul class="nav nav-pills nav-pills-sm nav-pills-bg gap-2 flex-wrap" id="applicantAssessmentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="hr-interview-tab" data-bs-toggle="tab" data-bs-target="#hr-interview-pane" type="button" role="tab" aria-controls="hr-interview-pane" aria-selected="true">Form Interview HR</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="assessment-test-tab" data-bs-toggle="tab" data-bs-target="#assessment-test-pane" type="button" role="tab" aria-controls="assessment-test-pane" aria-selected="false">Assessment Test</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="technical-test-tab" data-bs-toggle="tab" data-bs-target="#technical-test-pane" type="button" role="tab" aria-controls="technical-test-pane" aria-selected="false">Technical Test</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="user-interview-tab" data-bs-toggle="tab" data-bs-target="#user-interview-pane" type="button" role="tab" aria-controls="user-interview-pane" aria-selected="false">Interview User</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="final-assessment-tab" data-bs-toggle="tab" data-bs-target="#final-assessment-pane" type="button" role="tab" aria-controls="final-assessment-pane" aria-selected="false">Penilaian Akhir</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="applicantAssessmentTabContent">
                        <div class="tab-pane fade show active" id="hr-interview-pane" role="tabpanel" aria-labelledby="hr-interview-tab" tabindex="0">
                            <div class="applicant-assessment-panel">
                                <div class="applicant-assessment-panel-title">Form Interview HR</div>
                                <div class="applicant-assessment-panel-subtitle">Evaluasi dasar, komunikasi, motivasi, dan culture fit.</div>
                                @foreach ($hrCriteria as $criteria)
                                    <div class="applicant-rating-row" data-assessment-rating data-score-target="hr-interview" data-store-url="{{ route('applicant.assessment.hr-interview-score.store', $applicant) }}" data-criterion-key="{{ $criteria['key'] }}">
                                        <div>
                                            <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                            <div class="applicant-rating-weight">Bobot {{ $criteria['weight_label'] ?? $criteria['weight'].'%' }}</div>
                                        </div>
                                        <div class="rating-widget applicant-rating-widget mb-0" aria-label="{{ $criteria['label'] }}">
                                            <div class="rating-stars">
                                                <ul>
                                                    @for ($rating = 1; $rating <= 5; $rating++)
                                                        <li class="star {{ $rating <= $criteria['selected'] ? 'selected' : '' }}" title="{{ $ratingLabels[$rating] }}" data-value="{{ $rating }}" aria-label="{{ $ratingLabels[$rating] }}" role="button" tabindex="0">
                                                            <i class="fa fa-star fa-fw"></i>
                                                        </li>
                                                    @endfor
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="tab-pane fade" id="assessment-test-pane" role="tabpanel" aria-labelledby="assessment-test-tab" tabindex="0">
                            <div class="applicant-assessment-panel">
                                <div class="applicant-assessment-panel-header">
                                    <div>
                                        <div class="applicant-assessment-panel-title">Assessment Test</div>
                                        <div class="applicant-assessment-panel-subtitle">Area lampiran hasil tes administrasi atau psikologi dasar.</div>
                                    </div>
                                    <div class="applicant-assessment-panel-actions">
                                        <form method="POST" action="{{ route('applicant.assessment.upload-request.store', $applicant) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="bi bi-link-45deg me-1"></i>
                                                Generate Link Upload
                                            </button>
                                        </form>
                                        @if ($generatedAssessmentUploadUrl)
                                            <button type="button" class="btn btn-primary light btn-sm" data-copy-assessment-link data-copy-value="{{ $generatedAssessmentUploadUrl }}">
                                                <i class="bi bi-copy me-1"></i>
                                                Copy Link
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="applicant-assessment-upload">
                                    @if ($assessmentUploaded)
                                        <div class="applicant-assessment-preview">
                                            <a href="{{ $assessmentFileUrl }}" target="_blank" rel="noopener">
                                                <img src="{{ $assessmentFileUrl }}" alt="Hasil assessment {{ $applicant->full_name }}">
                                            </a>
                                        </div>
                                        <div class="fw-semibold">Hasil assessment sudah diupload</div>
                                        <div class="small">{{ $assessmentDocument?->original_name ?: basename((string) $assessmentDocument?->file_path) }}</div>
                                    @else
                                        <i class="bi bi-cloud-arrow-up fs-3 d-block mb-2"></i>
                                        <div class="fw-semibold">Upload hasil assessment</div>
                                        <div class="small">Generate link khusus brand {{ $assessmentUploadBrand['name'] ?? 'RNB Management' }} untuk kandidat ini.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="technical-test-pane" role="tabpanel" aria-labelledby="technical-test-tab" tabindex="0">
                            <div class="applicant-assessment-panel">
                                <div class="applicant-assessment-panel-title">Technical Test</div>
                                <div class="applicant-assessment-panel-subtitle">Kriteria Tes Teknis mengikuti bobot dari lowongan yang dilamar.</div>
                                @forelse ($technicalCriteria as $criteria)
                                    <div class="applicant-rating-row" data-assessment-rating data-score-target="technical-test" data-store-url="{{ route('applicant.assessment.technical-test-score.store', $applicant) }}" data-criterion-key="{{ $criteria['key'] }}">
                                        <div>
                                            <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                            <div class="applicant-rating-weight">Bobot {{ $criteria['weight_label'] ?? $criteria['weight'].'%' }}</div>
                                        </div>
                                        <div class="rating-widget applicant-rating-widget mb-0" aria-label="{{ $criteria['label'] }}">
                                            <div class="rating-stars">
                                                <ul>
                                                    @for ($rating = 1; $rating <= 5; $rating++)
                                                        <li class="star {{ $rating <= $criteria['selected'] ? 'selected' : '' }}" title="{{ $ratingLabels[$rating] }}" data-value="{{ $rating }}" aria-label="{{ $ratingLabels[$rating] }}" role="button" tabindex="0">
                                                            <i class="fa fa-star fa-fw"></i>
                                                        </li>
                                                    @endfor
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="applicant-assessment-note-box">
                                        Kriteria Tes Teknis belum diset pada lowongan ini.
                                    </div>
                                @endforelse
                                <textarea class="form-control applicant-assessment-note-input mt-3" name="technical_test_notes" rows="3" data-assessment-note data-store-url="{{ route('applicant.assessment.notes.store', [$applicant, 'technical_test']) }}" placeholder="Tulis catatan reviewer technical test.">{{ $technicalTestNotes }}</textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="user-interview-pane" role="tabpanel" aria-labelledby="user-interview-tab" tabindex="0">
                            <div class="applicant-assessment-panel">
                                <div class="applicant-assessment-panel-title">Interview User</div>
                                <div class="applicant-assessment-panel-subtitle">Evaluasi teknis lanjutan oleh atasan divisi atau user terkait.</div>
                                @foreach ($userCriteria as $criteria)
                                    <div class="applicant-rating-row" data-assessment-rating data-score-target="user-interview" data-store-url="{{ route('applicant.assessment.user-interview-score.store', $applicant) }}" data-criterion-key="{{ $criteria['key'] }}">
                                        <div>
                                            <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                            <div class="applicant-rating-weight">Bobot {{ $criteria['weight_label'] ?? $criteria['weight'].'%' }}</div>
                                        </div>
                                        <div class="rating-widget applicant-rating-widget mb-0" aria-label="{{ $criteria['label'] }}">
                                            <div class="rating-stars">
                                                <ul>
                                                    @for ($rating = 1; $rating <= 5; $rating++)
                                                        <li class="star {{ $rating <= $criteria['selected'] ? 'selected' : '' }}" title="{{ $ratingLabels[$rating] }}" data-value="{{ $rating }}" aria-label="{{ $ratingLabels[$rating] }}" role="button" tabindex="0">
                                                            <i class="fa fa-star fa-fw"></i>
                                                        </li>
                                                    @endfor
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <textarea class="form-control applicant-assessment-note-input mt-3" name="interview_user_notes" rows="3" data-assessment-note data-store-url="{{ route('applicant.assessment.notes.store', [$applicant, 'interview_user']) }}" placeholder="Tulis catatan reviewer interview user.">{{ $userInterviewNotes }}</textarea>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="final-assessment-pane" role="tabpanel" aria-labelledby="final-assessment-tab" tabindex="0">
                            <div class="applicant-assessment-panel applicant-final-layout">
                                <div>
                                    <div class="applicant-assessment-panel-title">Penilaian Akhir</div>
                                    <div class="applicant-assessment-panel-subtitle">Ringkasan nilai, summary matrix, dan keputusan akhir kandidat.</div>
                                </div>

                                <div class="applicant-final-content-grid">
                                    <div class="applicant-final-section">
                                        <div class="applicant-final-section-title">Summary Matrix</div>
                                        <div class="applicant-final-section-subtitle">Rangkum poin penting kandidat tanpa mengulang detail skor.</div>
                                        <div class="applicant-summary-field-grid">
                                            <div class="applicant-summary-field">
                                                <label for="summarySkill">Skill</label>
                                                <textarea id="summarySkill" placeholder="Kekuatan skill utama kandidat."></textarea>
                                            </div>
                                            <div class="applicant-summary-field">
                                                <label for="summaryCommunication">Komunikasi</label>
                                                <textarea id="summaryCommunication" placeholder="Cara kandidat menjelaskan ide dan menerima feedback."></textarea>
                                            </div>
                                            <div class="applicant-summary-field">
                                                <label for="summaryExperience">Kesesuaian Pengalaman</label>
                                                <textarea id="summaryExperience" placeholder="Kesesuaian pengalaman dengan kebutuhan posisi."></textarea>
                                            </div>
                                            <div class="applicant-summary-field">
                                                <label for="summaryTools">Tools</label>
                                                <textarea id="summaryTools" placeholder="Tools yang dikuasai dan catatan workflow."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="applicant-final-section">
                                        <div class="applicant-final-section-title">Keputusan Akhir</div>
                                        <div class="applicant-final-section-subtitle">Pilih keputusan akhir kandidat.</div>
                                        <div class="applicant-final-decision-group" role="group" aria-label="Keputusan Akhir">
                                            <label class="applicant-final-decision-option is-hired" for="finalVerdictHired">
                                                <input type="radio" id="finalVerdictHired" name="final_verdict" value="hired" required>
                                                <span>Hired</span>
                                            </label>
                                            <label class="applicant-final-decision-option is-keep" for="finalVerdictKeep">
                                                <input type="radio" id="finalVerdictKeep" name="final_verdict" value="keep_in_view" required>
                                                <span>Keep in View</span>
                                            </label>
                                            <label class="applicant-final-decision-option is-rejected" for="finalVerdictRejected">
                                                <input type="radio" id="finalVerdictRejected" name="final_verdict" value="rejected" required>
                                                <span>Rejected</span>
                                            </label>
                                        </div>

                                        <label for="finalAssessmentNote" class="applicant-final-section-title d-block">Catatan Final</label>
                                        <textarea id="finalAssessmentNote" class="applicant-final-note" placeholder="Tulis rangkuman akhir, catatan risiko, atau next step kandidat."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        document.querySelectorAll('[data-copy-assessment-link]').forEach((button) => {
            button.addEventListener('click', async () => {
                const value = button.dataset.copyValue || '';

                if (!value) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(value);
                    button.textContent = 'Copied';
                } catch (error) {
                    const temporaryInput = document.createElement('input');
                    temporaryInput.value = value;
                    temporaryInput.style.position = 'fixed';
                    temporaryInput.style.opacity = '0';
                    document.body.appendChild(temporaryInput);
                    temporaryInput.select();
                    document.execCommand('copy');
                    temporaryInput.remove();
                    button.textContent = 'Copied';
                }
            });
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const selectedRatingValue = (row) => {
            const selectedStars = Array.from(row.querySelectorAll('.star.selected'));
            const lastSelectedStar = selectedStars[selectedStars.length - 1];

            return Number(lastSelectedStar?.dataset.value || 0);
        };

        const paintStars = (row, value) => {
            row.querySelectorAll('.star').forEach((star) => {
                star.classList.toggle('selected', Number(star.dataset.value) <= value);
            });
        };

        const storeRating = async (row, star) => {
            const rating = Number(star.dataset.value || 0);
            const previousRating = selectedRatingValue(row);

            if (!rating || row.classList.contains('is-saving')) {
                return;
            }

            row.classList.remove('is-save-error');
            row.classList.add('is-saving');
            paintStars(row, rating);

            try {
                const response = await fetch(row.dataset.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        criterion_key: row.dataset.criterionKey,
                        rating,
                    }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'Nilai gagal disimpan.');
                }

                paintStars(row, Number(payload.rating || rating));

                const scoreTarget = row.dataset.scoreTarget || '';
                const scoreElement = document.querySelector(`[data-assessment-score="${scoreTarget}"]`);

                if (scoreElement && payload.display_score) {
                    scoreElement.textContent = payload.display_score;
                }
            } catch (error) {
                paintStars(row, previousRating);
                row.classList.add('is-save-error');
                alert(error.message || 'Nilai gagal disimpan.');
            } finally {
                row.classList.remove('is-saving');
            }
        };

        document.querySelectorAll('[data-assessment-rating]').forEach((row) => {
            row.querySelectorAll('.star').forEach((star) => {
                star.addEventListener('click', () => storeRating(row, star));
                star.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        storeRating(row, star);
                    }
                });
            });
        });

        const storeNote = async (textarea) => {
            if (textarea.classList.contains('is-saving')) {
                return;
            }

            textarea.classList.remove('is-save-error');
            textarea.classList.add('is-saving');

            try {
                const response = await fetch(textarea.dataset.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        notes: textarea.value,
                    }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'Catatan gagal disimpan.');
                }
            } catch (error) {
                textarea.classList.add('is-save-error');
                alert(error.message || 'Catatan gagal disimpan.');
            } finally {
                textarea.classList.remove('is-saving');
            }
        };

        document.querySelectorAll('[data-assessment-note]').forEach((textarea) => {
            let saveTimeout;

            textarea.addEventListener('blur', () => {
                window.clearTimeout(saveTimeout);
                storeNote(textarea);
            });

            textarea.addEventListener('change', () => {
                window.clearTimeout(saveTimeout);
                saveTimeout = window.setTimeout(() => storeNote(textarea), 250);
            });
        });
    </script>
@endsection
