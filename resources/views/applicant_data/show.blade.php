@extends('layouts.main')

@section('title', 'Applicant Detail')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .applicant-cv-header {
            display: grid;
            grid-template-columns: 132px minmax(0, 1fr) auto;
            align-items: center;
            gap: 1.25rem;
        }

        .applicant-cv-photo {
            width: 132px;
            height: 132px;
            border-radius: 0.75rem;
            background: #eef2ff;
            color: #2448c7;
            object-fit: cover;
        }

        .applicant-cv-photo-fallback {
            width: 132px;
            height: 132px;
            border-radius: 0.75rem;
            background: #eef2ff;
            color: #2448c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 800;
        }

        .applicant-cv-name {
            color: #111827;
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 0.3rem;
        }

        .applicant-cv-subtitle {
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .applicant-cv-contact {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem 0.85rem;
            color: #334155;
            font-size: 0.9rem;
        }

        .applicant-link-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .applicant-inline-action {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
        }

        .applicant-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .applicant-detail-label {
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .applicant-detail-value {
            color: #172033;
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        .applicant-detail-section + .applicant-detail-section {
            margin-top: 1.25rem;
        }

        .applicant-cv-section-title {
            color: #111827;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0;
            margin-bottom: 0.8rem;
            padding-bottom: 0.45rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .applicant-cv-list {
            display: grid;
            gap: 0.9rem;
        }

        .applicant-cv-item {
            position: relative;
            padding-left: 1.2rem;
        }

        .applicant-cv-item::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0.35rem;
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 50%;
            background: #2448c7;
        }

        .applicant-cv-item-title {
            color: #111827;
            font-size: 0.98rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
        }

        .applicant-cv-item-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .applicant-cv-item-period {
            color: #64748b;
            flex: 0 0 auto;
            font-size: 0.86rem;
            font-weight: 700;
            padding-top: 0.05rem;
            text-align: right;
            white-space: nowrap;
        }

        .applicant-cv-item-meta {
            color: #64748b;
            font-size: 0.88rem;
            margin-bottom: 0.15rem;
        }

        .applicant-cv-item-detail {
            color: #334155;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        @media only screen and (max-width: 767.98px) {
            .applicant-cv-header {
                grid-template-columns: 1fr;
            }

            .applicant-detail-grid {
                grid-template-columns: 1fr;
            }

            .applicant-cv-item-heading {
                display: block;
            }

            .applicant-cv-item-period {
                margin-bottom: 0.2rem;
                text-align: left;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Applicant Detail')

@section('content')
@php
    $photoFile = trim((string) $applicant->photo);
    $photoUrl = $photoFile === ''
        ? null
        : (\Illuminate\Support\Str::startsWith($photoFile, ['http://', 'https://'])
            ? $photoFile
            : 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/'.rawurlencode($photoFile));
    $cvUrl = $applicant->cvDownloadUrl();
    $initials = collect(explode(' ', $applicant->full_name))
        ->filter()
        ->take(2)
        ->map(fn (string $name): string => strtoupper(substr($name, 0, 1)))
        ->implode('');
    $whatsAppUrl = $applicant->whatsAppUrl();
@endphp

<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Applicant Detail</h1></li>
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Main</a></li>
            <li class="breadcrumb-item"><a href="{{ route('applicant') }}">Applicants</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $applicant->full_name }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card h-auto">
            <div class="card-header">
                <div class="applicant-cv-header w-100">
                    <div>
                        @if ($photoUrl)
                            <img
                                src="{{ $photoUrl }}"
                                alt="{{ $applicant->full_name }}"
                                class="applicant-cv-photo"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="applicant-cv-photo-fallback" style="display: none;">{{ $initials ?: 'A' }}</div>
                        @else
                            <div class="applicant-cv-photo-fallback">{{ $initials ?: 'A' }}</div>
                        @endif
                    </div>
                    <div>
                        <h4 class="applicant-cv-name">{{ $applicant->full_name }}</h4>
                        <p class="applicant-cv-subtitle">{{ $applicant->jobVacancy?->name ?? '-' }} | {{ $applicant->statusLabel() }}</p>
                        <div class="applicant-cv-contact">
                            <span>{{ $applicant->email ?? '-' }}</span>
                            <span>{{ $applicant->phone ?? '-' }}</span>
                            <span>{{ collect([$applicant->place_of_birth, $applicant->date_of_birth])->filter()->implode(', ') ?: '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('applicant') }}" class="btn btn-primary light btn-sm">Back</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="applicant-detail-section">
                    <h5 class="mb-3">Personal Data</h5>
                    <div class="applicant-detail-grid">
                        <div>
                            <div class="applicant-detail-label">Email</div>
                            <div class="applicant-inline-action">
                                <p class="applicant-detail-value">{{ $applicant->email ?? '-' }}</p>
                                @if ($applicant->email)
                                    <a href="mailto:{{ $applicant->email }}" class="btn btn-primary light btn-xs">
                                        <i class="bi bi-envelope me-1"></i>Mailto
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="applicant-detail-label">Phone</div>
                            <div class="applicant-inline-action">
                                <p class="applicant-detail-value">{{ $applicant->phone ?? '-' }}</p>
                                @if ($whatsAppUrl)
                                    <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener" class="btn btn-success light btn-xs">
                                        <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="applicant-detail-label">Nickname</div>
                            <p class="applicant-detail-value">{{ $applicant->nickname ?? '-' }}</p>
                        </div>
                        <div>
                            <div class="applicant-detail-label">Birth</div>
                            <p class="applicant-detail-value">{{ collect([$applicant->place_of_birth, $applicant->date_of_birth])->filter()->implode(', ') ?: '-' }}</p>
                        </div>
                        <div>
                            <div class="applicant-detail-label">Gender</div>
                            <p class="applicant-detail-value">{{ $applicant->gender?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <div class="applicant-detail-label">Marital Status</div>
                            <p class="applicant-detail-value">{{ $applicant->maritalStatus?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <div class="applicant-detail-label">Expected Salary</div>
                            <p class="applicant-detail-value">{{ $applicant->expected_salary ?? '-' }}</p>
                        </div>
                        <div>
                            <div class="applicant-detail-label">Applied At</div>
                            <p class="applicant-detail-value">{{ $applicant->legacy_created_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="applicant-detail-section">
                    <h5 class="mb-3">Address</h5>
                    <p class="applicant-detail-value">{{ $applicant->address ?? '-' }}</p>
                </div>

                <div class="applicant-detail-section">
                    <h5 class="mb-3">Self Resume</h5>
                    <p class="applicant-detail-value">{{ $applicant->self_resume ?? '-' }}</p>
                </div>

                <div class="applicant-detail-section">
                    <h5 class="applicant-cv-section-title">Education</h5>
                    <div class="applicant-cv-list">
                        @forelse ($applicant->educations as $education)
                            <div class="applicant-cv-item">
                                <div class="applicant-cv-item-heading">
                                    <h6 class="applicant-cv-item-title">
                                        {{ $education->educationLevel?->name ?? 'Education' }}
                                        @if ($education->institution)
                                            - {{ $education->institution }}
                                        @endif
                                    </h6>
                                    <div class="applicant-cv-item-period">{{ collect([$education->start_period, $education->graduate_period])->filter()->implode(' - ') ?: '-' }}</div>
                                </div>
                                <p class="applicant-cv-item-detail">{{ collect([$education->department, $education->gpa ? 'GPA '.$education->gpa : null])->filter()->implode(' - ') ?: '-' }}</p>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No education data.</p>
                        @endforelse
                    </div>
                </div>

                <div class="applicant-detail-section">
                    <h5 class="applicant-cv-section-title">Work Experience</h5>
                    <div class="applicant-cv-list">
                        @forelse ($applicant->workExperiences as $experience)
                            <div class="applicant-cv-item">
                                <div class="applicant-cv-item-heading">
                                    <h6 class="applicant-cv-item-title">
                                        {{ $experience->company_name ?? 'Work Experience' }}
                                        @if ($experience->role)
                                            - <strong>{{ $experience->role }}</strong>
                                        @endif
                                    </h6>
                                    <div class="applicant-cv-item-period">{{ collect([$experience->start_period, $experience->end_period])->filter()->implode(' - ') ?: '-' }}</div>
                                </div>
                                <p class="applicant-cv-item-meta">{{ $experience->company_location ?? '-' }}</p>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No work experience data.</p>
                        @endforelse
                    </div>
                </div>

                <div class="applicant-detail-section">
                    <h5 class="mb-3">Files and Links</h5>
                    <div class="applicant-link-actions">
                        <div>
                            @if ($cvUrl)
                                <a href="{{ $cvUrl }}" target="_blank" rel="noopener" class="btn btn-primary light btn-sm">
                                    <i class="bi bi-download me-1"></i>Download CV
                                </a>
                            @else
                                <button type="button" class="btn btn-primary light btn-sm" disabled>
                                    <i class="bi bi-file-earmark-text me-1"></i>No CV
                                </button>
                            @endif
                        </div>
                        <div>
                            @forelse ($applicant->portfolioLinks() as $portfolioLink)
                                <a href="{{ $portfolioLink }}" target="_blank" rel="noopener" class="btn btn-info light btn-sm">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Open Portfolio {{ $loop->iteration }}
                                </a>
                            @empty
                                <button type="button" class="btn btn-info light btn-sm" disabled>
                                    <i class="bi bi-box-arrow-up-right me-1"></i>No Portfolio
                                </button>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
