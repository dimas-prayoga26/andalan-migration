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

        .applicant-rating-buttons {
            display: inline-flex;
            gap: 0.25rem;
        }

        .applicant-rating-button {
            width: 32px;
            height: 32px;
            border: 1px solid #d9dce5;
            border-radius: 0.45rem;
            background: #fff;
            color: #475569;
            cursor: default;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .applicant-rating-button.active {
            border-color: #2448c7;
            background: #2448c7;
            color: #fff;
        }

        .applicant-assessment-upload {
            border: 1px dashed #cbd5e1;
            border-radius: 0.75rem;
            background: #f8fafc;
            color: #64748b;
            padding: 1rem;
            text-align: center;
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
                            <div class="applicant-assessment-metric-value">82 / Draft</div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Assessment Test</div>
                            <div class="applicant-assessment-metric-value">Belum Upload</div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Technical Test</div>
                            <div class="applicant-assessment-metric-value">76 / Draft</div>
                        </div>
                        <div class="applicant-assessment-metric">
                            <div class="applicant-assessment-metric-label">Final Verdict</div>
                            <div class="applicant-assessment-metric-value">Keep in View</div>
                        </div>
                    </div>

                    <div class="applicant-assessment-panel">
                        <div class="applicant-assessment-panel-title">Form Interview HR</div>
                        <div class="applicant-assessment-panel-subtitle">Evaluasi dasar, komunikasi, motivasi, dan culture fit.</div>
                        @foreach ($hrCriteria as $criteria)
                            <div class="applicant-rating-row">
                                <div>
                                    <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                    <div class="applicant-rating-weight">Bobot {{ $criteria['weight'] }}</div>
                                </div>
                                <div class="applicant-rating-buttons" aria-label="{{ $criteria['label'] }}">
                                    @for ($rating = 1; $rating <= 5; $rating++)
                                        <button type="button" class="applicant-rating-button {{ $rating === $criteria['selected'] ? 'active' : '' }}" title="{{ $rating === 1 ? 'Sangat Kurang' : ($rating === 5 ? 'Sangat Baik' : 'Nilai '.$rating) }}">{{ $rating }}</button>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="applicant-assessment-panel">
                        <div class="applicant-assessment-panel-title">Assessment Test</div>
                        <div class="applicant-assessment-panel-subtitle">Area lampiran hasil tes administrasi atau psikologi dasar.</div>
                        <div class="applicant-assessment-upload">
                            <i class="bi bi-cloud-arrow-up fs-3 d-block mb-2"></i>
                            <div class="fw-semibold">Upload hasil assessment</div>
                            <div class="small">Preview gambar akan tampil di area ini.</div>
                        </div>
                    </div>

                    <div class="applicant-assessment-panel">
                        <div class="applicant-assessment-panel-title">Technical Test</div>
                        <div class="applicant-assessment-panel-subtitle">Contoh kriteria dinamis sesuai posisi yang dilamar.</div>
                        @foreach ($technicalCriteria as $criteria)
                            <div class="applicant-rating-row">
                                <div>
                                    <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                    <div class="applicant-rating-weight">Bobot {{ $criteria['weight'] }}</div>
                                </div>
                                <div class="applicant-rating-buttons" aria-label="{{ $criteria['label'] }}">
                                    @for ($rating = 1; $rating <= 5; $rating++)
                                        <button type="button" class="applicant-rating-button {{ $rating === $criteria['selected'] ? 'active' : '' }}" title="{{ $rating === 1 ? 'Sangat Kurang' : ($rating === 5 ? 'Sangat Baik' : 'Nilai '.$rating) }}">{{ $rating }}</button>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                        <div class="applicant-assessment-note-box mt-3">Catatan reviewer technical test akan ditampilkan di sini.</div>
                    </div>

                    <div class="applicant-assessment-panel">
                        <div class="applicant-assessment-panel-title">Interview User</div>
                        <div class="applicant-assessment-panel-subtitle">Evaluasi teknis lanjutan oleh atasan divisi atau user terkait.</div>
                        @foreach ($userCriteria as $criteria)
                            <div class="applicant-rating-row">
                                <div>
                                    <div class="applicant-rating-label">{{ $criteria['label'] }}</div>
                                    <div class="applicant-rating-weight">Bobot {{ $criteria['weight'] }}</div>
                                </div>
                                <div class="applicant-rating-buttons" aria-label="{{ $criteria['label'] }}">
                                    @for ($rating = 1; $rating <= 5; $rating++)
                                        <button type="button" class="applicant-rating-button {{ $rating === $criteria['selected'] ? 'active' : '' }}" title="{{ $rating === 1 ? 'Sangat Kurang' : ($rating === 5 ? 'Sangat Baik' : 'Nilai '.$rating) }}">{{ $rating }}</button>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>

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
@endsection
