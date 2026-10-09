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

        .applicant-assessment-panel {
            padding: 1rem;
        }

        .applicant-assessment-panel-title {
            color: #111827;
            font-size: 0.96rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
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

        .applicant-assessment-upload-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.85rem;
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

        .applicant-assessment-link-box {
            display: grid;
            gap: 0.5rem;
            margin-top: 0.85rem;
            text-align: left;
        }

        .applicant-assessment-link-input {
            min-height: 38px;
            width: 100%;
            border: 1px solid #d9dce5;
            border-radius: 0.5rem;
            background: #fff;
            color: #172033;
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.4rem 0.65rem;
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

        .applicant-summary-field-grid {
            display: grid;
            gap: 0.75rem;
            margin-top: 0.65rem;
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

        .applicant-summary-field input {
            min-height: 38px;
            border: 1px solid #d9dce5;
            border-radius: 0.5rem;
            background: #fff;
            color: #172033;
            font-size: 0.86rem;
            font-weight: 600;
            padding: 0.4rem 0.65rem;
        }

        @media only screen and (max-width: 767.98px) {
            .applicant-assessment-overview,
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
    $hrCriteria = [
        ['label' => 'Kemampuan Komunikasi', 'weight' => '20%', 'selected' => 4],
        ['label' => 'Kepercayaan Diri', 'weight' => '10%', 'selected' => 3],
        ['label' => 'Motivasi Kerja', 'weight' => '10%', 'selected' => 4],
        ['label' => 'Kesesuaian Pengalaman', 'weight' => '15%', 'selected' => 3],
        ['label' => 'Pemahaman Posisi', 'weight' => '15%', 'selected' => 4],
        ['label' => 'Kedisiplinan & Profesionalisme', 'weight' => '10%', 'selected' => 5],
        ['label' => 'Culture Fit & Problem Solving', 'weight' => '20%', 'selected' => 4],
    ];
    $technicalCriteria = [
        ['label' => 'Clean Output / Kualitas Hasil', 'weight' => '30%', 'selected' => 4],
        ['label' => 'Pemahaman Brief', 'weight' => '25%', 'selected' => 3],
        ['label' => 'Ketepatan Waktu', 'weight' => '20%', 'selected' => 4],
        ['label' => 'Kerapian File / Dokumentasi', 'weight' => '25%', 'selected' => 3],
    ];
    $userCriteria = [
        ['label' => 'Skill Teknis', 'weight' => '25%', 'selected' => 4],
        ['label' => 'Kreativitas', 'weight' => '20%', 'selected' => 4],
        ['label' => 'Penggunaan Tools', 'weight' => '20%', 'selected' => 3],
        ['label' => 'Pengalaman', 'weight' => '20%', 'selected' => 3],
        ['label' => 'Kesanggupan', 'weight' => '15%', 'selected' => 4],
    ];
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
                            <div class="applicant-assessment-metric-value">82 / 100</div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Assessment Test</div>
                            <div class="applicant-assessment-metric-value">{{ $assessmentUploaded ? 'Sudah Upload' : 'Belum Upload' }}</div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Technical Test</div>
                            <div class="applicant-assessment-metric-value">76 / 100</div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Final Verdict</div>
                            <div class="applicant-assessment-metric-value">Keep in View</div>
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
                                    <div class="applicant-rating-row">
                                        <div>
                                            <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                            <div class="applicant-rating-weight">Bobot {{ $criteria['weight'] }}</div>
                                        </div>
                                        <div class="rating-widget applicant-rating-widget mb-0" aria-label="{{ $criteria['label'] }}">
                                            <div class="rating-stars">
                                                <ul>
                                                    @for ($rating = 1; $rating <= 5; $rating++)
                                                        <li class="star {{ $rating <= $criteria['selected'] ? 'selected' : '' }}" title="{{ $ratingLabels[$rating] }}" data-value="{{ $rating }}" aria-label="{{ $ratingLabels[$rating] }}">
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
                                <div class="applicant-assessment-panel-title">Assessment Test</div>
                                <div class="applicant-assessment-panel-subtitle">Area lampiran hasil tes administrasi atau psikologi dasar.</div>
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
                                    <div class="applicant-assessment-upload-actions">
                                        <form method="POST" action="{{ route('applicant.assessment.upload-request.store', $applicant) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="bi bi-link-45deg me-1"></i>
                                                Generate Link Upload
                                            </button>
                                        </form>
                                        @if ($generatedAssessmentUploadUrl)
                                            <a href="{{ $generatedAssessmentUploadUrl }}" target="_blank" rel="noopener" class="btn btn-primary light btn-sm">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>
                                                Buka Link
                                            </a>
                                        @endif
                                    </div>
                                    @if ($generatedAssessmentUploadUrl)
                                        <div class="applicant-assessment-link-box">
                                            <label for="assessmentUploadUrl" class="small fw-semibold mb-0">Link Upload Assessment</label>
                                            <input id="assessmentUploadUrl" class="applicant-assessment-link-input" type="text" value="{{ $generatedAssessmentUploadUrl }}" readonly>
                                            <button type="button" class="btn btn-primary light btn-sm" data-copy-assessment-link data-target="assessmentUploadUrl">Copy Link</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="technical-test-pane" role="tabpanel" aria-labelledby="technical-test-tab" tabindex="0">
                            <div class="applicant-assessment-panel">
                                <div class="applicant-assessment-panel-title">Technical Test</div>
                                <div class="applicant-assessment-panel-subtitle">Contoh kriteria dinamis sesuai posisi yang dilamar.</div>
                                @foreach ($technicalCriteria as $criteria)
                                    <div class="applicant-rating-row">
                                        <div>
                                            <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                            <div class="applicant-rating-weight">Bobot {{ $criteria['weight'] }}</div>
                                        </div>
                                        <div class="rating-widget applicant-rating-widget mb-0" aria-label="{{ $criteria['label'] }}">
                                            <div class="rating-stars">
                                                <ul>
                                                    @for ($rating = 1; $rating <= 5; $rating++)
                                                        <li class="star {{ $rating <= $criteria['selected'] ? 'selected' : '' }}" title="{{ $ratingLabels[$rating] }}" data-value="{{ $rating }}" aria-label="{{ $ratingLabels[$rating] }}">
                                                            <i class="fa fa-star fa-fw"></i>
                                                        </li>
                                                    @endfor
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="applicant-assessment-note-box mt-3">Catatan reviewer technical test akan ditampilkan di sini.</div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="user-interview-pane" role="tabpanel" aria-labelledby="user-interview-tab" tabindex="0">
                            <div class="applicant-assessment-panel">
                                <div class="applicant-assessment-panel-title">Interview User</div>
                                <div class="applicant-assessment-panel-subtitle">Evaluasi teknis lanjutan oleh atasan divisi atau user terkait.</div>
                                @foreach ($userCriteria as $criteria)
                                    <div class="applicant-rating-row">
                                        <div>
                                            <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                            <div class="applicant-rating-weight">Bobot {{ $criteria['weight'] }}</div>
                                        </div>
                                        <div class="rating-widget applicant-rating-widget mb-0" aria-label="{{ $criteria['label'] }}">
                                            <div class="rating-stars">
                                                <ul>
                                                    @for ($rating = 1; $rating <= 5; $rating++)
                                                        <li class="star {{ $rating <= $criteria['selected'] ? 'selected' : '' }}" title="{{ $ratingLabels[$rating] }}" data-value="{{ $rating }}" aria-label="{{ $ratingLabels[$rating] }}">
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

                        <div class="tab-pane fade" id="final-assessment-pane" role="tabpanel" aria-labelledby="final-assessment-tab" tabindex="0">
                            <div class="applicant-assessment-summary-grid">
                                <div class="applicant-assessment-note-box">
                                    <strong>Summary Matrix</strong>
                                    <div class="applicant-summary-field-grid">
                                        <div class="applicant-summary-field">
                                            <label for="summarySkill">Skill</label>
                                            <input type="text" id="summarySkill" value="Kuat di visual composition, perlu rapikan file final." readonly>
                                        </div>
                                        <div class="applicant-summary-field">
                                            <label for="summaryCommunication">Komunikasi</label>
                                            <input type="text" id="summaryCommunication" value="Presentasi jelas, responsif saat menerima feedback." readonly>
                                        </div>
                                        <div class="applicant-summary-field">
                                            <label for="summaryExperience">Kesesuaian Pengalaman</label>
                                            <input type="text" id="summaryExperience" value="Portofolio cukup relevan dengan kebutuhan brand." readonly>
                                        </div>
                                        <div class="applicant-summary-field">
                                            <label for="summaryTools">Tools</label>
                                            <input type="text" id="summaryTools" value="Adobe Illustrator, Photoshop, Figma." readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="applicant-assessment-note-box">
                                    <strong>Final Verdict</strong>
                                    <div class="applicant-assessment-verdict-badge">Recommended</div>
                                    <div class="mt-2">
                                        Kandidat memenuhi standar komunikasi dan interview user. Technical test masih perlu review minor pada kerapian dokumentasi.
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
                const target = document.getElementById(button.dataset.target);

                if (!target) {
                    return;
                }

                target.select();
                target.setSelectionRange(0, target.value.length);

                try {
                    await navigator.clipboard.writeText(target.value);
                    button.textContent = 'Copied';
                } catch (error) {
                    document.execCommand('copy');
                    button.textContent = 'Copied';
                }
            });
        });
    </script>
@endsection
