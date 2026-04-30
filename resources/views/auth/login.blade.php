<!DOCTYPE html>
<html lang="en">

<head>
   
	<base href="{{ asset('assets') }}/">
    
	<!-- Title -->
	<title>Gymove  - Fitness Bootstrap Admin Dashboard Template</title>

	<!-- Meta -->
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
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
</head>
<body class="vh-100">

	<!-- Start - Authincation Section -->
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
					<div class="card p-5 shadow-lg">
						<div class="text-center mb-3">
							@if (! empty($logoPaths))
								<div id="company-logo-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2200" data-bs-pause="false">
									<div class="carousel-inner">
										@foreach ($logoPaths as $logoPath)
											<div class="carousel-item {{ $loop->first ? 'active' : '' }}">
												<img src="{{ $logoPath }}" alt="Logo Perusahaan" style="max-width: 220px; width: 100%; height: 84px; object-fit: contain;">
											</div>
										@endforeach
									</div>
								</div>
							@else
								<a href="{{ route('login') }}" class="brand-logo" aria-label="Andalan Migration">
									<span class="fw-semibold">Andalan Migration</span>
								</a>
							@endif
						</div>
						<h4 class="text-center mb-4">Sign in your account</h4>
						@if ($errors->any())
							<div class="alert alert-danger">
								{{ $errors->first() }}
							</div>
						@endif
						<form id="login-form" action="{{ route('login.store') }}" method="POST">
							@csrf
							<div class="form-group mb-3">
								<label class="form-label"><strong>Email</strong></label>
								<input type="email" name="email" class="form-control form-control-lg" value="{{ old('email') }}" required autocomplete="email" autofocus>
							</div>
							<div class="form-group mb-3">
								<label class="form-label"><strong>Password</strong></label>
								<div class="position-relative">
									<input type="password" name="password" autocomplete="current-password" class="form-control form-control-lg dz-password" placeholder="Enter your password" required>
									<span class="show-pass position-absolute top-50 end-0 me-2 translate-middle">
										<span class="show"><i class="fa fa-eye-slash"></i></span>
										<span class="hide"><i class="fa fa-eye"></i></span>
									</span>
								</div>
							</div>
							<div class="form-row d-flex justify-content-between mt-4 mb-2 flex-wrap">
								<div class="form-group mb-3">
								   <div class="custom-control custom-checkbox ms-1">
										<input type="checkbox" name="remember" class="form-check-input" id="basic_checkbox_1" {{ old('remember') ? 'checked' : '' }}>
										<label class="form-check-label" for="basic_checkbox_1">Remember my preference</label>
									</div>
								</div>
							</div>
							<div class="text-center">
								<button type="submit" class="btn btn-primary btn-lg w-100">Sign Me In</button>
							</div>
						</form>
					</div>
                </div>
            </div>
        </div>
    </div>
	<!-- End - Authincation Section -->
	
	<!-- Start - Script -->
	<script src="vendor/jquery/dist/jquery.min.js"></script>
	<script src="vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="vendor/@yaireo/tagify/dist/tagify.js"></script>
	<script src="vendor/metismenu/dist/metisMenu.min.js"></script>
	{{-- <script src="vendor/chart-js/chart.bundle.min.js"></script> --}}
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
	<!-- Script For Custom JS -->
	{{-- <script src="js/deznav-init.js"></script>
    <script src="js/custom.js"></script> --}}
	
	<!-- Script For Multiple Languages -->
	{{-- <script src="vendor/i18n/i18n.js"></script>
	<script src="js/translator.js"></script> --}}
	<script>
		(function () {
			const loginForm = document.getElementById('login-form');
			const submitButton = loginForm?.querySelector('button[type="submit"]');

			if (!loginForm || typeof Swal === 'undefined') {
				return;
			}

			loginForm.addEventListener('submit', async function (event) {
				event.preventDefault();

				if (submitButton) {
					submitButton.disabled = true;
				}

				try {
					const response = await fetch(loginForm.action, {
						method: 'POST',
						headers: {
							'Accept': 'application/json',
							'X-Requested-With': 'XMLHttpRequest',
						},
						credentials: 'same-origin',
						body: new FormData(loginForm),
					});

					if (response.ok) {
						const payload = await response.json();

						await Swal.fire({
							icon: 'success',
							title: 'Login Berhasil',
							text: payload.message ?? 'Anda akan diarahkan ke dashboard.',
							timer: 1800,
							timerProgressBar: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
							confirmButtonText: 'OK',
						});

						window.location.href = payload.redirect ?? "/";
						return;
					}

					let message = 'Email atau password tidak sesuai.';

					if (response.status === 422) {
						const payload = await response.json();
						const firstErrorGroup = payload?.errors ? Object.values(payload.errors)[0] : null;

						if (Array.isArray(firstErrorGroup) && firstErrorGroup.length > 0) {
							message = String(firstErrorGroup[0]);
						}
					}

					await Swal.fire({
						icon: 'error',
						title: 'Login Gagal',
						text: message,
						confirmButtonText: 'Coba Lagi',
					});
				} catch (error) {
					await Swal.fire({
						icon: 'error',
						title: 'Terjadi Kesalahan',
						text: 'Tidak dapat memproses login saat ini.',
						confirmButtonText: 'Tutup',
					});
				} finally {
					if (submitButton) {
						submitButton.disabled = false;
					}
				}
			});
		})();
	</script>
	
</body>
</html>
