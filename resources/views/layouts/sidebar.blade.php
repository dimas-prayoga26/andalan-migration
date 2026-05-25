		<!-- Start - Sidebar -->
          <div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
					@php
						$isDashboardMenu = request()->routeIs('dashboard');
						$isCalendarMenu = request()->routeIs('activity-schadule*');
						$isAttendanceMenu = request()->routeIs('absensi') || request()->is('absensi');
						$isReportingMenu = request()->routeIs('project_management', 'project_management.detail') || request()->is('project-management*');
						$isMeetingMenu = request()->routeIs('agenda');
						$isOrganizationMenu = request()->routeIs('employee_data*') || request()->is('employee-data*');
						$isEmployeeDatabaseMenu = request()->routeIs('employee_data*') || request()->is('employee-data*');
						$isTalentAcquisitionMenu = request()->routeIs('applicant*') || request()->is('applicant*');
					@endphp
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Main</strong> </p>
					</div>
                    <li class="{{ $isDashboardMenu ? 'mm-active' : '' }}">
						<a class="{{ $isDashboardMenu ? 'mm-active' : '' }}" href="{{ route('dashboard') }}">
							<i class="fa-solid fa-gauge"></i>
							<span class="nav-text" data-i18n="Dashboard">Dashboard</span>
						</a>
                    </li>
					<li class="{{ $isCalendarMenu ? 'mm-active' : '' }}">
						<a class="{{ $isCalendarMenu ? 'active' : '' }}" href="{{ route('activity-schadule') }}">
							<i class="fa-regular fa-calendar-days"></i>
							<span class="nav-text" data-i18n="Google Calendar">Google Calendar </span>
						</a>
					</li>
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Siap</strong> </p>
					</div>
					<li class="{{ $isAttendanceMenu ? 'mm-active' : '' }}">
						<a class="{{ $isAttendanceMenu ? 'active' : '' }}" href="{{ route('absensi') }}" aria-expanded="{{ $isAttendanceMenu ? 'true' : 'false' }}">
							<i class="fa-regular fa-clock"></i>
							<span class="nav-text" data-i18n="Attendance">Attendance </span>
						</a>
					</li>
					<li class="{{ $isReportingMenu ? 'mm-active' : '' }}">
						<a class="{{ $isReportingMenu ? 'active' : '' }}" href="{{ route('project_management') }}" aria-expanded="{{ $isReportingMenu ? 'true' : 'false' }}">
							<i class="fa-regular fa-file-lines"></i>
							<span class="nav-text" data-i18n="Timesheet & Reporting">Timesheet & Reporting </span>
						</a>
					</li>
					<li class="{{ $isMeetingMenu ? 'mm-active' : '' }}">
						<a class="{{ $isMeetingMenu ? 'active' : '' }}" href="{{ route('agenda') }}" aria-expanded="{{ $isMeetingMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-users-viewfinder"></i>
							<span class="nav-text" data-i18n="Zoom Meeting">Zoom Meeting </span>
						</a>
					</li>
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
					<div class="copyright mt-1">
						<p class="mb-1"><strong>HR Management</strong> </p>
					</div>
					<li class="{{ $isAttendanceMenu ? 'mm-active' : '' }}">
						<a class="{{ $isAttendanceMenu ? 'active' : '' }}" href="{{ route('absensi') }}" aria-expanded="{{ $isAttendanceMenu ? 'true' : 'false' }}">
							<i class="fa-regular fa-clock"></i>
							<span class="nav-text" data-i18n="Time & Attendance">Time & Attendance </span>
						</a>
					</li>
					<li class="{{ $isReportingMenu ? 'mm-active' : '' }}">
						<a class="{{ $isReportingMenu ? 'active' : '' }}" href="{{ route('project_management') }}" aria-expanded="{{ $isReportingMenu ? 'true' : 'false' }}">
							<i class="fa-regular fa-file-lines"></i>
							<span class="nav-text" data-i18n="Timesheet & Reporting">Timesheet & Reporting </span>
						</a>
					</li>
					<li class="{{ $isMeetingMenu ? 'mm-active' : '' }}">
						<a class="{{ $isMeetingMenu ? 'active' : '' }}" href="{{ route('agenda') }}" aria-expanded="{{ $isMeetingMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-users-viewfinder"></i>
							<span class="nav-text" data-i18n="Meeting">Meeting </span>
						</a>
					</li>
					<li class="{{ $isOrganizationMenu ? 'mm-active' : '' }}">
						<a class="{{ $isOrganizationMenu ? 'active' : '' }}" href="{{ route('employee_data') }}" aria-expanded="{{ $isOrganizationMenu ? 'true' : 'false' }}">
							<i class="fa-solid fa-diagram-project"></i>
							<span class="nav-text" data-i18n="Organization">Organization </span>
						</a>
					</li>
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
					<li>
						<a class="" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="fa-solid fa-chart-bar"></i>
							<span class="nav-text" data-i18n="Performance">Performance </span>
						</a>
					</li>
					<div class="copyright mt-1">
						<p class="mb-1"><strong>Finance Management</strong> </p>
					</div>
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
                </ul>
			</div>
        </div>
        <!-- End - Sidebar -->
