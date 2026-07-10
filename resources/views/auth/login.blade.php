<!DOCTYPE html>
<html lang="en">

<head>
    @php
        $assetVersion = static function (string $path): int {
            $assetPath = public_path('assets/' . ltrim($path, '/'));

            return file_exists($assetPath) ? filemtime($assetPath) : time();
        };
        $documentTitle = ($brandName ?? 'Dev').' - Siap';
    @endphp
    
	<!-- Title -->
	<title>{{ $documentTitle }}</title>

	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="DexignZone">
	<meta name="robots" content="">

	<meta name="keywords" content="admin, admin dashboard, admin template, bootstrap, bootstrap 5, bootstrap 5 admin template, fitness, fitness admin, modern, responsive admin dashboard, sales dashboard, sass, ui kit, web app">
	<meta name="description" content="Discover Gymove, the ultimate fitness solution that is designed to help you achieve a healthier lifestyle with its cutting-edge features and personalized programs. Gymove is a fully mobile-responsive admin dashboard template that provides the perfect blend of exercise, nutrition, and motivation. Begin your fitness journey today with Gymove and visit DexignZone for more information.">

	<meta property="og:title" content="{{ $documentTitle }}">
	<meta property="og:description" content="Discover Gymove, the ultimate fitness solution that is designed to help you achieve a healthier lifestyle with its cutting-edge features and personalized programs. Gymove is a fully mobile-responsive admin dashboard template that provides the perfect blend of exercise, nutrition, and motivation. Begin your fitness journey today with Gymove and visit DexignZone for more information.">
	<meta property="og:image" content="https://gymove.dexignzone.com/xhtml/social-image.avif">
	<meta name="format-detection" content="telephone=no">

	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Favicon icon -->
	<link rel="shortcut icon" type="image/x-icon" href="{{ $brandLogoUrl ?? asset('images/images.png') }}">
    
	<!-- Start - Basic CSS -->
    <link href="{{ asset('assets/vendor/metismenu/dist/metisMenu.min.css') }}?v={{ $assetVersion('vendor/metismenu/dist/metisMenu.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}?v={{ $assetVersion('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/chartist/css/chartist.min.css') }}?v={{ $assetVersion('vendor/chartist/css/chartist.min.css') }}">
    <!-- End - Basic CSS -->
    
	<!-- Start - Switcher CSS -->
	<link class="main-switcher" href="{{ asset('assets/css/switcher.css') }}?v={{ $assetVersion('css/switcher.css') }}" rel="stylesheet">
	<!-- End - Switcher CSS -->

	<!-- Start - Style Css -->
	<link class="main-plugins" href="{{ asset('assets/css/plugins.css') }}?v={{ $assetVersion('css/plugins.css') }}" rel="stylesheet">
	<link class="main-css" href="{{ asset('assets/css/style.css') }}?v={{ $assetVersion('css/style.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/icons/font-awesome/css/all.min.css') }}?v={{ $assetVersion('icons/font-awesome/css/all.min.css') }}" rel="stylesheet">
	<!-- End - Style Css -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		html,
		body {
			height: 100%;
			overflow: hidden;
		}

		.authincation .card {
			max-height: calc(100vh - 2rem);
			overflow-y: auto;
		}

		.show-pass {
			width: 2.5rem;
			height: 2.5rem;
			border: 0;
			background: transparent;
			color: var(--bs-body-color);
			cursor: pointer;
			user-select: none;
		}

		.show-pass:focus-visible {
			outline: 2px solid var(--bs-primary);
			outline-offset: 2px;
		}

		.show-pass .hide {
			display: none;
		}

		.show-pass.is-visible .show {
			display: none;
		}

		.show-pass.is-visible .hide {
			display: inline-flex;
		}
	</style>
    
</head>
<body class="vh-100">

	<!-- Start - Authincation Section -->
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
					<div class="card p-5 shadow-lg">
						<div class="text-center mb-3">
							<a href="{{ route('login') }}" class="brand-logo d-inline-flex justify-content-center" aria-label="{{ $brandName ?? 'Andalan Bersama Group' }}">
								<img src="{{ $brandLogoUrl ?? asset('images/images.png') }}" alt="{{ $brandName ?? 'Andalan Bersama Group' }} Logo" style="max-width: 220px; width: 100%; height: 84px; object-fit: contain;">
							</a>
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
									<input type="password" name="password" autocomplete="current-password" class="form-control form-control-lg dz-password pe-5" placeholder="Enter your password" required>
									<button type="button" class="show-pass position-absolute top-50 end-0 me-2 translate-middle-y" aria-label="Tampilkan password" aria-pressed="false" title="Tampilkan password">
										<span class="show" aria-hidden="true"><i class="fa-solid fa-eye"></i></span>
										<span class="hide" aria-hidden="true"><i class="fa-solid fa-eye-slash"></i></span>
									</button>
								</div>
							</div>
							<div class="form-row d-flex justify-content-between mt-4 mb-2 flex-wrap">
								<div class="form-group mb-3">
								   <div class="custom-control custom-checkbox ms-1">
										<input type="checkbox" name="remember" value="1" class="form-check-input" id="basic_checkbox_1" {{ old('remember') ? 'checked' : '' }}>
										<label class="form-check-label" for="basic_checkbox_1">Remember me</label>
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
	<script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}?v={{ $assetVersion('vendor/jquery/dist/jquery.min.js') }}"></script>
	<script src="{{ asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}?v={{ $assetVersion('vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
	<script src="{{ asset('assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}?v={{ $assetVersion('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
	<script src="{{ asset('assets/vendor/@yaireo/tagify/dist/tagify.js') }}?v={{ $assetVersion('vendor/@yaireo/tagify/dist/tagify.js') }}"></script>
	<script src="{{ asset('assets/vendor/metismenu/dist/metisMenu.min.js') }}?v={{ $assetVersion('vendor/metismenu/dist/metisMenu.min.js') }}"></script>
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
			const passwordInput = loginForm?.querySelector('input[name="password"]');
			const showPassButton = loginForm?.querySelector('.show-pass');

			if (!loginForm) {
				return;
			}

			if (passwordInput && showPassButton) {
				showPassButton.addEventListener('click', function () {
					const isCurrentlyVisible = passwordInput.type === 'text';
					passwordInput.type = isCurrentlyVisible ? 'password' : 'text';
					showPassButton.classList.toggle('is-visible', !isCurrentlyVisible);
					showPassButton.setAttribute('aria-pressed', String(!isCurrentlyVisible));
					showPassButton.setAttribute('aria-label', isCurrentlyVisible ? 'Tampilkan password' : 'Sembunyikan password');
					showPassButton.setAttribute('title', isCurrentlyVisible ? 'Tampilkan password' : 'Sembunyikan password');
				});
			}

			if (typeof Swal === 'undefined') {
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

						window.location.href = payload.redirect ?? "{{ route('dashboard') }}";
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
