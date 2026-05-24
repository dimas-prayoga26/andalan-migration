<!-- Start - Profile Header -->
    <style>
        @media (max-width: 767.98px) {
            .mobile-stats-offset {
                margin-top: -8px;
            }

            .mobile-stats-slider {
                gap: 8px;
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
                    $defaultAvatarPath = asset('assets/images/avatar/large/avatar5.webp');
                    $profilePictureValue = is_string($profilePicturePath ?? null) ? trim($profilePicturePath) : '';
                    $profileAvatarPath = $profilePictureValue === ''
                        ? $defaultAvatarPath
                        : (\Illuminate\Support\Str::startsWith($profilePictureValue, ['http://', 'https://'])
                            ? $profilePictureValue
                            : asset(ltrim($profilePictureValue, '/')));
                @endphp
                <img src="{{ $profileAvatarPath }}" class="avatar avatar-xxl" alt="User Avatar">
                <span class="fa fa-circle border border-3 border-white text-success position-absolute bottom-0 end-0 rounded-circle"></span>
            </div>
        </div>
        <div class="clearfix d-xl-flex flex-grow-1">
            <div class="clearfix pe-md-5">
                <h3 class="fw-semibold mb-1">{{ $profileDisplayName ?? '-' }}</h3>
                <ul class="d-flex flex-wrap align-items-center">
                    <li class="me-3 d-inline-flex align-items-center">
                        <i class="las la-suitcase me-1"></i>{{ $profilePositionName ?? '-' }}
                    </li>
                    <li class="me-3 d-inline-flex align-items-center">
                        <i class="las la-map-marker me-1"></i>{{ $profileAddressSummary ?? '-' }}
                    </li>
                    <li class="me-3 d-inline-flex align-items-center">
                        <i class="las la-envelope me-1"></i>{{ $profileBusinessEmail ?? '-' }}
                    </li>
                </ul>
                @php
                    $attendanceDaysCount = (int) ($profileAttendanceDaysCount ?? 0);
                    $workingDaysCount = (int) ($profileWorkingDaysCount ?? 0);
                    $workingMonthLabel = is_string($profileWorkingMonthLabel ?? null) && trim($profileWorkingMonthLabel) !== ''
                        ? trim($profileWorkingMonthLabel)
                        : now('Asia/Jakarta')->format('F');
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
                                    <circle cx="12" cy="12" r="9" stroke="var(--bs-primary)" stroke-width="2" />
                                    <path d="M12 7V12L15 14" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">10</h3>
                                <span class="small">On Going Task (June)</span>
                            </div>
                        </div>
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                            <div class="avatar avatar-sm avatar-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 6H21" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M10 12H21" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M10 18H21" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                                    <path d="M3 6L4.5 7.5L7 5" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M3 12L4.5 13.5L7 11" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M3 18L4.5 19.5L7 17" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="clearfix ms-2">
                                <h3 class="mb-0 fw-semibold lh-1">25</h3>
                                <span class="small">Task Complete (June)</span>
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
            <div class="clearfix mt-3 mt-xl-0 ms-auto d-flex flex-column col-xl-3">
                <div class="mt-auto d-flex align-items-center">
                    <div class="clearfix me-3">
                        <span class="fw-medium text-black d-block mb-1">Progress</span>
                        <p class="mb-0 d-flex">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.83334 14.1668L14.1667 5.8335" stroke="var(--bs-success)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M5.83334 5.8335H14.1667V14.1668" stroke="var(--bs-success)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <span class="text-success me-1">+3.50%</span>
                        </p>
                    </div>
                    <div id="chartProfileProgress"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body pt-0 d-md-none mobile-stats-offset">
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
                            <circle cx="12" cy="12" r="9" stroke="var(--bs-primary)" stroke-width="2" />
                            <path d="M12 7V12L15 14" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">10</h3>
                        <span class="small">On Going Task (June)</span>
                    </div>
                </div>
                <div class="border outline-dashed rounded p-2 d-flex align-items-center flex-shrink-0 mobile-stats-card" style="min-width: 100%;">
                    <div class="avatar avatar-sm avatar-primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 6H21" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M10 12H21" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M10 18H21" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" />
                            <path d="M3 6L4.5 7.5L7 5" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M3 12L4.5 13.5L7 11" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M3 18L4.5 19.5L7 17" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">25</h3>
                        <span class="small">Task Complete (June)</span>
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
