		<!-- Start - Sidebar -->
          <div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
					@php
						$isDashboardMenu = request()->routeIs('dashboard');
						$isCalendarMenu = request()->routeIs('activity-schadule*');
						$isAttendanceMenu = request()->routeIs('attendance*') || request()->is('attendance*');
						$isAdminAttendanceMenu = request()->routeIs('admin-attendance*') || request()->is('admin-attendance*');
						$isPicAttendanceMenu = request()->routeIs('pic-attendance*') || request()->is('pic-attendance*');
						$isDirectorAttendanceMenu = request()->routeIs('director-attendance*') || request()->is('director-attendance*');
						$isReportingMenu = request()->routeIs('project_management', 'project_management.detail') || request()->is('project-management*');
						$isAuthorizationMenu = request()->routeIs('authorization*') || request()->is('authorization*');
						$isSettingsMenu = request()->routeIs('settings*') || request()->is('settings*');
						$canViewSidebarMenu = $canViewSidebarMenu ?? static fn (string $permissionName): bool => true;
						$canViewDashboardMenu = $canViewSidebarMenu('view-dashboard');
						$canViewCalendarMenu = $canViewSidebarMenu('view-calendar');
						$canViewAttendanceMenu = $canViewSidebarMenu('view-attendance');
						$canViewTimesheetReportingMenu = $canViewSidebarMenu('view-timesheet-reporting');
						$canViewAdminAttendanceMenu = $canViewSidebarMenu('view-admin-attendance');
						$canViewPicAttendanceMenu = $canViewSidebarMenu('view-pic-attendance');
						$canViewDirectorAttendanceMenu = $canViewSidebarMenu('view-director-attendance');
						$canViewAuthorizationMenu = $canViewSidebarMenu('view-authorization');
						$canViewSettingsMenu = $canViewSidebarMenu('view-settings');
					@endphp
					@if ($canViewDashboardMenu || $canViewCalendarMenu)
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Main</strong> </p>
					</div>
					@endif
					@if ($canViewDashboardMenu)
                    <li class="{{ $isDashboardMenu ? 'mm-active' : '' }}">
						<a class="{{ $isDashboardMenu ? 'active' : '' }}" href="{{ route('dashboard') }}">
							<i class="fa-solid fa-gauge"></i>
							<span class="nav-text" data-i18n="Dashboard">Dashboard</span>
						</a>
                    </li>
					@endif
					@if ($canViewCalendarMenu)
					<li class="{{ $isCalendarMenu ? 'mm-active' : '' }}">
						<a class="{{ $isCalendarMenu ? 'active' : '' }}" href="{{ route('activity-schadule') }}">
							<i class="fa-regular fa-calendar-days"></i>
							<span class="nav-text" data-i18n="Activity Calendar">Activity Calendar </span>
						</a>
					</li>
					@endif
					@if ($canViewAttendanceMenu || $canViewTimesheetReportingMenu)
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Siap</strong> </p>
					</div>
					@endif
					@if ($canViewAttendanceMenu)
					<li class="{{ $isAttendanceMenu ? 'mm-active' : '' }}">
						<a class="{{ $isAttendanceMenu ? 'active' : '' }}" href="{{ route('attendance') }}" aria-expanded="{{ $isAttendanceMenu ? 'true' : 'false' }}">
							<i class="fa-regular fa-clock"></i>
							<span class="nav-text" data-i18n="Attendance">Attendance </span>
						</a>
					</li>
					@endif
					@if ($canViewTimesheetReportingMenu)
					<li class="{{ $isReportingMenu ? 'mm-active' : '' }}">
						<a class="{{ $isReportingMenu ? 'active' : '' }}" href="{{ route('project_management') }}" aria-expanded="{{ $isReportingMenu ? 'true' : 'false' }}">
							<i class="fa-regular fa-file-lines"></i>
							<span class="nav-text" data-i18n="Timesheet & Reporting">Timesheet & Reporting </span>
						</a>
					</li>
					@endif
					@if ($canViewAdminAttendanceMenu || $canViewPicAttendanceMenu || $canViewDirectorAttendanceMenu || $canViewAuthorizationMenu)
					<div class="copyright mt-1">
						<p class="mb-1"><strong>HR Management</strong> </p>
					</div>
					@endif
					@if ($canViewAdminAttendanceMenu)
					<li class="{{ $isAdminAttendanceMenu ? 'mm-active' : '' }}">
						<a class="{{ $isAdminAttendanceMenu ? 'active' : '' }}" href="{{ route('admin-attendance.overview') }}" aria-expanded="{{ $isAdminAttendanceMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-user-clock"></i>
							<span class="nav-text" data-i18n="Admin Attendance">Admin Attendance </span>
						</a>
					</li>
					@endif
					@if ($canViewPicAttendanceMenu)
					<li class="{{ $isPicAttendanceMenu ? 'mm-active' : '' }}">
						<a class="{{ $isPicAttendanceMenu ? 'active' : '' }}" href="{{ route('pic-attendance.attendance') }}" aria-expanded="{{ $isPicAttendanceMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-clipboard-check"></i>
							<span class="nav-text" data-i18n="PIC">PIC</span>
						</a>
					</li>
					@endif
					@if ($canViewDirectorAttendanceMenu)
					<li class="{{ $isDirectorAttendanceMenu ? 'mm-active' : '' }}">
						<a class="{{ $isDirectorAttendanceMenu ? 'active' : '' }}" href="{{ route('director-attendance.attendance') }}" aria-expanded="{{ $isDirectorAttendanceMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-user-tie"></i>
							<span class="nav-text" data-i18n="Director">Director</span>
						</a>
					</li>
					@endif
					@if ($canViewAuthorizationMenu)
					<li class="{{ $isAuthorizationMenu ? 'mm-active' : '' }}">
						<a class="{{ $isAuthorizationMenu ? 'active' : '' }}" href="{{ route('authorization') }}" aria-expanded="{{ $isAuthorizationMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-id-card"></i>
							<span class="nav-text" data-i18n="Employee Data">Employee Data </span>
						</a>
					</li>
					@endif
					@if ($canViewSettingsMenu)
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Setting</strong> </p>
					</div>
					<li class="{{ $isSettingsMenu ? 'mm-active' : '' }}">
						<a class="has-arrow {{ $isSettingsMenu ? 'active' : '' }}" href="javascript:void(0);" aria-expanded="{{ $isSettingsMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-gear"></i>
							<span class="nav-text" data-i18n="Setting">Setting</span>
						</a>
						<ul aria-expanded="{{ $isSettingsMenu ? 'true' : 'false' }}" class="{{ $isSettingsMenu ? 'mm-show' : '' }}">
							<li>
								<a class="{{ request()->routeIs('settings.divisions*') ? 'active' : '' }}" href="{{ route('settings.divisions.index') }}">Division</a>
							</li>
							<li>
								<a class="{{ request()->routeIs('settings.positions*') ? 'active' : '' }}" href="{{ route('settings.positions.index') }}">Position</a>
							</li>
						</ul>
					</li>
					@endif
                </ul>
			</div>
        </div>
        <!-- End - Sidebar -->
