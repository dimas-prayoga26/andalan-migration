		<!-- Start - Sidebar -->
          <div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
					@php
						$isDashboardMenu = request()->routeIs('dashboard');
						$isCalendarMenu = request()->routeIs('activity-schadule*');
						$isAttendanceMenu = request()->routeIs('attendance*') || request()->is('attendance*');
						$isAdminAttendanceMenu = request()->routeIs('admin-attendance*') || request()->is('admin-attendance*');
						$isReportingMenu = request()->routeIs('project_management', 'project_management.detail') || request()->is('project-management*');
						$isMeetingMenu = request()->routeIs('agenda');
						$isOrganizationMenu = request()->routeIs('employee_data*') || request()->is('employee-data*');
						$isEmployeeDatabaseMenu = request()->routeIs('employee_data*') || request()->is('employee-data*');
						$isAuthorizationMenu = request()->routeIs('authorization*') || request()->is('authorization*');
						$isTalentAcquisitionMenu = request()->routeIs('applicant*') || request()->is('applicant*');
						$canViewSidebarMenu = $canViewSidebarMenu ?? static fn (string $permissionName): bool => true;
						$canViewDashboardMenu = $canViewSidebarMenu('view-dashboard');
						$canViewCalendarMenu = $canViewSidebarMenu('view-calendar');
						$canViewAttendanceMenu = $canViewSidebarMenu('view-attendance');
						$canViewTimesheetReportingMenu = $canViewSidebarMenu('view-timesheet-reporting');
						$canViewMeetingMenu = $canViewSidebarMenu('view-meeting');
						$canViewAdminAttendanceMenu = $canViewSidebarMenu('view-admin-attendance');
						$canViewOrganizationMenu = $canViewSidebarMenu('view-organization');
						$canViewAuthorizationMenu = $canViewSidebarMenu('view-authorization');
						$canViewEmployeeDatabaseMenu = $canViewSidebarMenu('view-employee-database');
						$canViewTalentAcquisitionMenu = $canViewSidebarMenu('view-talent-acquisition');
						$canViewPayrollMenu = $canViewSidebarMenu('view-payroll');
						$canViewEmployeeServicesMenu = $canViewSidebarMenu('view-employee-services');
					@endphp
					@if ($canViewDashboardMenu || $canViewCalendarMenu)
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Main</strong> </p>
					</div>
					@endif
					@if ($canViewDashboardMenu)
                    <li class="{{ $isDashboardMenu ? 'mm-active' : '' }}">
						<a class="{{ $isDashboardMenu ? 'mm-active' : '' }}" href="{{ route('dashboard') }}">
							<i class="fa-solid fa-gauge"></i>
							<span class="nav-text" data-i18n="Dashboard">Dashboard</span>
						</a>
                    </li>
					@endif
					@if ($canViewCalendarMenu)
					<li class="{{ $isCalendarMenu ? 'mm-active' : '' }}">
						<a class="{{ $isCalendarMenu ? 'active' : '' }}" href="{{ route('activity-schadule') }}">
							<i class="fa-regular fa-calendar-days"></i>
							<span class="nav-text" data-i18n="Google Calendar">Google Calendar </span>
						</a>
					</li>
					@endif
					@if ($canViewAttendanceMenu || $canViewTimesheetReportingMenu || $canViewMeetingMenu)
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
					@if ($canViewMeetingMenu)
					<li class="{{ $isMeetingMenu ? 'mm-active' : '' }}">
						<a class="{{ $isMeetingMenu ? 'active' : '' }}" href="{{ route('agenda') }}" aria-expanded="{{ $isMeetingMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-users-viewfinder"></i>
							<span class="nav-text" data-i18n="Zoom Meeting">Zoom Meeting </span>
						</a>
					</li>
					@endif
					<div class="copyright mt-1">
						<p class="mb-1"><strong>My Account</strong> </p>
					</div>
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-regular fa-user"></i>
							<span class="nav-text" data-i18n="Profile">Profile </span>
						</a>
					</li> 
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-solid fa-chalkboard-user"></i>
							<span class="nav-text" data-i18n="Learning">Learning </span>
						</a>
					</li>
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-solid fa-user-shield"></i>
							<span class="nav-text" data-i18n="Employee Self-Service">Employee Self-Service </span>
						</a>
					</li>
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-regular fa-file-powerpoint"></i>
							<span class="nav-text" data-i18n="Policies and Procedures">Policies and Procedures </span>
						</a>
					</li>
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Website Management</strong> </p>
					</div>
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-solid fa-rss"></i>
							<span class="nav-text" data-i18n="Blog Management">Blog Management </span>
						</a>
					</li>
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-regular fa-file"></i>
							<span class="nav-text" data-i18n="Portfolio Management">Portfolio Management </span>
						</a>
					</li>
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-regular fa-folder"></i>
							<span class="nav-text" data-i18n="Others">Others </span>
						</a>
					</li>
					@if ($canViewAttendanceMenu || $canViewAdminAttendanceMenu || $canViewTimesheetReportingMenu || $canViewMeetingMenu || $canViewOrganizationMenu || $canViewAuthorizationMenu || $canViewEmployeeDatabaseMenu || $canViewTalentAcquisitionMenu)
					<div class="copyright mt-1">
						<p class="mb-1"><strong>HR Management</strong> </p>
					</div>
					@endif
					@if ($canViewAttendanceMenu)
					<li class="{{ $isAttendanceMenu ? 'mm-active' : '' }}">
						<a class="{{ $isAttendanceMenu ? 'active' : '' }}" href="{{ route('attendance') }}" aria-expanded="{{ $isAttendanceMenu ? 'true' : 'false' }}">
							<i class="fa-regular fa-clock"></i>
							<span class="nav-text" data-i18n="Time & Attendance">Time & Attendance </span>
						</a>
					</li>
					@endif
					@if ($canViewAdminAttendanceMenu)
					<li class="{{ $isAdminAttendanceMenu ? 'mm-active' : '' }}">
						<a class="{{ $isAdminAttendanceMenu ? 'active' : '' }}" href="{{ route('admin-attendance.overview') }}" aria-expanded="{{ $isAdminAttendanceMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-user-clock"></i>
							<span class="nav-text" data-i18n="Admin Attendance">Admin Attendance </span>
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
					@if ($canViewMeetingMenu)
					<li class="{{ $isMeetingMenu ? 'mm-active' : '' }}">
						<a class="{{ $isMeetingMenu ? 'active' : '' }}" href="{{ route('agenda') }}" aria-expanded="{{ $isMeetingMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-users-viewfinder"></i>
							<span class="nav-text" data-i18n="Meeting">Meeting </span>
						</a>
					</li>
					@endif
					@if ($canViewOrganizationMenu)
					<li class="{{ $isOrganizationMenu ? 'mm-active' : '' }}">
						<a class="{{ $isOrganizationMenu ? 'active' : '' }}" href="{{ route('employee_data') }}" aria-expanded="{{ $isOrganizationMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-diagram-project"></i>
							<span class="nav-text" data-i18n="Organization">Organization </span>
						</a>
					</li>
					@endif
					@if ($canViewAuthorizationMenu)
					<li class="{{ $isAuthorizationMenu ? 'mm-active' : '' }}">
						<a class="{{ $isAuthorizationMenu ? 'active' : '' }}" href="{{ route('authorization') }}" aria-expanded="{{ $isAuthorizationMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-user-shield"></i>
							<span class="nav-text" data-i18n="Authorization">Authorization </span>
						</a>
					</li>
					@endif
					@if ($canViewEmployeeDatabaseMenu)
					<li class="{{ $isEmployeeDatabaseMenu ? 'mm-active' : '' }}">
						<a class="has-arrow ai-icon {{ $isEmployeeDatabaseMenu ? 'active' : '' }}" href="javascript:void(0)" aria-expanded="{{ $isEmployeeDatabaseMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-users"></i>
							<span class="nav-text" data-i18n="Employee Database">Employee Database</span>
						</a>
						<ul aria-expanded="{{ $isEmployeeDatabaseMenu ? 'true' : 'false' }}">
							<li><a href="{{ route('employee_data') }}" data-i18n="Employee Database">Employee Database</a></li>
							<li><a href="{{ route('employee_data.authorization') }}" data-i18n="Employee Lifecycle">Employee Lifecycle</a></li>
						</ul>
					</li>
					@endif
					@if ($canViewTalentAcquisitionMenu)
					<li class="{{ $isTalentAcquisitionMenu ? 'mm-active' : '' }}">
						<a class="has-arrow ai-icon {{ $isTalentAcquisitionMenu ? 'active' : '' }}" href="javascript:void(0)" aria-expanded="{{ $isTalentAcquisitionMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-users"></i>
							<span class="nav-text" data-i18n="Talent Acquisition">Talent Acquisition</span>
						</a>
						<ul aria-expanded="{{ $isTalentAcquisitionMenu ? 'true' : 'false' }}">
							<li><a href="{{ route('applicant.job_vacancies') }}" data-i18n="Job Vacancy">Job Vacancy</a></li>
							<li><a href="{{ route('applicant') }}" data-i18n="Job Application">Job Application</a></li>
						</ul>
					</li>
					@endif
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-solid fa-chart-bar"></i>
							<span class="nav-text" data-i18n="Performance">Performance </span>
						</a>
					</li>
					@if ($canViewPayrollMenu || $canViewEmployeeServicesMenu)
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Finance Management</strong> </p>
					</div>
					@endif
					@if ($canViewPayrollMenu)
					<li>
						<a class="has-arrow ai-icon" href="javascript:void(0)" aria-expanded="false">
							<i class="fa-solid fa-receipt"></i>
							<span class="nav-text" data-i18n="Payroll">Payroll</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('error_code') }}" data-i18n="Salary Structure">Salary Structure</a></li>
							<li><a href="{{ route('error_code') }}" data-i18n="Taxation">Taxation</a></li>
							<li><a href="{{ route('error_code') }}" data-i18n="Insurance">Insurance</a></li>
							<li><a href="{{ route('error_code') }}" data-i18n="Deduction">Deduction</a></li>
							<li><a href="{{ route('error_code') }}" data-i18n="Payroll Run">Payroll Run</a></li>
						</ul>
					</li>
					@endif
					@if ($canViewEmployeeServicesMenu)
					<li>
						<a class="has-arrow ai-icon" href="javascript:void(0)" aria-expanded="false">
							<i class="fa-solid fa-hand-holding-dollar"></i>
							<span class="nav-text" data-i18n="Employee Services">Employee Services</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('error_code') }}" data-i18n="Reimbursement">Reimbursement</a></li>
							<li><a href="{{ route('error_code') }}" data-i18n="Cash Advance">Cash Advance</a></li>
							<li><a href="{{ route('error_code') }}" data-i18n="Employee Loan">Employee Loan</a></li>
						</ul>
					</li>
					@endif
                </ul>
			</div>
        </div>
        <!-- End - Sidebar -->
