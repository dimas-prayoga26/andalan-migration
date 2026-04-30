<!DOCTYPE html>
<html lang="en">

<head>
    
	<!-- Title -->
	<title>Gymove  - Fitness Bootstrap Admin Dashboard Template</title>

	<!-- Meta -->
    <base href="{{ asset('assets') }}/">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="DexignZone">
	<meta name="robots" content="">

	<meta name="keywords" content="admin, admin dashboard, admin template, bootstrap, bootstrap 5, bootstrap 5 admin template, fitness, fitness admin, modern, responsive admin dashboard, sales dashboard, sass, ui kit, web app">
	<meta name="description" content="Discover Gymove, the ultimate fitness solution that is designed to help you achieve a healthier lifestyle with its cutting-edge features and personalized programs. Gymove is a fully mobile-responsive admin dashboard template that provides the perfect blend of exercise, nutrition, and motivation. Begin your fitness journey today with Gymove and visit DexignZone for more information.">

	<meta property="og:title" content="Gymove  - Fitness Bootstrap Admin Dashboard Template">
	<meta property="og:description" content="Discover Gymove, the ultimate fitness solution that is designed to help you achieve a healthier lifestyle with its cutting-edge features and personalized programs. Gymove is a fully mobile-responsive admin dashboard template that provides the perfect blend of exercise, nutrition, and motivation. Begin your fitness journey today with Gymove and visit DexignZone for more information.">
	<meta property="og:image" content="https://gymove.dexignzone.com/xhtml/social-image.avif">
	<meta name="format-detection" content="telephone=no">

	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Favicon icon -->
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.avif">
    
	<!-- Start - All Required css -->
	<link href="vendor/lightgallery/css/lightgallery.min.css" rel="stylesheet">
   	<!-- End - All Required css -->
	
	<!-- Start - Basic CSS -->
    <link href="vendor/metismenu/dist/metisMenu.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <!-- End - Basic CSS -->
    
	<!-- Start - Switcher CSS -->
	<link class="main-switcher" href="css/switcher.css" rel="stylesheet">
	<!-- End - Switcher CSS -->

	<!-- Start - Style Css -->
	<link class="main-plugins" href="css/plugins.css" rel="stylesheet">
	<link class="main-css" href="css/style.css" rel="stylesheet">	
	<!-- End - Style Css -->
    
</head>

<body>

	<!-- Start - Preloader-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!-- End - Preloader -->
    
   <!-- Start - Main Wrapper -->
    <div id="main-wrapper">

             <!-- Start - Nav header -->
         <div class="nav-header">
            <a href="index.html" class="brand-logo" aria-label="Gymove">
                <svg class="logo-abbr" width="53" height="53" viewBox="0 0 53 53" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M0.730591 20.2078C0.730591 9.16207 9.6849 0.207764 20.7306 0.207764H32.7306C43.7763 0.207764 52.7306 9.16207 52.7306 20.2078V44.2078C52.7306 48.626 49.1489 52.2078 44.7306 52.2078H20.7306C9.68489 52.2078 0.730591 43.2535 0.730591 32.2078V20.2078Z" fill="url(#paint0_linear_42_4)"/>
					<path d="M21.2539 18.2042C23.2694 18.2042 24.9032 16.638 24.9032 14.706C24.9032 12.7741 23.2694 11.2078 21.2539 11.2078C19.2388 11.2078 17.6051 12.7741 17.6051 14.706C17.6051 16.638 19.2388 18.2042 21.2539 18.2042Z" fill="white"/>
					<path d="M22.4456 22.7752C23.6846 24.8155 24.8612 26.8917 25.9913 28.9903C24.3209 29.809 22.3444 31.2439 21.3368 32.0273C20.3291 32.8107 18.4505 36.815 17.4845 38.7296C16.4468 40.7857 19.7855 42.2771 20.8194 40.2297C21.6715 38.5423 23.1065 35.1838 23.9418 34.5246C24.7771 33.8653 27.5191 32.005 29.611 31.3063C29.714 31.2718 29.8076 31.2308 29.8965 31.1853C32.4572 34.301 37.4727 35.7728 41.7506 33.3761C44.0047 32.1131 41.9658 28.9991 39.7051 30.2659C36.5717 32.0227 33.3106 29.7764 31.834 26.9639C30.3572 24.1513 29.3109 22.1716 28.1138 20.1798C30.4515 19.465 32.5888 20.1179 34.2697 22.3747C35.6366 24.2108 38.8341 22.4501 37.4515 20.5923C34.1982 16.2245 29.3473 15.1405 24.4906 17.9047C24.2888 18.0196 23.377 18.5013 23.1716 18.6182C20.1526 20.3365 17.3469 20.0107 15.2518 17.197C13.8838 15.3608 10.687 17.1215 12.0706 18.979C14.7437 22.5689 18.4968 23.9351 22.4456 22.7752Z" fill="white"/>
					<defs>
					<linearGradient id="paint0_linear_42_4" x1="26.7306" y1="0.207765" x2="55.2306" y2="56.2078" gradientUnits="userSpaceOnUse">
					<stop stop-color="#6A10C5"/>
					<stop offset="1" stop-color="#0B2A97"/>
					</linearGradient>
					</defs>
				</svg>
				<svg class="brand-title view-light" width="126" height="28" viewBox="0 0 126 28" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path class="svg-logo-path" d="M10.6786 0.411763C5.02259 0.411763 0.710591 4.55576 0.710591 10.4078C0.710591 16.2598 5.02259 20.3758 10.7066 20.3758C15.9146 20.3758 19.4706 16.7358 20.2546 12.7598V9.34376H9.67059V12.3398H16.3906C15.8026 15.1118 13.8706 16.8478 10.8466 16.8478C7.20659 16.8478 4.74259 14.3838 4.74259 10.4078C4.74259 6.51576 7.17859 4.02376 10.6786 4.02376C12.7506 4.02376 14.3746 4.89176 15.2706 6.54376H19.7786C18.5186 2.62376 15.0466 0.411763 10.6786 0.411763ZM29.7628 15.5318L25.7588 4.69576H21.3628L27.5788 19.8158L24.2188 27.5438H28.3908L37.9948 4.69576H33.8228L29.7628 15.5318Z" fill="#262626"/>
					<path class="svg-logo-path" d="M61.8079 20.2078H65.7279V11.1078C65.7279 6.82376 63.0959 4.47176 59.3719 4.47176C57.1039 4.47176 55.0599 5.67576 54.0519 7.43976C52.9879 5.50776 50.9999 4.47176 48.5919 4.47176C46.6599 4.47176 45.0079 5.28376 44.0279 6.57176V4.69576H40.1079V20.2078H44.0279V11.6398C44.0279 9.17576 45.3999 7.88776 47.5279 7.88776C49.5999 7.88776 50.9719 9.17576 50.9719 11.6398V20.2078H54.8919V11.6398C54.8919 9.17576 56.2639 7.88776 58.3639 7.88776C60.4359 7.88776 61.8079 9.17576 61.8079 11.6398V20.2078ZM84.4277 12.4518C84.4277 7.55176 80.9277 4.44376 76.4477 4.44376C71.9677 4.44376 68.4677 7.55176 68.4677 12.4518C68.4677 17.3238 71.8557 20.4598 76.3357 20.4598C80.8437 20.4598 84.4277 17.3238 84.4277 12.4518ZM72.4437 12.4518C72.4437 9.34376 74.3197 7.85976 76.3917 7.85976C78.4637 7.85976 80.3957 9.34376 80.3957 12.4518C80.3957 15.5318 78.4077 17.0438 76.3357 17.0438C74.2357 17.0438 72.4437 15.5318 72.4437 12.4518ZM85.6512 4.69576L91.3632 20.2078H96.1232L101.863 4.69576H97.6912L93.7712 16.5958L89.8512 4.69576H85.6512ZM110.729 7.66376C112.745 7.66376 114.369 8.95176 114.425 10.9958H107.061C107.369 8.86776 108.853 7.66376 110.729 7.66376ZM118.093 15.3358H113.865C113.361 16.3718 112.437 17.2118 110.757 17.2118C108.797 17.2118 107.229 15.9238 107.033 13.6278H118.373C118.457 13.1238 118.485 12.6198 118.485 12.1158C118.485 7.49576 115.321 4.44376 110.841 4.44376C106.249 4.44376 103.057 7.55176 103.057 12.4518C103.057 17.3238 106.333 20.4598 110.841 20.4598C114.677 20.4598 117.253 18.1918 118.093 15.3358ZM125.462 18.1078C125.462 16.8198 124.454 15.8118 123.082 15.8118C121.654 15.8118 120.646 16.8198 120.646 18.1078C120.646 19.3958 121.654 20.4038 123.082 20.4038C124.454 20.4038 125.462 19.3958 125.462 18.1078Z" fill="#0B2A97"/>
				</svg>
				<svg class="brand-title view-dark" width="126" height="28" viewBox="0 0 126 28" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M10.6786 0.411763C5.02259 0.411763 0.710591 4.55576 0.710591 10.4078C0.710591 16.2598 5.02259 20.3758 10.7066 20.3758C15.9146 20.3758 19.4706 16.7358 20.2546 12.7598V9.34376H9.67059V12.3398H16.3906C15.8026 15.1118 13.8706 16.8478 10.8466 16.8478C7.20659 16.8478 4.74259 14.3838 4.74259 10.4078C4.74259 6.51576 7.17859 4.02376 10.6786 4.02376C12.7506 4.02376 14.3746 4.89176 15.2706 6.54376H19.7786C18.5186 2.62376 15.0466 0.411763 10.6786 0.411763ZM29.7628 15.5318L25.7588 4.69576H21.3628L27.5788 19.8158L24.2188 27.5438H28.3908L37.9948 4.69576H33.8228L29.7628 15.5318Z" fill="#fff"/>
					<path d="M61.8079 20.2078H65.7279V11.1078C65.7279 6.82376 63.0959 4.47176 59.3719 4.47176C57.1039 4.47176 55.0599 5.67576 54.0519 7.43976C52.9879 5.50776 50.9999 4.47176 48.5919 4.47176C46.6599 4.47176 45.0079 5.28376 44.0279 6.57176V4.69576H40.1079V20.2078H44.0279V11.6398C44.0279 9.17576 45.3999 7.88776 47.5279 7.88776C49.5999 7.88776 50.9719 9.17576 50.9719 11.6398V20.2078H54.8919V11.6398C54.8919 9.17576 56.2639 7.88776 58.3639 7.88776C60.4359 7.88776 61.8079 9.17576 61.8079 11.6398V20.2078ZM84.4277 12.4518C84.4277 7.55176 80.9277 4.44376 76.4477 4.44376C71.9677 4.44376 68.4677 7.55176 68.4677 12.4518C68.4677 17.3238 71.8557 20.4598 76.3357 20.4598C80.8437 20.4598 84.4277 17.3238 84.4277 12.4518ZM72.4437 12.4518C72.4437 9.34376 74.3197 7.85976 76.3917 7.85976C78.4637 7.85976 80.3957 9.34376 80.3957 12.4518C80.3957 15.5318 78.4077 17.0438 76.3357 17.0438C74.2357 17.0438 72.4437 15.5318 72.4437 12.4518ZM85.6512 4.69576L91.3632 20.2078H96.1232L101.863 4.69576H97.6912L93.7712 16.5958L89.8512 4.69576H85.6512ZM110.729 7.66376C112.745 7.66376 114.369 8.95176 114.425 10.9958H107.061C107.369 8.86776 108.853 7.66376 110.729 7.66376ZM118.093 15.3358H113.865C113.361 16.3718 112.437 17.2118 110.757 17.2118C108.797 17.2118 107.229 15.9238 107.033 13.6278H118.373C118.457 13.1238 118.485 12.6198 118.485 12.1158C118.485 7.49576 115.321 4.44376 110.841 4.44376C106.249 4.44376 103.057 7.55176 103.057 12.4518C103.057 17.3238 106.333 20.4598 110.841 20.4598C114.677 20.4598 117.253 18.1918 118.093 15.3358ZM125.462 18.1078C125.462 16.8198 124.454 15.8118 123.082 15.8118C121.654 15.8118 120.646 16.8198 120.646 18.1078C120.646 19.3958 121.654 20.4038 123.082 20.4038C124.454 20.4038 125.462 19.3958 125.462 18.1078Z" fill="#fff"/>
				</svg>

            </a>

            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!-- End - Nav header -->
        
		<!-- Start - Sidebar Chat Box  -->
		<div class="chatbox">
			<div class="chatbox-close"></div>
			<div class="clearfix">
				<ul class="nav nav-underline">
					<li class="nav-item">
						<a class="nav-link" data-bs-toggle="tab" href="#notes">Notes</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-bs-toggle="tab" href="#alerts">Alerts</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" data-bs-toggle="tab" href="#chat">Chat</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade active show" id="chat">
						<div class="card mb-sm-3 mb-md-0 contacts_card dz-chat-user-box">
							<div class="card-header chat-list-header text-center">
								<a >
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<rect fill="var(--bs-heading-color)" x="4" y="11" width="16" height="2" rx="1"/>
											<rect fill="var(--bs-heading-color)" opacity="1.0" transform="translate(12.000000, 12.000000) rotate(-270.000000) translate(-12.000000, -12.000000) " x="4" y="11" width="16" height="2" rx="1"/>
										</g>
									</svg>
								</a>
								<div class="clearfix">
									<h6 class="mb-1">Chat List</h6>
									<p class="mb-0">Show All</p>
								</div>
								<a >
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<rect x="0" y="0" width="24" height="24"/>
											<circle fill="var(--bs-heading-color)" cx="5" cy="12" r="2"/>
											<circle fill="var(--bs-heading-color)" cx="12" cy="12" r="2"/>
											<circle fill="var(--bs-heading-color)" cx="19" cy="12" r="2"/>
										</g>
									</svg>
								</a>
							</div>
							<div class="card-body contacts_body p-0 dz-scroll  " id="DZ_W_Contacts_Body">
								<ul class="contacts">
									<li class="name-first-letter">A</li>
									<li class="active dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon"></span>
											</div>
											<div class="user_info">
												<span>Archie Parker</span>
												<p>Kalid is online</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar2.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>Alfie Mason</span>
												<p>Taherah left 7 mins ago</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar3.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon"></span>
											</div>
											<div class="user_info">
												<span>AharlieKane</span>
												<p>Sami is online</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar4.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>Athan Jacoby</span>
												<p>Nargis left 30 mins ago</p>
											</div>
										</div>
									</li>
									<li class="name-first-letter">B</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar5.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>Bashid Samim</span>
												<p>Rashid left 50 mins ago</p>
											</div>
										</div>
									</li>
									<li class="dz- -user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar6.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon"></span>
											</div>
											<div class="user_info">
												<span>Breddie Ronan</span>
												<p>Kalid is online</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar7.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>Ceorge Carson</span>
												<p>Taherah left 7 mins ago</p>
											</div>
										</div>
									</li>
									<li class="name-first-letter">D</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar8.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon"></span>
											</div>
											<div class="user_info">
												<span>Darry Parker</span>
												<p>Sami is online</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar9.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>Denry Hunter</span>
												<p>Nargis left 30 mins ago</p>
											</div>
										</div>
									</li>
									<li class="name-first-letter">J</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar10.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>Jack Ronan</span>
												<p>Rashid left 50 mins ago</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon"></span>
											</div>
											<div class="user_info">
												<span>Jacob Tucker</span>
												<p>Kalid is online</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar2.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>James Logan</span>
												<p>Taherah left 7 mins ago</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar3.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon"></span>
											</div>
											<div class="user_info">
												<span>Joshua Weston</span>
												<p>Sami is online</p>
											</div>
										</div>
									</li>
									<li class="name-first-letter">O</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar4.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>Oliver Acker</span>
												<p>Nargis left 30 mins ago</p>
											</div>
										</div>
									</li>
									<li class="dz-chat-user">
										<div class="d-flex bd-highlight">
											<div class="img_cont">
												<img src="images/avatar/small/avatar5.webp" class="rounded-circle user_img" alt="">
												<span class="online_icon offline"></span>
											</div>
											<div class="user_info">
												<span>Oscar Weston</span>
												<p>Rashid left 50 mins ago</p>
											</div>
										</div>
									</li>
								</ul>
							</div>
						</div>
						<div class="card chat dz-chat-history-box d-none">
							<div class="card-header chat-list-header text-center">
								<a class="dz-chat-history-back">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<polygon points="0 0 24 0 24 24 0 24"/>
											<rect fill="var(--bs-heading-color)" opacity="0.3" transform="translate(15.000000, 12.000000) scale(-1, 1) rotate(-90.000000) translate(-15.000000, -12.000000)" x="14" y="7" width="2" height="10" rx="1"/>
											<path d="M3.7071045,15.7071045 C3.3165802,16.0976288 2.68341522,16.0976288 2.29289093,15.7071045 C1.90236664,15.3165802 1.90236664,14.6834152 2.29289093,14.2928909 L8.29289093,8.29289093 C8.67146987,7.914312 9.28105631,7.90106637 9.67572234,8.26284357 L15.6757223,13.7628436 C16.0828413,14.136036 16.1103443,14.7686034 15.7371519,15.1757223 C15.3639594,15.5828413 14.7313921,15.6103443 14.3242731,15.2371519 L9.03007346,10.3841355 L3.7071045,15.7071045 Z" fill="var(--bs-heading-color)" fill-rule="nonzero" transform="translate(9.000001, 11.999997) scale(-1, -1) rotate(90.000000) translate(-9.000001, -11.999997) "/>
										</g>
									</svg>
								</a>
								<div class="clearfix">
									<h6 class="mb-1">Chat with Verla Morgano</h6>
									<p class="mb-0 text-success">Online</p>
								</div>							
								<div class="dropdown">
									<a role="button" data-bs-toggle="dropdown" aria-expanded="false" class="dz-chat-dropdown dz-chatbox-btn">
										<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
											<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
												<rect x="0" y="0" width="24" height="24"/>
												<circle fill="var(--bs-heading-color)" cx="5" cy="12" r="2"/>
												<circle fill="var(--bs-heading-color)" cx="12" cy="12" r="2"/>
												<circle fill="var(--bs-heading-color)" cx="19" cy="12" r="2"/>
											</g>
										</svg>
									</a>
									<ul class="dropdown-menu dropdown-menu-end">
										<li><a class="dropdown-item" ><i class="fa fa-user-circle text-primary me-1"></i> View profile</a></li>
										<li><a class="dropdown-item" ><i class="fa fa-users text-primary me-1"></i> Add to Friends</a></li>
										<li><a class="dropdown-item" ><i class="fa fa-plus text-primary me-1"></i> Add to group</a></li>
										<li><a class="dropdown-item" ><i class="fa fa-ban text-primary me-1"></i> Block</a></li>
									</ul>
								</div>
							</div>
							<div class="card-body msg_card_body dz-scroll" id="DZ_W_Contacts_Body3">
								<div class="d-flex justify-content-start mb-4">
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img_msg" alt="">
									</div>
									<div class="msg_cotainer">
										Hi, how are you samim?
										<span class="msg_time">8:40 AM, Today</span>
									</div>
								</div>
								<div class="d-flex justify-content-end mb-4">
									<div class="msg_cotainer_send">
										Hi Khalid i am good tnx how about you?
										<span class="msg_time_send">8:55 AM, Today</span>
									</div>
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar2.webp" class="rounded-circle user_img_msg" alt="">
									</div>
								</div>
								<div class="d-flex justify-content-start mb-4">
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img_msg" alt="">
									</div>
									<div class="msg_cotainer">
										I am good too, thank you for your chat template
										<span class="msg_time">9:00 AM, Today</span>
									</div>
								</div>
								<div class="d-flex justify-content-end mb-4">
									<div class="msg_cotainer_send">
										You are welcome
										<span class="msg_time_send">9:05 AM, Today</span>
									</div>
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar2.webp" class="rounded-circle user_img_msg" alt="">
									</div>
								</div>
								<div class="d-flex justify-content-start mb-4">
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img_msg" alt="">
									</div>
									<div class="msg_cotainer">
										I am looking for your next templates
										<span class="msg_time">9:07 AM, Today</span>
									</div>
								</div>
								<div class="d-flex justify-content-end mb-4">
									<div class="msg_cotainer_send">
										Ok, thank you have a good day
										<span class="msg_time_send">9:10 AM, Today</span>
									</div>
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar2.webp" class="rounded-circle user_img_msg" alt="">
									</div>
								</div>
								<div class="d-flex justify-content-start mb-4">
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img_msg" alt="">
									</div>
									<div class="msg_cotainer">
										Bye, see you
										<span class="msg_time">9:12 AM, Today</span>
									</div>
								</div>
								<div class="d-flex justify-content-start mb-4">
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img_msg" alt="">
									</div>
									<div class="msg_cotainer">
										Hi, how are you samim?
										<span class="msg_time">8:40 AM, Today</span>
									</div>
								</div>
								<div class="d-flex justify-content-end mb-4">
									<div class="msg_cotainer_send">
										Hi Khalid i am good tnx how about you?
										<span class="msg_time_send">8:55 AM, Today</span>
									</div>
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar2.webp" class="rounded-circle user_img_msg" alt="">
									</div>
								</div>
								<div class="d-flex justify-content-start mb-4">
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img_msg" alt="">
									</div>
									<div class="msg_cotainer">
										I am good too, thank you for your chat template
										<span class="msg_time">9:00 AM, Today</span>
									</div>
								</div>
								<div class="d-flex justify-content-end mb-4">
									<div class="msg_cotainer_send">
										You are welcome
										<span class="msg_time_send">9:05 AM, Today</span>
									</div>
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar2.webp" class="rounded-circle user_img_msg" alt="">
									</div>
								</div>
								<div class="d-flex justify-content-start mb-4">
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img_msg" alt="">
									</div>
									<div class="msg_cotainer">
										I am looking for your next templates
										<span class="msg_time">9:07 AM, Today</span>
									</div>
								</div>
								<div class="d-flex justify-content-end mb-4">
									<div class="msg_cotainer_send">
										Ok, thank you have a good day
										<span class="msg_time_send">9:10 AM, Today</span>
									</div>
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar2.webp" class="rounded-circle user_img_msg" alt="">
									</div>
								</div>
								<div class="d-flex justify-content-start mb-4">
									<div class="img_cont_msg">
										<img src="images/avatar/small/avatar1.webp" class="rounded-circle user_img_msg" alt="">
									</div>
									<div class="msg_cotainer">
										Bye, see you
										<span class="msg_time">9:12 AM, Today</span>
									</div>
								</div>
							</div>
							<div class="card-footer type_msg">
								<div class="input-group">
									<textarea class="form-control" placeholder="Type your message..."></textarea>
									<div class="input-group-append">
										<button type="button" class="btn btn-primary"><i class="fa fa-location-arrow"></i></button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="alerts">
						<div class="card mb-sm-3 mb-md-0 contacts_card">
							<div class="card-header chat-list-header text-center">
								<a class="dz-chatbox-btn">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<rect x="0" y="0" width="24" height="24"/>
											<circle fill="var(--bs-heading-color)" cx="5" cy="12" r="2"/>
											<circle fill="var(--bs-heading-color)" cx="12" cy="12" r="2"/>
											<circle fill="var(--bs-heading-color)" cx="19" cy="12" r="2"/>
										</g>
									</svg>
								</a>
								<div class="clearfix">
									<h6 class="mb-1">Notications</h6>
									<p class="mb-0">Show All</p>
								</div>
								<a class="dz-chatbox-btn">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<rect x="0" y="0" width="24" height="24"/>
											<path d="M14.2928932,16.7071068 C13.9023689,16.3165825 13.9023689,15.6834175 14.2928932,15.2928932 C14.6834175,14.9023689 15.3165825,14.9023689 15.7071068,15.2928932 L19.7071068,19.2928932 C20.0976311,19.6834175 20.0976311,20.3165825 19.7071068,20.7071068 C19.3165825,21.0976311 18.6834175,21.0976311 18.2928932,20.7071068 L14.2928932,16.7071068 Z" fill="var(--bs-heading-color)" fill-rule="nonzero" opacity="1"/>
											<path d="M11,16 C13.7614237,16 16,13.7614237 16,11 C16,8.23857625 13.7614237,6 11,6 C8.23857625,6 6,8.23857625 6,11 C6,13.7614237 8.23857625,16 11,16 Z M11,18 C7.13400675,18 4,14.8659932 4,11 C4,7.13400675 7.13400675,4 11,4 C14.8659932,4 18,7.13400675 18,11 C18,14.8659932 14.8659932,18 11,18 Z" fill="var(--bs-heading-color)" fill-rule="nonzero"/>
										</g>
									</svg>
								</a>
							</div>
							<div class="card-body contacts_body p-0 dz-scroll" id="DZ_W_Contacts_Body1">
								<ul class="contacts">
									<li class="name-first-letter">SEVER STATUS</li>
									<li class="active">
										<div class="d-flex bd-highlight">
											<div class="img_cont primary">KK</div>
											<div class="user_info">
												<span>David Nester Birthday</span>
												<p class="text-primary">Today</p>
											</div>
										</div>
									</li>
									<li class="name-first-letter">SOCIAL</li>
									<li>
										<div class="d-flex bd-highlight">
											<div class="img_cont success">RU</div>
											<div class="user_info">
												<span>Perfection Simplified</span>
												<p>Jame Smith commented on your status</p>
											</div>
										</div>
									</li>
									<li class="name-first-letter">SEVER STATUS</li>
									<li>
										<div class="d-flex bd-highlight">
											<div class="img_cont primary">AU</div>
											<div class="user_info">
												<span>AharlieKane</span>
												<p>Sami is online</p>
											</div>
										</div>
									</li>
									<li>
										<div class="d-flex bd-highlight">
											<div class="img_cont info">MO</div>
											<div class="user_info">
												<span>Athan Jacoby</span>
												<p>Nargis left 30 mins ago</p>
											</div>
										</div>
									</li>
								</ul>
							</div>
							<div class="card-footer"></div>
						</div>
					</div>
					<div class="tab-pane fade" id="notes">
						<div class="card mb-sm-3 mb-md-0 note_card">
							<div class="card-header chat-list-header text-center">
								<a class="dz-chatbox-btn">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<rect fill="var(--bs-heading-color)" x="4" y="11" width="16" height="2" rx="1"/>
											<rect fill="var(--bs-heading-color)" opacity="1.0" transform="translate(12.000000, 12.000000) rotate(-270.000000) translate(-12.000000, -12.000000) " x="4" y="11" width="16" height="2" rx="1"/>
										</g>
									</svg>
								</a>
								<div class="clarfix">
									<h6 class="mb-1">Notes</h6>
									<p class="mb-0">Add New Nots</p>
								</div>
								<a class="dz-chatbox-btn">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<rect x="0" y="0" width="24" height="24"/>
											<path d="M14.2928932,16.7071068 C13.9023689,16.3165825 13.9023689,15.6834175 14.2928932,15.2928932 C14.6834175,14.9023689 15.3165825,14.9023689 15.7071068,15.2928932 L19.7071068,19.2928932 C20.0976311,19.6834175 20.0976311,20.3165825 19.7071068,20.7071068 C19.3165825,21.0976311 18.6834175,21.0976311 18.2928932,20.7071068 L14.2928932,16.7071068 Z" fill="var(--bs-heading-color)" fill-rule="nonzero" opacity="1"/>
											<path d="M11,16 C13.7614237,16 16,13.7614237 16,11 C16,8.23857625 13.7614237,6 11,6 C8.23857625,6 6,8.23857625 6,11 C6,13.7614237 8.23857625,16 11,16 Z M11,18 C7.13400675,18 4,14.8659932 4,11 C4,7.13400675 7.13400675,4 11,4 C14.8659932,4 18,7.13400675 18,11 C18,14.8659932 14.8659932,18 11,18 Z" fill="var(--bs-heading-color)" fill-rule="nonzero"/>
										</g>
									</svg>
								</a>
							</div>
							<div class="card-body contacts_body p-0 dz-scroll" id="DZ_W_Contacts_Body2">
								<ul class="contacts">
									<li class="active">
										<div class="d-flex bd-highlight">
											<div class="user_info">
												<span>New order placed..</span>
												<p>10 Aug 2020</p>
											</div>
											<div class="ms-auto">
												<a class="btn btn-square btn-xs btn-primary light ms-1"><i class="fas fa-pencil-alt"></i></a>
												<a class="btn btn-square btn-xs btn-danger light"><i class="fa fa-trash"></i></a>
											</div>
										</div>
									</li>
									<li>
										<div class="d-flex bd-highlight">
											<div class="user_info">
												<span>Youtube, a video-sharing website..</span>
												<p>10 Aug 2020</p>
											</div>
											<div class="ms-auto">
												<a class="btn btn-square btn-xs btn-primary light ms-1"><i class="fas fa-pencil-alt"></i></a>
												<a class="btn btn-square btn-xs btn-danger light"><i class="fa fa-trash"></i></a>
											</div>
										</div>
									</li>
									<li>
										<div class="d-flex bd-highlight">
											<div class="user_info">
												<span>john just buy your product..</span>
												<p>10 Aug 2020</p>
											</div>
											<div class="ms-auto">
												<a class="btn btn-square btn-xs btn-primary light ms-1"><i class="fas fa-pencil-alt"></i></a>
												<a class="btn btn-square btn-xs btn-danger light"><i class="fa fa-trash"></i></a>
											</div>
										</div>
									</li>
									<li>
										<div class="d-flex bd-highlight">
											<div class="user_info">
												<span>Athan Jacoby</span>
												<p>10 Aug 2020</p>
											</div>
											<div class="ms-auto">
												<a class="btn btn-square btn-xs btn-primary light ms-1"><i class="fas fa-pencil-alt"></i></a>
												<a class="btn btn-square btn-xs btn-danger light"><i class="fa fa-trash"></i></a>
											</div>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End - Sidebar Chat Box  -->
		
		<!-- Start - Header -->
        <header class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                            <div class="dashboard_bar">
								Profile
                            </div>
                        </div>
                        <ul class="navbar-nav header-right">
							<li class="nav-item">
								<form>
									<div class="input-group search-area d-lg-inline-flex d-none me-3">
										<div class="input-group-append">
											<button class="input-group-text rounded-0 rounded-start pe-2 border-0"><i class="flaticon-381-search-2"></i></button>
										</div>
										<input type="text" class="form-control ps-2 border-0" placeholder="Search here" aria-label="Username" data-bs-toggle="modal" data-bs-target="#search-modal" aria-describedby="header-search">
									</div>
								</form>
							</li>
							<li class="nav-item">
								<select class="selectpicker form-select i18n-select" id="langSwitcher">
									<option value="en_GB" title="English" data-content="<img src='images/flags/gb-eng.svg' alt=''> English">English</option>
									<option value="pt_BR" title="Portuguese" data-content="<img src='images/flags/pt.svg' alt=''> Português">Português</option>
									<option value="fr_FR" title="French" data-content="<img src='images/flags/fr.svg' alt=''> Français">Français</option>
									<option value="de_DE" title="German" data-content="<img src='images/flags/de.svg' alt=''> Deutsch">Deutsch</option>
									<option value="it_IT" title="Italian" data-content="<img src='images/flags/it.svg' alt=''> Italiano">Italiano</option>
									<option value="jp_JP" title="Japanese" data-content="<img src='images/flags/jp.svg' alt=''> 日本語">日本語</option>
									<option value="es_ES" title="Spanish" data-content="<img src='images/flags/es.svg' alt=''> Español">Español</option>
									<option value="ar_TN" title="Arabic" data-content="<img src='images/flags/arab.svg' alt=''> العربية">العربية</option>
									<option value="tr_TR" title="Turkish" data-content="<img src='images/flags/tr.svg' alt=''> Türkçe">Türkçe</option>
								</select>
							</li>
							<li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link bell dz-theme-mode"  aria-label="dz-theme-mode">
									<i id="icon-light" class="fas fa-sun"></i>
                                    <i id="icon-dark" class="fas fa-moon"></i>
                                </a>
							</li>
							<li class="nav-item notification_dropdown d-none d-lg-flex">
								<a class="nav-link dz-fullscreen"  aria-label="Fullscreen">
									<svg id="icon-full" viewBox="0 0 24 24" width="24" height="24" stroke="var(--bs-primary)" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
										<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3" style="stroke-dasharray: 37, 57; stroke-dashoffset: 0;"></path>
									</svg>
									<svg id="icon-minimize" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--bs-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minimize">
										<path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3" style="stroke-dasharray: 37, 57; stroke-dashoffset: 0;"></path>
									</svg>
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
							<li class="nav-item dropdown notification_dropdown">
								<a class="nav-link bell bell-link"  aria-label="Sidabar Chatbox">
									<svg width="24" height="24" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M22.4605 3.84888H5.31688C4.64748 3.84961 4.00571 4.11586 3.53237 4.58919C3.05903 5.06253 2.79279 5.7043 2.79205 6.3737V18.1562C2.79279 18.8256 3.05903 19.4674 3.53237 19.9407C4.00571 20.4141 4.64748 20.6803 5.31688 20.6811C5.54005 20.6812 5.75404 20.7699 5.91184 20.9277C6.06964 21.0855 6.15836 21.2995 6.15849 21.5227V23.3168C6.15849 23.6215 6.24118 23.9204 6.39774 24.1818C6.5543 24.4431 6.77886 24.6571 7.04747 24.8009C7.31608 24.9446 7.61867 25.0128 7.92298 24.9981C8.22729 24.9834 8.52189 24.8863 8.77539 24.7173L14.6173 20.8224C14.7554 20.7299 14.918 20.6807 15.0842 20.6811H19.187C19.7383 20.68 20.2743 20.4994 20.7137 20.1664C21.1531 19.8335 21.4721 19.3664 21.6222 18.8359L24.8966 7.05011C24.9999 6.67481 25.0152 6.28074 24.9414 5.89856C24.8675 5.51637 24.7064 5.15639 24.4707 4.84663C24.235 4.53687 23.931 4.28568 23.5823 4.11263C23.2336 3.93957 22.8497 3.84931 22.4605 3.84888ZM23.2733 6.60304L20.0006 18.3847C19.95 18.5614 19.8432 18.7168 19.6964 18.8275C19.5496 18.9381 19.3708 18.9979 19.187 18.9978H15.0842C14.5856 18.9972 14.0981 19.1448 13.6837 19.4219L7.84171 23.3168V21.5227C7.84097 20.8533 7.57473 20.2115 7.10139 19.7382C6.62805 19.2648 5.98628 18.9986 5.31688 18.9978C5.09371 18.9977 4.87972 18.909 4.72192 18.7512C4.56412 18.5934 4.4754 18.3794 4.47527 18.1562V6.3737C4.4754 6.15054 4.56412 5.93655 4.72192 5.77874C4.87972 5.62094 5.09371 5.53223 5.31688 5.5321H22.4605C22.5905 5.53243 22.7188 5.56277 22.8353 5.62076C22.9517 5.67875 23.0532 5.76283 23.1318 5.86646C23.2105 5.97008 23.2642 6.09045 23.2887 6.21821C23.3132 6.34597 23.308 6.47766 23.2733 6.60304Z" fill="var(--bs-primary)"/>
										<path d="M7.84173 11.4233H12.0498C12.273 11.4233 12.4871 11.3347 12.6449 11.1768C12.8027 11.019 12.8914 10.8049 12.8914 10.5817C12.8914 10.3585 12.8027 10.1444 12.6449 9.98661C12.4871 9.82878 12.273 9.74011 12.0498 9.74011H7.84173C7.61852 9.74011 7.40446 9.82878 7.24662 9.98661C7.08879 10.1444 7.00012 10.3585 7.00012 10.5817C7.00012 10.8049 7.08879 11.019 7.24662 11.1768C7.40446 11.3347 7.61852 11.4233 7.84173 11.4233Z" fill="var(--bs-primary)"/>
										<path d="M15.4162 13.1066H7.84173C7.61852 13.1066 7.40446 13.1952 7.24662 13.3531C7.08879 13.5109 7.00012 13.725 7.00012 13.9482C7.00012 14.1714 7.08879 14.3855 7.24662 14.5433C7.40446 14.7011 7.61852 14.7898 7.84173 14.7898H15.4162C15.6394 14.7898 15.8535 14.7011 16.0113 14.5433C16.1692 14.3855 16.2578 14.1714 16.2578 13.9482C16.2578 13.725 16.1692 13.5109 16.0113 13.3531C15.8535 13.1952 15.6394 13.1066 15.4162 13.1066Z" fill="var(--bs-primary)"/>
									</svg>
									<div class="pulse-css"></div>
								</a>
							</li>
							<li class="nav-item dropdown notification_dropdown d-xl-inline-flex d-none">
                                <a class="nav-link"  role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg width="24" height="24" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M22.625 5.125H21.75V1.625C21.75 1.47262 21.7102 1.32289 21.6345 1.19062C21.5589 1.05835 21.45 0.948128 21.3186 0.870868C21.1873 0.793609 21.0381 0.751989 20.8857 0.750126C20.7333 0.748264 20.5831 0.786224 20.4499 0.86025L13 4.99909L5.55007 0.86025C5.41688 0.786224 5.26667 0.748264 5.11431 0.750126C4.96194 0.751989 4.8127 0.793609 4.68136 0.870868C4.55002 0.948128 4.44113 1.05835 4.36547 1.19062C4.28981 1.32289 4.25001 1.47262 4.25 1.625V5.125H3.375C2.67904 5.12576 2.01181 5.40257 1.51969 5.89469C1.02757 6.3868 0.750764 7.05404 0.75 7.75V10.375C0.750764 11.071 1.02757 11.7382 1.51969 12.2303C2.01181 12.7224 2.67904 12.9992 3.375 13H4.25V22.625C4.25076 23.321 4.52757 23.9882 5.01969 24.4803C5.51181 24.9724 6.17904 25.2492 6.875 25.25H19.125C19.821 25.2492 20.4882 24.9724 20.9803 24.4803C21.4724 23.9882 21.7492 23.321 21.75 22.625V13H22.625C23.321 12.9992 23.9882 12.7224 24.4803 12.2303C24.9724 11.7382 25.2492 11.071 25.25 10.375V7.75C25.2492 7.05404 24.9724 6.3868 24.4803 5.89469C23.9882 5.40257 23.321 5.12576 22.625 5.125ZM20 5.125H16.3769L20 3.1125V5.125ZM6 3.1125L9.62311 5.125H6V3.1125ZM6 22.625V13H12.125V23.5H6.875C6.64303 23.4997 6.42064 23.4074 6.25661 23.2434C6.09258 23.0793 6.0003 22.857 6 22.625ZM20 22.625C19.9997 22.857 19.9074 23.0793 19.7434 23.2434C19.5794 23.4074 19.357 23.4997 19.125 23.5H13.875V13H20V22.625ZM23.5 10.375C23.4997 10.607 23.4074 10.8294 23.2434 10.9934C23.0794 11.1574 22.857 11.2497 22.625 11.25H3.375C3.14303 11.2497 2.92064 11.1574 2.75661 10.9934C2.59258 10.8294 2.5003 10.607 2.5 10.375V7.75C2.5003 7.51803 2.59258 7.29564 2.75661 7.13161C2.92064 6.96758 3.14303 6.8753 3.375 6.875H22.625C22.857 6.8753 23.0794 6.96758 23.2434 7.13161C23.4074 7.29564 23.4997 7.51803 23.5 7.75V10.375Z" fill="var(--bs-primary)"></path>
									</svg>
									<div class="pulse-css"></div>
                                </a>
								<div class="dropdown-menu dropdown-menu-end py-3 ps-3">
                                    <div id="DZ_W_Gifts" class="widget-timeline dz-scroll style-1 height300">
										<ul class="timeline">
											<li>
												<div class="timeline-badge primary"></div>
												<a class="timeline-panel text-muted" >
													<span>10 minutes ago</span>
													<h6 class="mb-0">Youtube, a video-sharing website, goes live <strong class="text-primary">$500</strong>.</h6>
												</a>
											</li>
											<li>
												<div class="timeline-badge info">
												</div>
												<a class="timeline-panel text-muted" >
													<span>20 minutes ago</span>
													<h6 class="mb-0">New order placed <strong class="text-info">#XF-2356.</strong></h6>
													<p class="mb-0">Quisque a consequat ante Sit amet magna at volutapt...</p>
												</a>
											</li>
											<li>
												<div class="timeline-badge danger">
												</div>
												<a class="timeline-panel text-muted" >
													<span>30 minutes ago</span>
													<h6 class="mb-0">john just buy your product <strong class="text-warning">Sell $250</strong></h6>
												</a>
											</li>
											<li>
												<div class="timeline-badge success">
												</div>
												<a class="timeline-panel text-muted" >
													<span>15 minutes ago</span>
													<h6 class="mb-0">StumbleUpon is acquired by eBay. </h6>
												</a>
											</li>
											<li>
												<div class="timeline-badge warning">
												</div>
												<a class="timeline-panel text-muted" >
													<span>20 minutes ago</span>
													<h6 class="mb-0">Mashable, a news website and blog, goes live.</h6>
												</a>
											</li>
											<li>
												<div class="timeline-badge dark">
												</div>
												<a class="timeline-panel text-muted" >
													<span>20 minutes ago</span>
													<h6 class="mb-0">Mashable, a news website and blog, goes live.</h6>
												</a>
											</li>
										</ul>
									</div>
                                </div>
							</li>
                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
                                    <img src="images/profile/10.webp" width="20" alt="/">
									<div class="header-info">
										<span class="text-black fw-semibold"><p class="mb-1">Peter Parkur</p></span>
										<p class="fs-12 mb-0">Super Admin</p>
									</div>
                                </a>
								<ul class="dropdown-menu dropdown-menu-end">
									<li>
										<div class="py-2 d-flex px-3">
											<img src="images/profile/10.webp" class="avatar avatar-sm rounded-circle" alt="">
											<div class="ms-2">
												<h6 class="mb-0">Johndoe</h6>
												<small>Super Admin</small>
											</div>	
										</div>
									</li>
									<li><hr class="dropdown-divider"></li>
									<li>
										<a class="dropdown-item" href="app-profile.html">
											<svg  width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9848 15.3462C8.11714 15.3462 4.81429 15.931 4.81429 18.2729C4.81429 20.6148 8.09619 21.2205 11.9848 21.2205C15.8524 21.2205 19.1543 20.6348 19.1543 18.2938C19.1543 15.9529 15.8733 15.3462 11.9848 15.3462Z" stroke="var(--bs-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9848 12.0059C14.5229 12.0059 16.58 9.94779 16.58 7.40969C16.58 4.8716 14.5229 2.81445 11.9848 2.81445C9.44667 2.81445 7.38857 4.8716 7.38857 7.40969C7.38 9.93922 9.42381 11.9973 11.9524 12.0059H11.9848Z" stroke="var(--bs-primary)" stroke-width="1.42857" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<span class="ms-2">Profile</span>
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
									<li>
										<a class="dropdown-item">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M17.9026 8.85114L13.4593 12.4642C12.6198 13.1302 11.4387 13.1302 10.5992 12.4642L6.11844 8.85114" stroke="var(--bs-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<path fill-rule="evenodd" clip-rule="evenodd" d="M16.9089 21C19.9502 21.0084 22 18.5095 22 15.4384V8.57001C22 5.49883 19.9502 3 16.9089 3H7.09114C4.04979 3 2 5.49883 2 8.57001V15.4384C2 18.5095 4.04979 21.0084 7.09114 21H16.9089Z" stroke="var(--bs-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<span class="ms-2">Message </span>
										</a>
									</li>
									<li>
										<a href="email-inbox.html" class="dropdown-item">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M12 17.8476C17.6392 17.8476 20.2481 17.1242 20.5 14.2205C20.5 11.3188 18.6812 11.5054 18.6812 7.94511C18.6812 5.16414 16.0452 2 12 2C7.95477 2 5.31885 5.16414 5.31885 7.94511C5.31885 11.5054 3.5 11.3188 3.5 14.2205C3.75295 17.1352 6.36177 17.8476 12 17.8476Z" stroke="var(--bs-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M14.3888 20.8572C13.0247 22.372 10.8967 22.3899 9.51947 20.8572" stroke="var(--bs-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<span class="ms-2">Notification </span>
										</a>
									</li>
									<li>
										<a class="dropdown-item">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M20.8066 7.62355L20.1842 6.54346C19.6576 5.62954 18.4907 5.31426 17.5755 5.83866V5.83866C17.1399 6.09528 16.6201 6.16809 16.1307 6.04103C15.6413 5.91396 15.2226 5.59746 14.9668 5.16131C14.8023 4.88409 14.7139 4.56833 14.7105 4.24598V4.24598C14.7254 3.72916 14.5304 3.22834 14.17 2.85761C13.8096 2.48688 13.3145 2.2778 12.7975 2.27802H11.5435C11.0369 2.27801 10.5513 2.47985 10.194 2.83888C9.83666 3.19791 9.63714 3.68453 9.63958 4.19106V4.19106C9.62457 5.23686 8.77245 6.07675 7.72654 6.07664C7.40418 6.07329 7.08843 5.98488 6.8112 5.82035V5.82035C5.89603 5.29595 4.72908 5.61123 4.20251 6.52516L3.53432 7.62355C3.00838 8.53633 3.31937 9.70255 4.22997 10.2322V10.2322C4.82187 10.574 5.1865 11.2055 5.1865 11.889C5.1865 12.5725 4.82187 13.204 4.22997 13.5457V13.5457C3.32053 14.0719 3.0092 15.2353 3.53432 16.1453V16.1453L4.16589 17.2345C4.41262 17.6797 4.82657 18.0082 5.31616 18.1474C5.80575 18.2865 6.33061 18.2248 6.77459 17.976V17.976C7.21105 17.7213 7.73116 17.6515 8.21931 17.7821C8.70746 17.9128 9.12321 18.233 9.37413 18.6716C9.53867 18.9488 9.62708 19.2646 9.63043 19.5869V19.5869C9.63043 20.6435 10.4869 21.5 11.5435 21.5H12.7975C13.8505 21.5 14.7055 20.6491 14.7105 19.5961V19.5961C14.7081 19.088 14.9088 18.6 15.2681 18.2407C15.6274 17.8814 16.1154 17.6806 16.6236 17.6831C16.9451 17.6917 17.2596 17.7797 17.5389 17.9393V17.9393C18.4517 18.4653 19.6179 18.1543 20.1476 17.2437V17.2437L20.8066 16.1453C21.0617 15.7074 21.1317 15.1859 21.0012 14.6963C20.8706 14.2067 20.5502 13.7893 20.111 13.5366V13.5366C19.6717 13.2839 19.3514 12.8665 19.2208 12.3769C19.0902 11.8872 19.1602 11.3658 19.4153 10.9279C19.5812 10.6383 19.8213 10.3981 20.111 10.2322V10.2322C21.0161 9.70283 21.3264 8.54343 20.8066 7.63271V7.63271V7.62355Z" stroke="var(--bs-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<circle cx="12.175" cy="11.889" r="2.63616" stroke="var(--bs-primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<span class="ms-2">Settings </span>
										</a>
									</li>
									<li><hr class="dropdown-divider"></li>
									<li>
										<a href="page-login.html" class="dropdown-item">
											<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--bs-danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path stroke="var(--bs-danger)" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
												<polyline stroke="var(--bs-danger)" points="16 17 21 12 16 7"></polyline>
												<line x1="21" y1="12" x2="9" y2="12"></line>
											</svg>
											<span class="ms-2 text-danger">Logout </span>
										</a>
									</li>
								</ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>
        <!-- End - Header -->

		<!-- Start - Sidebar -->
          <div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
                    <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
							<i class="flaticon-381-networking"></i>
							<span class="nav-text" data-i18n="Dashboard">Dashboard</span>
						</a>
                        <ul aria-expanded="false">
							<li><a href="index.html" data-i18n="Dashboard Light">Dashboard Light</a></li>
							<li><a href="index-dark.html" data-i18n="Dashboard Dark"><span>Dashboard Dark</span></a></li>
							<li><a href="workout-statistic.html" data-i18n="Workout Statistic"><span>Workout Statistic</span></a></li>
							<li><a href="workoutplan.html" data-i18n="Workout Plan"><span>Workout Plan</span></a></li>
							<li><a href="distance-map.html" data-i18n="Distance Map"><span>Distance Map</span></a></li>
							<li><a href="food-menu.html" data-i18n="Diet Food Menu"><span>Diet Food Menu</span></a></li>
							<li><a href="personal-record.html" data-i18n="Personal Record"><span>Personal Record</span></a></li>
						</ul>
                    </li>
                    <li>
						<a class="has-arrow"  aria-expanded="false">
							<i class="flaticon-381-user-7"></i>
							<span class="nav-text" data-i18n="Profile">Profile </span>
						</a>
						<ul aria-expanded="false">
							<li><a href="profile/overview.html" data-i18n="Overview">Overview</a></li>
							<li><a href="profile/projects.html" data-i18n="Projects">Projects</a></li>
							<li><a href="profile/projects-details.html" data-i18n="Projects Details">Projects Details</a></li>
							<li><a href="profile/campaigns.html" data-i18n="Campaigns">Campaigns</a></li>
							<li><a href="profile/documents.html" data-i18n="Documents">Documents</a></li>
							<li><a href="profile/followers.html" data-i18n="Followers">Followers</a></li>
							<li><a href="profile/activity.html" data-i18n="Activity">Activity</a></li>
						</ul>
					</li>
					<li>
						<a class="has-arrow "  aria-expanded="false">
							<i class="flaticon-381-user-4"></i>
							<span class="nav-text"  data-i18n="Account">Account</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="account/overview.html" data-i18n="Overview">Overview</a></li>
							<li><a href="account/settings.html" data-i18n="Settings">Settings</a></li>
							<li><a href="account/security.html" data-i18n="Security">Security</a></li>
							<li><a href="account/activity.html" data-i18n="Activity">Activity</a></li>
							<li><a href="account/billing.html" data-i18n="Billing">Billing</a></li>
							<li><a href="account/statements.html" data-i18n="Statements">Statements</a></li>
							<li><a href="account/referrals.html" data-i18n="Referrals">Referrals</a></li>
							<li><a href="account/api-keys.html" data-i18n="Api Keys">Api Keys</a></li>
							<li><a href="account/logs.html" data-i18n="Logs">Logs</a></li>
						</ul>
					</li>
                    <li><a class="has-arrow ai-icon"  aria-expanded="false">
							<i class="flaticon-381-television"></i>
							<span class="nav-text" data-i18n="Apps">Apps</span>
						</a>
                        <ul aria-expanded="false">
                            <li><a href="app-profile.html" data-i18n="Profile">Profile</a></li>
                            <li><a href="edit-profile.html" data-i18n="Edit Profile">Edit Profile</a></li>
                            <li><a href="post-details.html" data-i18n="Post Details">Post Details</a></li>
                            <li><a class="has-arrow"  aria-expanded="false" data-i18n="Email">Email</a>
                                <ul aria-expanded="false">
                                    <li><a href="email-compose.html" data-i18n="Compose">Compose</a></li>
                                    <li><a href="email-inbox.html" data-i18n="Inbox">Inbox</a></li>
                                    <li><a href="email-read.html" data-i18n="Read">Read</a></li>
                                </ul>
                            </li>
                            <li><a href="app-calender.html" data-i18n="Calendar">Calendar</a></li>
							<li><a class="has-arrow"  aria-expanded="false" data-i18n="Shop">Shop</a>
                                <ul aria-expanded="false">
                                    <li><a href="ecom-product-grid.html" data-i18n="Product Grid">Product Grid</a></li>
									<li><a href="ecom-product-list.html" data-i18n="Product List">Product List</a></li>
									<li><a href="ecom-product-detail.html" data-i18n="Product Details">Product Details</a></li>
									<li><a href="ecom-product-order.html" data-i18n="Order">Order</a></li>
									<li><a href="ecom-checkout.html" data-i18n="Checkout">Checkout</a></li>
									<li><a href="ecom-invoice.html" data-i18n="Invoice">Invoice</a></li>
									<li><a href="ecom-customers.html" data-i18n="Customers">Customers</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
					<li>
						<a class="has-arrow "  aria-expanded="false">
							<i class="flaticon-381-settings-3"></i>
							<span class="nav-text" data-i18n="AIKit">AIKit</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="aikit/auto-write.html" data-i18n="Auto Writer">Auto Writer</a></li>
							<li><a href="aikit/scheduled.html" data-i18n="Scheduler">Scheduler</a></li>
							<li><a href="aikit/repurpose.html" data-i18n="Repurpose">Repurpose</a></li>
							<li><a href="aikit/rss.html" data-i18n="RSS">RSS</a></li>
							<li><a href="aikit/chatbot.html" data-i18n="Chatbot">Chatbot</a></li>
							<li><a href="aikit/fine-tune-models.html" data-i18n="Fine tune Models">Fine tune Models</a></li>
							<li><a href="aikit/prompt.html" data-i18n="AI Menu Prompts">AI Menu Prompts</a></li>
							<li><a href="aikit/setting.html" data-i18n="Settings">Settings</a></li>
							<li><a href="aikit/import.html" data-i18n="Export/Import Settings">Export/Import Settings</a></li>
						</ul>
					</li>
					<li>
						<a class="has-arrow "  aria-expanded="false">
							<i class="flaticon-381-database-1"></i>
							<span class="nav-text" data-i18n="CMS">CMS</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="cms/content.html" data-i18n="Content">Content</a></li>
							<li><a href="cms/content-add.html" data-i18n="Add Content">Add Content</a></li>
							<li><a href="cms/menu.html" data-i18n="Menus">Menus</a></li>	
							<li><a href="cms/email-template.html" data-i18n="Email Template">Email Template</a></li>		
							<li><a href="cms/add-email.html" data-i18n="Add Email">Add Email</a></li>		
							<li><a href="cms/blog.html" data-i18n="Blog">Blog</a></li>	
							<li><a href="cms/blog-category.html" data-i18n="Blog Category">Blog Category</a></li>	
							<li><a href="cms/add-blog.html" data-i18n="Add Blog">Add Blog</a></li>	
						</ul>
					</li>
                    <li><a class="has-arrow ai-icon" href="javascript:void(0)" aria-expanded="false">
							<i class="flaticon-381-layer-1"></i>
							<span class="nav-text" data-i18n="Pages">Pages</span>
						</a>
                        <ul aria-expanded="false">
                            <li><a href="pages/register.html" data-i18n="Register">Register</a></li>
                            <li><a href="pages/login.html" data-i18n="Login">Login</a></li>
                            <li><a class="has-arrow" href="javascript:void(0)" aria-expanded="false" data-i18n="Error">Error</a>
                                <ul aria-expanded="false">
                                    <li><a href="pages/error-400.html" data-i18n="Error 400">Error 400</a></li>
									<li><a href="pages/error-403.html" data-i18n="Error 403">Error 403</a></li>
                                    <li><a href="pages/error-404.html" data-i18n="Error 404">Error 404</a></li>
                                    <li><a href="pages/error-500.html" data-i18n="Error 500">Error 500</a></li>
                                    <li><a href="pages/error-503.html" data-i18n="Error 503">Error 503</a></li>
                                </ul>
                            </li>
                            <li><a href="pages/lock-screen.html" data-i18n="Lock Screen">Lock Screen</a></li>
                        </ul>
                    </li>
                </ul>
				<div class="add-menu-sidebar">
					<img src="images/calendar.avif" alt="" class="me-3">
					<a href="workoutplan.html" class="font-w500 mb-0">Create Workout Plan Now</a>
				</div>
				<div class="copyright">
					<p><strong>Gymove Fitness Admin Dashboard</strong> © <span class="current-year">2023</span> All Rights Reserved</p>
					<p>Made with <span class="heart"></span> by DexignZone</p>
				</div>
			</div>
			<div class="deznav-footer">
				<a href="https://coreui.w3itexperts.com/?theme=Gymove" target="_blank" class="btn btn-docs btn-success w-100 mb-2">
					<span>Docs & Components</span>
					<i class="fa-solid fa-arrow-up rotate-x"></i>
				</a>
			</div>
        </div>
        <!-- End - Sidebar -->

       	<!-- Start - Content Body -->
        <main class="content-body">
			
			<div class="container-fluid">
            
				<!-- Start - Page Title & Breadcrumb -->
				<div class="page-title">
					    <nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li><h1>App Profile</h1></li>
			<li class="breadcrumb-item">
				<a href="index.html">
					<svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					Home
				</a>
			</li>
			<li class="breadcrumb-item active" aria-current="page">App Profile</li>
		</ol>
	</nav>
				</div>
				<!-- End - Page Title & Breadcrumb -->
				
				<div class="row">
                    <div class="col-lg-12">
                        <div class="card-profile card card-body p-2 pb-0">
							<div class="photo-content">
								<div class="cover-photo rounded"></div>
							</div>
							<div class="p-sm-3 p-2 d-sm-flex w-100">
								<div class="profile-photo me-sm-3">
									<img src="images/profile/profile.avif" class="img-fluid rounded-circle border border-4 border-white" alt="">
								</div>
								<div class="d-flex w-100 py-2 py-sm-0">
									<div class="d-flex flex-wrap w-100 gap-3">
										<div class="clearfix">
											<h5 class="mb-0">Mitchell C. Shay</h5>
											<span>UX / UI Designer</span>
										</div>
										<div class="clearfix">
											<h5 class="mb-0">hello@email.com</h5>
											<span>Email</span>
										</div>
									</div>
									<div class="dropdown ms-auto">
										<button class="btn btn-sm btn-square btn-primary light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="fa-solid fa-ellipsis"></i>
										</button>
										<ul class="dropdown-menu">
											<li><a class="dropdown-item" >
												<i class="fa fa-user-circle text-primary me-2"></i> View profile</a>
											</li>
											<li><a class="dropdown-item" >
												<i class="fa fa-users text-primary me-2"></i> Add to friends</a>
											</li>
											<li><a class="dropdown-item" >
												<i class="fa fa-plus text-primary me-2"></i> Add to group</a>
											</li>
											<li><a class="dropdown-item" >
												<i class="fa fa-ban text-primary me-2"></i> Block</a>
											</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
                    </div>
                </div>
				
                <div class="row">
                    <div class="col-xl-4 col-xxl-3">
						<div class="row">
							<div class="col-xl-12">
								<div class="card">
									<div class="card-body text-center">
										<div class="row">
											<div class="col">
												<span class="mb-0 d-block h2">150</span>
												<span>Follower</span>
											</div>
											<div class="col">
												<span class="mb-0 d-block h2">140</span>
												<span>Place Stay</span>
											</div>
											<div class="col">
												<span class="mb-0 d-block h2">45</span>
												<span>Reviews</span>
											</div>
										</div>
										<div class="mt-4">
											<a class="btn btn-primary mb-1 me-1">Follow</a> 
											<a class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#sendMessageModal">Send Message</a>
										</div>
									</div>
								</div>
							</div>
							
							<div class="col-xl-12">
								<div class="card">
									<div class="card-header border-0 pb-0">
										<h4 class="card-title">Today Highlights</h4>
									</div>
									<div class="card-body">
										<img src="images/profile/1.webp" alt="" class="img-fluid mb-3 w-100 rounded">
										<h5><a href="post-details.html">Darwin Creative Agency Theme</a></h5>
										<p class="mb-0">A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.</p>
									</div>
								</div>
							</div>
							
							<div class="col-xl-12">
								<div class="card">
									<div class="card-header border-0 pb-0">
										<h4 class="card-title">Interest</h4>
									</div>
									<div class="card-body">
										<div class="row g-1" id="lightgallery">
											<a href="images/profile/2.webp" data-exthumbimage="images/profile/2.webp" data-src="images/profile/2.webp" class="col-4">
												<img src="images/profile/2.webp" alt="" class="img-fluid rounded">
											</a>
											<a href="images/profile/3.webp" data-exthumbimage="images/profile/3.webp" data-src="images/profile/3.webp" class="col-4">
												<img src="images/profile/3.webp" alt="" class="img-fluid rounded">
											</a>
											<a href="images/profile/4.webp" data-exthumbimage="images/profile/4.webp" data-src="images/profile/4.webp" class="col-4">
												<img src="images/profile/4.webp" alt="" class="img-fluid rounded">
											</a>
											<a href="images/profile/3.webp" data-exthumbimage="images/profile/3.webp" data-src="images/profile/3.webp" class="col-4">
												<img src="images/profile/3.webp" alt="" class="img-fluid rounded">
											</a>
											<a href="images/profile/4.webp" data-exthumbimage="images/profile/4.webp" data-src="images/profile/4.webp" class="col-4">
												<img src="images/profile/4.webp" alt="" class="img-fluid rounded">
											</a>
											<a href="images/profile/2.webp" data-exthumbimage="images/profile/2.webp" data-src="images/profile/2.webp" class="col-4">
												<img src="images/profile/2.webp" alt="" class="img-fluid rounded">
											</a>
										</div>
									</div>
								</div>
							</div>
							
							<div class="col-xl-12">
								<div class="card">
									<div class="card-header border-0 pb-0">
										<h4 class="card-title">Our Latest News</h4>
									</div>
									<div class="card-body py-3">
										<div class="d-flex py-2 align-items-center">
											<img src="images/profile/5.webp" alt="image" class="avatar avatar-lg rounded">
											<div class="ms-3">
												<h6 class="mb-1"><a href="post-details.html">Collection of textile samples</a></h6>
												<p class="mb-0">I shared this on my fb wall a few months back, and I thought.</p>
											</div>
										</div>
										<div class="d-flex py-2 align-items-center">
											<img src="images/profile/6.webp" alt="image" class="avatar avatar-lg rounded">
											<div class="ms-3">
												<h6 class="mb-1"><a href="post-details.html">Website Developer </a></h6>
												<p class="mb-0">I shared this on my fb wall a few months back, and I thought.</p>
											</div>
										</div>
										<div class="d-flex py-2 align-items-center">
											<img src="images/profile/7.webp" alt="image" class="avatar avatar-lg rounded">
											<div class="ms-3">
												<h6 class="mb-1"><a href="post-details.html">Mern stack Developer</a></h6>
												<p class="mb-0">I shared this on my fb wall a few months back, and I thought.</p>
											</div>
										</div>
									</div>
								</div>
							</div>
							
						</div>
                    </div>
                    <div class="col-xl-8 col-xxl-9">
                        <div class="card h-auto">
							<div class="card-header">
								<ul class="nav nav-underline card-header-tabs" id="nav-tab" role="tablist">
									<li class="nav-item">
										<button class="nav-link active" id="underline-Posts-tab" data-bs-toggle="tab" data-bs-target="#underline-Posts" type="button" role="tab" aria-controls="underline-Posts" aria-selected="true">Posts</button>
									</li>
									<li class="nav-item">
										<button class="nav-link" id="underline-about-tab" data-bs-toggle="tab" data-bs-target="#underline-about" type="button" role="tab" aria-controls="underline-about" aria-selected="false">About Me</button>
									</li>
									<li class="nav-item">
										<button class="nav-link" id="underline-setting-tab" data-bs-toggle="tab" data-bs-target="#underline-setting" type="button" role="tab" aria-controls="underline-setting" aria-selected="false">Setting</button>
									</li>
								</ul>
							</div>
							<div class="card-body">
								<div class="tab-content" id="underline-tabContent">
									<div class="tab-pane fade show active" id="underline-Posts" role="tabpanel" aria-labelledby="underline-Posts-tab" tabindex="0">
										<div class="clearfix mb-4">
											<textarea name="textarea" id="textarea" rows="5" class="form-control" placeholder="Please type what you want...."></textarea>
											<div class="mt-3">
												<a class="btn btn-square btn-primary light me-1" data-bs-toggle="modal" data-bs-target="#linkModal">
													<i class="fa fa-link"></i>
												</a>
												<a class="btn btn-square btn-primary light me-1" data-bs-toggle="modal" data-bs-target="#cameraModal">
													<i class="fa fa-camera"></i>
												</a>
												<a class="btn btn-primary me-1" data-bs-toggle="modal" data-bs-target="#postModal">Post</a>
											</div>
										</div>
										
										<!-- Blog Post 1 -->
										<div class="pb-5">
											<img src="images/profile/8.webp" alt="" class="img-fluid w-100 rounded">
											<div class="mt-3">
												<h3><a href="post-details.html">Collection of textile samples lay spread</a></h3>
												<p>A wonderful serenity has take possession of my entire soul like these sweet morning of spare which enjoy whole heart. A wonderful serenity has take possession of my entire soul like these sweet morning of spare which enjoy whole heart.</p>
												<button class="btn btn-primary me-1">
													<i class="fa fa-heart"></i> Like
												</button>
												<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#replyModal">
													<i class="fa fa-reply"></i> Reply
												</button>
											</div>
										</div>
										
										<!-- Blog Post 2 -->
										<div class="pb-5">
											<img src="images/profile/9.webp" alt="" class="img-fluid w-100 rounded">
											<div class="mt-3">
												<h3><a href="post-details.html">Collection of textile samples lay spread</a></h3>
												<p>A wonderful serenity has take possession of my entire soul like these sweet morning of spare which enjoy whole heart. A wonderful serenity has take possession of my entire soul like these sweet morning of spare which enjoy whole heart.</p>
												<button class="btn btn-primary me-1">
													<i class="fa fa-heart"></i> Like
												</button>
												<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#replyModal">
													<i class="fa fa-reply"></i> Reply
												</button>
											</div>
										</div>
										
										<!-- Blog Post 3 -->
										<div class="pb-4">
											<img src="images/profile/8.webp" alt="" class="img-fluid w-100 rounded">
											<div class="mt-3">
												<h3><a href="post-details.html">Collection of textile samples lay spread</a></h3>
												<p>A wonderful serenity has take possession of my entire soul like these sweet morning of spare which enjoy whole heart. A wonderful serenity has take possession of my entire soul like these sweet morning of spare which enjoy whole heart.</p>
												<button class="btn btn-primary me-1">
													<i class="fa fa-heart"></i> Like
												</button>
												<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#replyModal">
													<i class="fa fa-reply"></i> Reply
												</button>
											</div>
										</div>
										
									</div>
									<div class="tab-pane fade" id="underline-about" role="tabpanel" aria-labelledby="underline-about-tab" tabindex="0">
										<div class="pt-4 border-bottom-1 pb-3">
											<h4 class="text-primary">About Me</h4>
											<p class="mb-2">A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart. I am alone, and feel the charm of existence was created for the bliss of souls like mine.I am so happy, my dear friend, so absorbed in the exquisite sense of mere tranquil existence, that I neglect my talents.</p>
											<p>A collection of textile samples lay spread out on the table - Samsa was a travelling salesman - and above it there hung a picture that he had recently cut out of an illustrated magazine and housed in a nice, gilded frame.</p>
										</div>
										<div class="py-3 border-top border-bottom">
											<h4 class="text-primary mb-2">Skills</h4>
											<a href="javascript:void();;" class="btn btn-primary light btn-sm mb-1">Admin</a>
											<a href="javascript:void();;" class="btn btn-primary light btn-sm mb-1" style="direction: ltr;">Dashboard</a>
											<a href="javascript:void();;" class="btn btn-primary light btn-sm mb-1">Photoshop</a>
											<a href="javascript:void();;" class="btn btn-primary light btn-sm mb-1" style="direction: ltr;">Bootstrap</a>
											<a href="javascript:void();;" class="btn btn-primary light btn-sm mb-1">Responsive</a>
											<a href="javascript:void();;" class="btn btn-primary light btn-sm mb-1">Crypto</a>
										</div>
										<div class="py-3 border-bottom">
											<h4 class="text-primary mb-2">Language</h4>
											<a class="badge light badge-primary">English</a> 
											<a class="badge light badge-primary">French</a>
											<a class="badge light badge-primary">Bangla</a>
										</div>
										<div class="py-3 px-4 bg-primary-subtle text-dark rounded mt-3">
											<h4 class="text-primary mb-4">Personal Information</h4>
											<div class="row mb-2">
												<div class="col-md-3 col-sm-4">
													<h5>Name <span class="float-sm-end">:</span></h5>
												</div>
												<div class="col-md-9 col-sm-8">
													<span>Mitchell C.Shay</span>
												</div>
											</div>
											<div class="row mb-2">
												<div class="col-md-3 col-sm-4">
													<h5>Email <span class="float-sm-end">:</span></h5>
												</div>
												<div class="col-md-9 col-sm-8">
													<span>example@examplel.com</span>
												</div>
											</div>
											<div class="row mb-2">
												<div class="col-md-3 col-sm-4">
													<h5>Availability <span class="float-sm-end">:</span></h5>
												</div>
												<div class="col-md-9 col-sm-8">
													<span>Full Time (Free Lancer)</span>
												</div>
											</div>
											<div class="row mb-2">
												<div class="col-md-3 col-sm-4">
													<h5>Age <span class="float-sm-end">:</span></h5>
												</div>
												<div class="col-md-9 col-sm-8">
													<span>27</span>
												</div>
											</div>
											<div class="row mb-2">
												<div class="col-md-3 col-sm-4">
													<h5>Location <span class="float-sm-end">:</span></h5>
												</div>
												<div class="col-md-9 col-sm-8">
													<span>Rosemont Avenue Melbourne, Florida</span>
												</div>
											</div>
											<div class="row mb-2">
												<div class="col-md-3 col-sm-4">
													<h5>Year Experience <span class="float-sm-end">:</span></h5>
												</div>
												<div class="col-md-9 col-sm-8">
													<span>07 Year Experiences</span>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane fade" id="underline-setting" role="tabpanel" aria-labelledby="underline-setting-tab" tabindex="0">
										<h4 class="text-primary">Account Setting</h4>
										<form>
											<div class="row">
												<div class="mb-3 col-md-6">
													<label class="form-label">Email</label>
													<input type="email" placeholder="Email" class="form-control">
												</div>
												<div class="mb-3 col-md-6">
													<label class="form-label">Password</label>
													<input type="password" autocomplete="current-password" placeholder="Password" class="form-control">
												</div>
												<div class="mb-3 col-md-12">													
													<label class="form-label">Address</label>
													<input type="text" placeholder="1234 Main St" class="form-control">
												</div>
												<div class="mb-3 col-md-12">
													<label class="form-label">Address 2</label>
													<input type="text" placeholder="Apartment, studio, or floor" class="form-control">
												</div>
												<div class="mb-3 col-md-4">
													<label class="form-label">City</label>
													<input type="text" class="form-control">
												</div>
												<div class="mb-3 col-md-4">
													<label class="form-label">State</label>
													<select class="selectpicker form-select">
														<option selected="">Choose...</option>
														<option>Option 1</option>
														<option>Option 2</option>
														<option>Option 3</option>
													</select>
												</div>
												<div class="mb-3 col-md-4">
													<label class="form-label">Zip</label>
													<input type="number" class="form-control type-number">
												</div>
												<div class="mb-3 col-md-12">													
													<div class="form-check custom-checkbox">
														<input type="checkbox" class="form-check-input" id="gridCheck">
														<label class="form-check-label" for="gridCheck"> Check me out</label>
													</div>
												</div>
												<div class="col-md-12">
													<button class="btn btn-primary" type="submit">Sign in</button>
												</div>
											</div>
										</form>
									</div>
								</div>
								
                            </div>
                        </div>
                    </div>
                </div>
				
            </div>
        </main>
		<!-- End - Content Body -->
	
		<!-- Start - Modal Send Message -->
		<div class="modal fade" id="sendMessageModal">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Send Message</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body">
						<form class="comment-form">
							<div class="row"> 
								<div class="col-lg-6">
									<div class="mb-3">
										<label class="form-label">Name <span class="required">*</span></label>
										<input type="text" class="form-control" name="Author" placeholder="Mitchell C. Shay">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="mb-3">
										<label class="form-label">Email <span class="required">*</span></label>
										<input type="text" class="form-control" placeholder="info@example.com" name="Email">
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Comment</label>
										<textarea rows="4" class="form-control" name="comment" placeholder="Comment"></textarea>
									</div>
								</div>
								<div class="col-lg-12">
									<input type="submit" value="Post Comment" class="submit btn btn-primary" name="submit">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- End - Modal Send Message -->
		
		<!-- Start - Modal Social Links -->
		<div class="modal fade" id="linkModal">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Social Links</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body">
						<div class="d-flex gap-2">
							<a class="btn btn-square btn-facebook btn-sm"><i class="fa-brands fa-facebook-f"></i></a>
							<a class="btn btn-square btn-skype btn-sm"><i class="fa-brands fa-skype"></i></a>
							<a class="btn btn-square btn-linkedin btn-sm"><i class="fa-brands fa-linkedin-in"></i></a>
							<a class="btn btn-square btn-instagram btn-sm"><i class="fa-brands fa-instagram"></i></a>
							<a class="btn btn-square btn-primary btn-sm"><i class="fa fa-rss"></i></a>
						</div>
					</div>
				</div>
			</div>
		</div>
        <!-- End - Modal Social Links -->
		
		<!-- Start - Modal Post -->
		<div class="modal fade" id="postModal">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Post</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body">
						<textarea name="textarea" id="textarea2" cols="30" rows="5" class="form-control mb-3" placeholder="Please type what you want...."></textarea>
						<a class="btn btn-primary" href="javascript:void(0)">Post</a>
					</div>
				</div>
			</div>
		</div>
		<!-- End - Modal Post -->
		
		<!-- Start - Modal Upload Images -->
		<div class="modal fade" id="cameraModal">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Upload images</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal">
						</button>
					</div>
					<div class="modal-body">
						<div class="input-group custom_file_input mb-3">
							<span class="input-group-text border-0">Upload</span>
							<div class="form-file">
								<input type="file" class="form-file-input form-control">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End - Modal Upload Images -->
		
		<!-- Start - Modal Post Reply -->
		<div class="modal fade" id="replyModal">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Post Reply</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body">
						<form>
							<textarea class="form-control h-50" rows="4">Message</textarea>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary">Reply</button>
					</div>
				</div>
			</div>
		</div>
		<!-- End - Modal Post Reply -->

                <!-- Start - Footer -->
        <footer class="footer">
            <div class="copyright">
                  <p>Copyright © Designed &amp; Developed by <a href="http://dexignzone.com/" target="_blank">DexignZone</a> <span class="current-year">2023</span></p>
            </div>
        </footer>
        <!-- End - Footer -->

		<div class="modal fade" id="search-modal" tabindex="-1" aria-labelledby="search-modal" aria-hidden="true" data-bs-keyboard="false">>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-0">
                <form class="d-flex align-items-center position-relative w-100" action="#">
                    <button type="button" class="btn btn-sm border-0 position-absolute start-0 p-0 text-sm">
                        <i class="flaticon-381-search-2"></i>
                    </button>
                    <input type="text" class="form-control form-control-lg ps-4 border-0 shadow-none" id="searchInput" placeholder="Search anything's">
                </form>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="dz-scroll" style="height: 325px;">
                    <span class="text-uppercase fw-semibold text-black d-block mb-2">Quick Actions</span>
                    <ul class="actions" id="resultsList"></ul>
                    <ul class="actions" id="defaultResult">
                        <li class="quick-list">
                            <a href="index.html"><i class="flaticon-381-networking"></i>Dashboard</a>
                        </li>
                        <li class="quick-list">
                            <a href="app-calender.html"><i class="flaticon-381-calendar"></i>Calendar</a>
                        </li>
                        <li class="quick-list">
                            <a href="email-inbox.html"><i class="flaticon-381-notebook"></i>Emails</a>
                        </li>
                        <li class="quick-list">
                            <a href="app-profile.html"><i class="flaticon-381-user-7"></i>Profile</a>
                        </li>
                        <li class="quick-list">
                            <a href="ecom-product-list.html"><i class="flaticon-381-briefcase"></i>Shop</a>
                        </li>
                        <li class="quick-list">
                            <a href="ecom-customers.html"><i class="flaticon-381-user-9"></i>Customers</a>
                        </li>
                    </ul>
                    <span class="text-uppercase fw-semibold text-black d-block my-2">Help</span>
                    <ul class="actions">
                        <li class="quick-list">
                            <a href="https://coreui.w3itexperts.com/?theme=Gymove"><i class="flaticon-381-reading"></i>Read Documentation</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
    <!-- End - Main Wrapper -->
	
	<!-- Start - Script -->
	<script src="vendor/jquery/dist/jquery.min.js"></script>
	<script src="vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="vendor/@yaireo/tagify/dist/tagify.js"></script>
	<script src="vendor/metismenu/dist/metisMenu.min.js"></script>
	<script src="vendor/chart-js/chart.bundle.min.js"></script>
    
	<!-- Script For Lightgallery -->
	<script src="vendor/lightgallery/js/lightgallery-all.min.js"></script>
	
	<!-- Script For Multiple Languages -->
	<script src="vendor/i18n/i18n.js"></script>
	<script src="js/translator.js"></script>

	<!-- Script For Custom JS -->
	<script src="js/custom.js"></script>
	<script src="js/deznav-init.js"></script>

</body>
</html>