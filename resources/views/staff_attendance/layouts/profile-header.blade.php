<!-- Start - Profile Header -->
    <style>
        .profile-header-content,
        .profile-header-identity,
        .profile-contact-list,
        .profile-contact-email {
            min-width: 0;
        }

        .profile-header-identity {
            max-width: 100%;
        }

        .profile-header-identity h3,
        .profile-contact-email {
            overflow-wrap: anywhere;
        }

        .profile-contact-email {
            max-width: 100%;
        }

        .profile-contact-email i {
            flex: 0 0 auto;
        }

        .profile-contact-email span {
            min-width: 0;
            max-width: 100%;
            word-break: break-word;
        }

        @media (max-width: 767.98px) {
            .mobile-stats-offset {
                margin-top: -8px;
            }

            .mobile-stats-slider {
                gap: 8px;
                margin-top: 1rem;
                scroll-snap-type: x mandatory;
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .mobile-stats-card {
                min-width: calc(100% - 8px) !important;
                scroll-snap-align: start;
            }

            .mobile-stats-slider::-webkit-scrollbar {
                display: none;
                width: 0;
                height: 0;
            }
        }
    </style>
    <div class="card-body d-flex py-md-4">
        <div class="clearfix">
            <div class="d-inline-block position-relative me-sm-4 me-3 mb-3 mb-lg-0">
                @php
                    $defaultAvatarPath = asset('assets/default_user.jpg');
                    $profilePictureValue = is_string($profilePicturePath ?? null) ? trim($profilePicturePath) : '';
                    $profileAvatarPath = $profilePictureValue === ''
                        ? $defaultAvatarPath
                        : (\Illuminate\Support\Str::startsWith($profilePictureValue, ['http://', 'https://'])
                            ? $profilePictureValue
                            : asset(ltrim($profilePictureValue, '/')));
                @endphp
                <img src="{{ $profileAvatarPath }}" class="avatar avatar-xxl" alt="User Avatar">
            </div>
        </div>
        <div class="clearfix d-xl-flex flex-grow-1 profile-header-content">
            <div class="clearfix pe-md-5 profile-header-identity">
                <h3 class="fw-semibold mb-1">{{ $profileDisplayName ?? '-' }}</h3>
                <ul class="d-flex flex-wrap align-items-center profile-contact-list">
                    <li class="me-3 d-inline-flex align-items-center">
                        <i class="las la-suitcase me-1"></i>{{ $profilePositionName ?? '-' }}
                    </li>
                    <li class="me-3 d-inline-flex align-items-center">
                        <i class="las la-map-marker me-1"></i>{{ $profileAddressSummary ?? '-' }}
                    </li>
                    <li class="me-3 d-inline-flex align-items-start profile-contact-email">
                        <i class="las la-envelope me-1 mt-1"></i><span>{{ $profileBusinessEmail ?? '-' }}</span>
                    </li>
                </ul>
                @php
                    $attendanceDaysCount = (int) ($profileAttendanceDaysCount ?? 0);
                    $workingDaysCount = (int) ($profileWorkingDaysCount ?? 0);
                    $workingMonthLabel = is_string($profileWorkingMonthLabel ?? null) && trim($profileWorkingMonthLabel) !== ''
                        ? trim($profileWorkingMonthLabel)
                        : now('Asia/Jakarta')->format('F');
                    $lateInCount = (int) ($profileLateInCount ?? 0);
                    $leavesAndSickCount = (int) ($profileLeavesAndSickCount ?? 0);
                    $weeklyAttendancePercent = (int) ($profileWeeklyAttendancePercent ?? 0);
                    $weeklyOnTimePercent = (int) ($profileWeeklyOnTimePercent ?? 0);
                    $monthlyAttendanceLabels = is_array($profileMonthlyAttendanceLabels ?? null) ? $profileMonthlyAttendanceLabels : [];
                    $monthlyAttendanceSeries = is_array($profileMonthlyAttendanceSeries ?? null) ? $profileMonthlyAttendanceSeries : [];
                    $monthlyAttendanceDelta = (float) ($profileMonthlyAttendanceDelta ?? 0);
                    $monthlyAttendanceDeltaLabel = ($monthlyAttendanceDelta >= 0 ? '+' : '').number_format($monthlyAttendanceDelta, 2).'%';
                    $monthlyAttendanceDeltaClass = $monthlyAttendanceDelta >= 0 ? 'text-success' : 'text-danger';
                    $monthlyAttendanceDeltaStrokeColor = $monthlyAttendanceDelta >= 0 ? 'var(--bs-success)' : 'var(--bs-danger)';
                    $profileStatsModeValue = is_string($profileStatsMode ?? null) ? $profileStatsMode : 'staff';
                    $managementTotalEmployees = (int) ($managementTotalEmployeesCount ?? 0);
                    $managementPresentToday = (int) ($managementPresentTodayCount ?? 0);
                    $managementLateToday = (int) ($managementLateTodayCount ?? 0);
                    $managementLeaveToday = (int) ($managementLeaveTodayCount ?? 0);
                @endphp

                @if ($profileStatsModeValue === 'staff')
                    <div class="d-md-flex d-none flex-wrap">
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="4" width="18" height="17" rx="2" stroke="var(--bs-primary)" stroke-width="2" />
                                    <path d="M8 2V6" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M16 2V6" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M3 10H21" stroke="var(--bs-primary)" stroke-width="2" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">{{ $attendanceDaysCount }} / {{ $workingDaysCount }}</h3>
                                <span class="small">Attendance Days ({{ $workingMonthLabel }})</span>
                            </div>
                        </div>
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="8" stroke="var(--bs-primary)" stroke-width="2" />
                                    <path d="M12 7V12L15 14" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M18.5 5.5L20.5 3.5" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <circle cx="18" cy="18" r="2" fill="var(--bs-primary)" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">{{ $lateInCount }}</h3>
                                <span class="small">Late In</span>
                            </div>
                        </div>
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="5" y="3" width="14" height="18" rx="2" stroke="var(--bs-primary)" stroke-width="2" />
                                    <path d="M9 3V7H15V3" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M12 11V17" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M9 14H15" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">{{ $leavesAndSickCount }}</h3>
                                <span class="small">Leaves & Sick</span>
                            </div>
                        </div>
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 19V5" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M4 19H20" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <rect x="7" y="12" width="3" height="4" rx="1" stroke="var(--bs-primary)" stroke-width="2" />
                                    <rect x="12" y="9" width="3" height="7" rx="1" stroke="var(--bs-primary)" stroke-width="2" />
                                    <rect x="17" y="6" width="3" height="10" rx="1" stroke="var(--bs-primary)" stroke-width="2" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">{{ $weeklyAttendancePercent }}%</h3>
                                <span class="small">Attendance</span>
                            </div>
                        </div>
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="8" stroke="var(--bs-primary)" stroke-width="2" />
                                    <path d="M12 7V12L14.5 13.5" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M8 18L10 20L15 15" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">{{ $weeklyOnTimePercent }}%</h3>
                                <span class="small">On-Time</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="d-md-flex d-none flex-wrap">
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="8" cy="8" r="3" stroke="var(--bs-primary)" stroke-width="2" />
                                    <circle cx="16" cy="8" r="3" stroke="var(--bs-primary)" stroke-width="2" />
                                    <path d="M2.5 18C2.5 15.7909 4.29086 14 6.5 14H9.5C11.7091 14 13.5 15.7909 13.5 18" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M10.5 18C10.5 15.7909 12.2909 14 14.5 14H17.5C19.7091 14 21.5 15.7909 21.5 18" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">{{ $managementPresentToday }} / {{ $managementTotalEmployees }}</h3>
                                <span class="small">Staff Presence (Today)</span>
                            </div>
                        </div>
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="8" stroke="var(--bs-primary)" stroke-width="2" />
                                    <path d="M12 8V12" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <circle cx="12" cy="16" r="1" fill="var(--bs-primary)" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">{{ $managementLateToday }}</h3>
                                <span class="small">Staff Late (Today)</span>
                            </div>
                        </div>
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="5" y="3" width="14" height="18" rx="2" stroke="var(--bs-primary)" stroke-width="2" />
                                    <path d="M8 8H16" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M8 12H16" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M8 16H13" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">{{ $managementLeaveToday }}</h3>
                                <span class="small">Staff Leave (Today)</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="clearfix mt-3 mt-xl-0 ms-auto d-none d-md-flex flex-column col-xl-3">
                <div class="clearfix mb-3 text-xl-end">
                    <a class="btn btn-primary mb-1">My Performance</a>
                </div>
                <div class="mt-auto d-flex align-items-center">
                    <div class="clearfix me-3">
                        <span class="fw-medium text-black d-block mb-1">Progress</span>
                        <p class="mb-0 d-flex">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.83334 14.1668L14.1667 5.8335" stroke="{{ $monthlyAttendanceDeltaStrokeColor }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M5.83334 5.8335H14.1667V14.1668" stroke="{{ $monthlyAttendanceDeltaStrokeColor }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <span class="{{ $monthlyAttendanceDeltaClass }} me-1">{{ $monthlyAttendanceDeltaLabel }}</span>
                        </p>
                    </div>
                    <div
                        id="chartProfileProgressDesktop"
                        data-progress-series='@json($monthlyAttendanceSeries)'
                        data-progress-labels='@json($monthlyAttendanceLabels)'></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body pt-0 d-md-none mobile-stats-offset">
        <div class="clearfix mt-3 d-flex flex-column">
            <div class="clearfix mb-3 text-end">
                <a class="btn btn-primary mb-1">My Performance</a>
            </div>
            <div class="mt-auto d-flex align-items-center">
                <div class="clearfix me-3">
                    <span class="fw-medium text-black d-block mb-1">Progress</span>
                    <p class="mb-0 d-flex">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.83334 14.1668L14.1667 5.8335" stroke="{{ $monthlyAttendanceDeltaStrokeColor }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M5.83334 5.8335H14.1667V14.1668" stroke="{{ $monthlyAttendanceDeltaStrokeColor }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span class="{{ $monthlyAttendanceDeltaClass }} me-1">{{ $monthlyAttendanceDeltaLabel }}</span>
                    </p>
                </div>
                <div
                    id="chartProfileProgress"
                    data-progress-series='@json($monthlyAttendanceSeries)'
                    data-progress-labels='@json($monthlyAttendanceLabels)'></div>
            </div>
        </div>
        @if ($profileStatsModeValue === 'staff')
            <div class="d-flex flex-nowrap overflow-auto pb-1 mobile-stats-slider">
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="4" width="18" height="17" rx="2" stroke="var(--bs-primary)" stroke-width="2" />
                            <path d="M8 2V6" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M16 2V6" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M3 10H21" stroke="var(--bs-primary)" stroke-width="2" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ $attendanceDaysCount }} / {{ $workingDaysCount }}</h3>
                        <span class="small">Attendance Days ({{ $workingMonthLabel }})</span>
                    </div>
                </div>
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="8" stroke="var(--bs-primary)" stroke-width="2" />
                            <path d="M12 7V12L15 14" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M18.5 5.5L20.5 3.5" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <circle cx="18" cy="18" r="2" fill="var(--bs-primary)" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ $lateInCount }}</h3>
                        <span class="small">Late In</span>
                    </div>
                </div>
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="5" y="3" width="14" height="18" rx="2" stroke="var(--bs-primary)" stroke-width="2" />
                            <path d="M9 3V7H15V3" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M12 11V17" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M9 14H15" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ $leavesAndSickCount }}</h3>
                        <span class="small">Leaves & Sick</span>
                    </div>
                </div>
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 19V5" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M4 19H20" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <rect x="7" y="12" width="3" height="4" rx="1" stroke="var(--bs-primary)" stroke-width="2" />
                            <rect x="12" y="9" width="3" height="7" rx="1" stroke="var(--bs-primary)" stroke-width="2" />
                            <rect x="17" y="6" width="3" height="10" rx="1" stroke="var(--bs-primary)" stroke-width="2" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ $weeklyAttendancePercent }}%</h3>
                        <span class="small">Attendance</span>
                    </div>
                </div>
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="8" stroke="var(--bs-primary)" stroke-width="2" />
                            <path d="M12 7V12L14.5 13.5" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M8 18L10 20L15 15" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ $weeklyOnTimePercent }}%</h3>
                        <span class="small">On-Time</span>
                    </div>
                </div>
            </div>
        @else
            <div class="d-flex flex-nowrap overflow-auto pb-1 mobile-stats-slider">
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="3" stroke="var(--bs-primary)" stroke-width="2" />
                            <circle cx="16" cy="8" r="3" stroke="var(--bs-primary)" stroke-width="2" />
                            <path d="M2.5 18C2.5 15.7909 4.29086 14 6.5 14H9.5C11.7091 14 13.5 15.7909 13.5 18" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M10.5 18C10.5 15.7909 12.2909 14 14.5 14H17.5C19.7091 14 21.5 15.7909 21.5 18" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ $managementPresentToday }} / {{ $managementTotalEmployees }}</h3>
                        <span class="small">Staff Presence (Today)</span>
                    </div>
                </div>
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="8" stroke="var(--bs-primary)" stroke-width="2" />
                            <path d="M12 8V12" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <circle cx="12" cy="16" r="1" fill="var(--bs-primary)" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ $managementLateToday }}</h3>
                        <span class="small">Staff Late (Today)</span>
                    </div>
                </div>
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="5" y="3" width="14" height="18" rx="2" stroke="var(--bs-primary)" stroke-width="2" />
                            <path d="M8 8H16" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M8 12H16" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M8 16H13" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ $managementLeaveToday }}</h3>
                        <span class="small">Staff Leave (Today)</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- End - Profile Header -->
