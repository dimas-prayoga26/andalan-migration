@php
    $headerProfileUrl = \Illuminate\Support\Facades\Route::has('profile')
        ? route('profile')
        : url('/profile');
@endphp
<!-- Start - Header -->
        <header class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                            <div class="dashboard_bar">
								@yield('navbarTitle')
                            </div>
                        </div>
                        <ul class="navbar-nav header-right">
							<li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link bell dz-theme-mode"  aria-label="dz-theme-mode">
									<i id="icon-light" class="fas fa-sun"></i>
                                    <i id="icon-dark" class="fas fa-moon"></i>
                                </a>
							</li>
							<li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link  ai-icon" href="javascript:void(0)" aria-label="bell" role="button" data-bs-toggle="dropdown">
                                    <svg width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M22.75 15.8385V13.0463C22.7471 10.8855 21.9385 8.80353 20.4821 7.20735C19.0258 5.61116 17.0264 4.61555 14.875 4.41516V2.625C14.875 2.39294 14.7828 2.17038 14.6187 2.00628C14.4546 1.84219 14.2321 1.75 14 1.75C13.7679 1.75 13.5454 1.84219 13.3813 2.00628C13.2172 2.17038 13.125 2.39294 13.125 2.625V4.41534C10.9736 4.61572 8.97429 5.61131 7.51794 7.20746C6.06159 8.80361 5.25291 10.8855 5.25 13.0463V15.8383C4.26257 16.0412 3.37529 16.5784 2.73774 17.3593C2.10019 18.1401 1.75134 19.1169 1.75 20.125C1.75076 20.821 2.02757 21.4882 2.51969 21.9803C3.01181 22.4724 3.67904 22.7492 4.375 22.75H9.71346C9.91521 23.738 10.452 24.6259 11.2331 25.2636C12.0142 25.9013 12.9916 26.2497 14 26.2497C15.0084 26.2497 15.9858 25.9013 16.7669 25.2636C17.548 24.6259 18.0848 23.738 18.2865 22.75H23.625C24.321 22.7492 24.9882 22.4724 25.4803 21.9803C25.9724 21.4882 26.2492 20.821 26.25 20.125C26.2486 19.117 25.8998 18.1402 25.2622 17.3594C24.6247 16.5786 23.7374 16.0414 22.75 15.8385ZM7 13.0463C7.00232 11.2113 7.73226 9.45223 9.02974 8.15474C10.3272 6.85726 12.0863 6.12732 13.9212 6.125H14.0788C15.9137 6.12732 17.6728 6.85726 18.9703 8.15474C20.2677 9.45223 20.9977 11.2113 21 13.0463V15.75H7V13.0463ZM14 24.5C13.4589 24.4983 12.9316 24.3292 12.4905 24.0159C12.0493 23.7026 11.716 23.2604 11.5363 22.75H16.4637C16.284 23.2604 15.9507 23.7026 15.5095 24.0159C15.0684 24.3292 14.5411 24.4983 14 24.5ZM23.625 21H4.375C4.14298 20.9999 3.9205 20.9076 3.75644 20.7436C3.59237 20.5795 3.50014 20.357 3.5 20.125C3.50076 19.429 3.77757 18.7618 4.26969 18.2697C4.76181 17.7776 5.42904 17.5008 6.125 17.5H21.875C22.571 17.5008 23.2382 17.7776 23.7303 18.2697C24.2224 18.7618 24.4992 19.429 24.5 20.125C24.4999 20.357 24.4076 20.5795 24.2436 20.7436C24.0795 20.9076 23.857 20.9999 23.625 21Z" fill="var(--bs-primary)"/>
									</svg>
									<div class="pulse-css"></div>
                                </a>
                                 <div class="dropdown-menu dropdown-menu-end py-0">
									<div class="dz-scroll p-2" style="height: 380px;">
										<div class="d-flex align-items-center p-2 bg-action-light rounded">
											<div class="d-inline-block">
												<img src="images/avatar/small/avatar1.webp" alt="" class="rounded-circle avatar avatar-sm">
											</div>
											<div class="clearfix ms-2">
												<h6 class="fs-13 mb-0 fw-semibold">Dr sultads Send you Photo</h6>
												<small>29 July 2020 - 02:26 PM</small>
											</div>
										</div>
										<div class="d-flex align-items-center p-2 bg-action-light rounded">
											<div class="d-inline-block">
												<div class="avatar avatar-sm avatar-success rounded-circle">KG</div>
											</div>
											<div class="clearfix ms-2">
												<h6 class="fs-13 mb-0 fw-semibold">Resport created successfully</h6>
												<small>29 July 2020 - 02:26 PM</small>
											</div>
										</div>
										<div class="d-flex align-items-center p-2 bg-action-light rounded">
											<div class="d-inline-block">
												<div class="avatar avatar-sm avatar-primary rounded-circle"><i class="fa fa-home"></i></div>
											</div>
											<div class="clearfix ms-2">
												<h6 class="fs-13 mb-0 fw-semibold">Reminder : Treatment Time!</h6>
												<small>29 July 2020 - 02:26 PM</small>
											</div>
										</div>
										<div class="d-flex align-items-center p-2 bg-action-light rounded">
											<div class="d-inline-block">
												<img src="images/avatar/small/avatar2.webp" alt="" class="rounded-circle avatar avatar-sm">
											</div>
											<div class="clearfix ms-2">
												<h6 class="fs-13 mb-0 fw-semibold">Resport created successfully</h6>
												<small>29 July 2020 - 02:26 PM</small>
											</div>
										</div>
										<div class="d-flex align-items-center p-2 bg-action-light rounded">
											<div class="d-inline-block">
												<img src="images/avatar/small/avatar3.webp" alt="" class="rounded-circle avatar avatar-sm">
											</div>
											<div class="clearfix ms-2">
												<h6 class="fs-13 mb-0 fw-semibold">Dr sultads Send you Photo</h6>
												<small>29 July 2020 - 02:26 PM</small>
											</div>
										</div>
										<div class="d-flex align-items-center p-2 bg-action-light rounded">
											<div class="d-inline-block">
												<div class="avatar avatar-sm avatar-success rounded-circle">KG</div>
											</div>
											<div class="clearfix ms-2">
												<h6 class="fs-13 mb-0 fw-semibold">Resport created successfully</h6>
												<small>29 July 2020 - 02:26 PM</small>
											</div>
										</div>
										<div class="d-flex align-items-center p-2 bg-action-light rounded">
											<div class="d-inline-block">
												<div class="avatar avatar-sm avatar-primary rounded-circle"><i class="fa fa-home"></i></div>
											</div>
											<div class="clearfix ms-2">
												<h6 class="fs-13 mb-0 fw-semibold">Reminder : Treatment Time!</h6>
												<small>29 July 2020 - 02:26 PM</small>
											</div>
										</div>
										<div class="d-flex align-items-center p-2 bg-action-light rounded">
											<div class="d-inline-block">
												<img src="images/avatar/small/avatar4.webp" alt="" class="rounded-circle avatar avatar-sm">
											</div>
											<div class="clearfix ms-2">
												<h6 class="fs-13 mb-0 fw-semibold">Resport created successfully</h6>
												<small>29 July 2020 - 02:26 PM</small>
											</div>
										</div>
									</div>
									<a class="d-block text-center p-3 border-top" >See all notifications <i class="fa fa-arrow-right"></i></a>
								</div>
                            </li>
                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
                                    <img src="{{ $headerUserAvatarUrl }}" width="20" alt="{{ $headerUserName }}">
									<div class="header-info">
										<span class="text-black fw-semibold"><p class="mb-1">{{ $headerUserName }}</p></span>
										<p class="fs-12 mb-0">{{ $headerUserPositionLabel }}</p>
									</div>
                                </a>
								<ul class="dropdown-menu dropdown-menu-end">
									<li>
										<div class="py-2 d-flex px-3">
											<img src="{{ $headerUserAvatarUrl }}" class="avatar avatar-sm rounded-circle" alt="{{ $headerUserName }}">
											<div class="ms-2">
												<h6 class="mb-0">{{ $headerUserName }}</h6>
												<small>{{ $headerUserPositionLabel }}</small>
											</div>
										</div>
									</li>
									<li><hr class="dropdown-divider"></li>
									<li>
										<a class="dropdown-item" href="{{ $headerProfileUrl }}">
											<svg  width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9848 15.3462C8.11714 15.3462 4.81429 15.931 4.81429 18.2729C4.81429 20.6148 8.09619 21.2205 11.9848 21.2205C15.8524 21.2205 19.1543 20.6348 19.1543 18.2938C19.1543 15.9529 15.8733 15.3462 11.9848 15.3462Z" stroke="var(--bs-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9848 12.0059C14.5229 12.0059 16.58 9.94779 16.58 7.40969C16.58 4.8716 14.5229 2.81445 11.9848 2.81445C9.44667 2.81445 7.38857 4.8716 7.38857 7.40969C7.38 9.93922 9.42381 11.9973 11.9524 12.0059H11.9848Z" stroke="var(--bs-primary)" stroke-width="1.42857" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<span class="ms-2">Edit Profile</span>
										</a>
									</li>
									<li>
										<a class="dropdown-item" href="app-profile.html">
											<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pie-chart">
												<path stroke="var(--bs-primary)" d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
												<path stroke="var(--bs-primary)" d="M22 12A10 10 0 0 0 12 2v10z"></path>
											</svg>
											<span class="ms-2">My Project</span>
											<span class="badge badge-sm badge-primary light rounded-circle float-end">4</span>
										</a>
									</li>
									<li><hr class="dropdown-divider"></li>
									<li>
										<form action="{{ route('logout') }}" method="POST" class="m-0">
											@csrf
											<button type="submit" class="dropdown-item">
												<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--bs-danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
													<path stroke="var(--bs-danger)" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
													<polyline stroke="var(--bs-danger)" points="16 17 21 12 16 7"></polyline>
													<line x1="21" y1="12" x2="9" y2="12"></line>
												</svg>
												<span class="ms-2 text-danger">Logout </span>
											</button>
										</form>
									</li>
								</ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>
        <!-- End - Header -->
