		<!-- Start - Sidebar -->
          <div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
                    <li class="nav-label first">Main</li>
                    <li>
						<a href="{{ url('/') }}" class="ai-icon" aria-expanded="false">
							<i class="bi bi-house-door"></i>
							<span class="nav-text">Dashboard</span>
						</a>
					</li>
                    <li class="{{ request()->routeIs('activity-schadule*') || request()->is('activity-schadule*') ? 'mm-active' : '' }}">
						<a href="{{ route('activity-schadule') }}" class="ai-icon {{ request()->routeIs('activity-schadule*') || request()->is('activity-schadule*') ? 'active' : '' }}" aria-expanded="{{ request()->routeIs('activity-schadule*') || request()->is('activity-schadule*') ? 'true' : 'false' }}">
							<i class="bi bi-calendar-event"></i>
							<span class="nav-text">Agenda Kegiatan</span>
						</a>
					</li>

                    <li class="nav-label">Data Siap</li>
                    <li>
						<a href="{{ route('absensi') }}" class="ai-icon {{ request()->routeIs('absensi') ? 'active' : '' }}" aria-expanded="{{ request()->routeIs('absensi') ? 'true' : 'false' }}">
							<i class="bi bi-archive"></i>
							<span class="nav-text">Data Absensi</span>
						</a>
					</li>
                    <li class="{{ request()->routeIs('project_management', 'project_management.detail') || request()->is('project-management*') ? 'mm-active' : '' }}">
						<a href="{{ route('project_management') }}" class="ai-icon {{ request()->routeIs('project_management', 'project_management.detail') || request()->is('project-management*') ? 'active' : '' }}" aria-expanded="{{ request()->routeIs('project_management', 'project_management.detail') || request()->is('project-management*') ? 'true' : 'false' }}">
							<i class="bi bi-file-earmark-plus"></i>
							<span class="nav-text">Laporan Pekerjaan</span>
						</a>
					</li>
                    <li class="{{ request()->routeIs('applicant', 'applicant.job_vacancies') || request()->is('applicant*') ? 'mm-active' : '' }}">
						<a href="{{ route('applicant') }}" class="ai-icon {{ request()->routeIs('applicant', 'applicant.job_vacancies') || request()->is('applicant*') ? 'active' : '' }}" aria-expanded="{{ request()->routeIs('applicant', 'applicant.job_vacancies') || request()->is('applicant*') ? 'true' : 'false' }}">
							<i class="bi bi-layout-text-sidebar"></i>
							<span class="nav-text">Data Pelamar</span>
						</a>
					</li>
                    <li class="{{ request()->routeIs('employee_data', 'employee_data.authorization') || request()->is('employee-data*') ? 'mm-active' : '' }}">
						<a href="{{ route('employee_data') }}" class="ai-icon {{ request()->routeIs('employee_data', 'employee_data.authorization') || request()->is('employee-data*') ? 'active' : '' }}" aria-expanded="{{ request()->routeIs('employee_data', 'employee_data.authorization') || request()->is('employee-data*') ? 'true' : 'false' }}">
							<i class="bi bi-people"></i>
							<span class="nav-text">Data Karyawan</span>
						</a>
					</li>

                    <li class="nav-label">Website Features</li>
                    <li>
						<a href="{{ route('error_code') }}" class="ai-icon" aria-expanded="false">
							<i class="bi bi-type-bold"></i>
							<span class="nav-text">Blog Management</span>
						</a>
					</li>
                    <li class="nav-label">Administration Features</li>
                    <li>
						<a href="{{ route('error_code') }}" class="ai-icon" aria-expanded="false">
							<i class="bi bi-wallet2"></i>
							<span class="nav-text">Finance Management</span>
						</a>
					</li>
                    <li>
						<a href="{{ route('error_code') }}" class="ai-icon" aria-expanded="false">
							<i class="bi bi-window-sidebar"></i>
							<span class="nav-text">Administration Management</span>
						</a>
					</li>

                    <li class="nav-label">Meeting</li>
                    <li>
						<a href="{{ route('error_code') }}" class="ai-icon" aria-expanded="false">
							<i class="bi bi-camera-video"></i>
							<span class="nav-text">Zoom Meeting</span>
						</a>
					</li>

                    <li class="nav-label">Resource</li>
                    <li>
						<a href="{{ route('error_code') }}" class="ai-icon" aria-expanded="false">
							<i class="bi bi-list-task"></i>
							<span class="nav-text">Options</span>
						</a>
					</li>

                    <li class="nav-label">My Account</li>
                    <li>
						<a href="{{ route('error_code') }}" class="ai-icon" aria-expanded="false">
							<i class="bi bi-person"></i>
							<span class="nav-text">My Profile</span>
						</a>
					</li>

                    <li class="nav-label">Miscellaneous</li>
                    <li>
						<a class="has-arrow ai-icon" href="{{ route('error_code') }}" aria-expanded="false">
							<i class="bi bi-command"></i>
							<span class="nav-text">Other Menu</span>
						</a>
                        <ul aria-expanded="false">
                            <li><a href="{{ route('error_code') }}">Information</a></li>
                        </ul>
						<ul aria-expanded="false">
                            <li><a href="{{ route('error_code') }}">Setting</a></li>
                        </ul>
						<ul aria-expanded="false">
                            <li><a href="{{ route('error_code') }}">Help Center</a></li>
                        </ul>
						<ul aria-expanded="false">
                            <li><a href="{{ route('error_code') }}">Privacy Policy</a></li>
                        </ul>
						<ul aria-expanded="false">
                            <li><a href="{{ route('error_code') }}">Sitemap</a></li>
                        </ul>
                    </li>
                </ul>
			</div>
        </div>
        <!-- End - Sidebar -->
