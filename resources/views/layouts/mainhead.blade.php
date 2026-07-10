<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $documentTitle = ($brandName ?? 'Dev').' - Siap';
    @endphp

	<!-- Title -->
	<base href="{{ asset('assets') }}/">
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

    @php
        $assetVersion = static function (string $path): int {
            $assetPath = public_path('assets/' . ltrim($path, '/'));

            return file_exists($assetPath) ? filemtime($assetPath) : time();
        };
    @endphp

	<!-- All Required CSS -->
	<link href="vendor/owl-carousel/owl.carousel.css?v={{ $assetVersion('vendor/owl-carousel/owl.carousel.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

	<!-- Start - Basic CSS -->
    <link href="vendor/metismenu/dist/metisMenu.min.css?v={{ $assetVersion('vendor/metismenu/dist/metisMenu.min.css') }}" rel="stylesheet">
    <link rel="preload" href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css?v={{ $assetVersion('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css?v={{ $assetVersion('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet"></noscript>
    <link rel="preload" href="icons/bootstrap-icons/font/bootstrap-icons.css?v={{ $assetVersion('icons/bootstrap-icons/font/bootstrap-icons.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="icons/bootstrap-icons/font/bootstrap-icons.css?v={{ $assetVersion('icons/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet"></noscript>
    <link href="icons/font-awesome/css/all.min.css?v={{ $assetVersion('icons/font-awesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="preload" href="icons/line-awesome/css/line-awesome.min.css?v={{ $assetVersion('icons/line-awesome/css/line-awesome.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="icons/line-awesome/css/line-awesome.min.css?v={{ $assetVersion('icons/line-awesome/css/line-awesome.min.css') }}" rel="stylesheet"></noscript>
    <link rel="preload" href="icons/avasta/css/style.css?v={{ $assetVersion('icons/avasta/css/style.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="icons/avasta/css/style.css?v={{ $assetVersion('icons/avasta/css/style.css') }}" rel="stylesheet"></noscript>
    <link rel="preload" href="icons/flaticon/flaticon.css?v={{ $assetVersion('icons/flaticon/flaticon.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="icons/flaticon/flaticon.css?v={{ $assetVersion('icons/flaticon/flaticon.css') }}" rel="stylesheet"></noscript>
    <link rel="preload" href="icons/flaticon-1/font/flaticon-1.css?v={{ $assetVersion('icons/flaticon-1/font/flaticon-1.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="icons/flaticon-1/font/flaticon-1.css?v={{ $assetVersion('icons/flaticon-1/font/flaticon-1.css') }}" rel="stylesheet"></noscript>
    <!-- End - Basic CSS -->

	<!-- Start - Switcher CSS -->
	<link class="main-switcher" rel="preload" href="css/switcher.css?v={{ $assetVersion('css/switcher.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link class="main-switcher" href="css/switcher.css?v={{ $assetVersion('css/switcher.css') }}" rel="stylesheet"></noscript>
	<!-- End - Switcher CSS -->
	<!-- End - All Required css -->
    <!-- All Required CSS -->
	<link rel="preload" href="vendor/daterangepicker/daterangepicker.css?v={{ $assetVersion('vendor/daterangepicker/daterangepicker.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="vendor/daterangepicker/daterangepicker.css?v={{ $assetVersion('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet"></noscript>

	<!-- Start - Style Css -->
	<link class="main-plugins" rel="preload" href="css/plugins.css?v={{ $assetVersion('css/plugins.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link class="main-plugins" href="css/plugins.css?v={{ $assetVersion('css/plugins.css') }}" rel="stylesheet"></noscript>
	<link class="main-css" href="css/style.css?v={{ $assetVersion('css/style.css') }}" rel="stylesheet">
	<!-- End - Style Css -->
    <style>
        .deznav {
            box-shadow: none !important;
            border-right: 0 !important;
        }

        .nav-label {
            border-top: 0 !important;
        }

        .nav-header .brand-logo .logo-mobile {
            display: none;
        }

        .nav-header .brand-logo .brand-logo-icon {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            object-fit: contain;
            object-position: center;
        }

        .nav-header .brand-logo .brand-logo-desktop {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            max-width: 100%;
            overflow: hidden;
        }

        .nav-header .brand-logo .brand-logo-text {
            color: #000;
            font-size: 0.8125rem;
            font-weight: 800;
            letter-spacing: 0;
            line-height: 1.15;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-transform: uppercase;
        }

        #main-wrapper.menu-toggle .nav-header .brand-logo .logo-mobile {
            display: block;
        }

        #main-wrapper.menu-toggle .nav-header .brand-logo .logo-desktop {
            display: none;
        }

        #main-wrapper.menu-toggle .deznav .slimScrollDiv,
        #main-wrapper.menu-toggle .deznav .deznav-scroll {
            height: 100% !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        @media only screen and (max-width: 74.9375rem) {
            #main-wrapper.menu-toggle .deznav {
                height: 100dvh;
            }

            #main-wrapper.menu-toggle .deznav .slimScrollDiv,
            #main-wrapper.menu-toggle .deznav .deznav-scroll {
                height: calc(100dvh - 80px) !important;
                padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 0.75rem);
                overscroll-behavior: contain;
                touch-action: pan-y;
            }
        }

        @media only screen and (max-width: 1199.98px) {
            .nav-header .brand-logo .logo-mobile {
                display: block;
            }

            .nav-header .brand-logo .brand-logo-desktop {
                display: none;
            }
        }

        @media only screen and (max-width: 1024px) {
            body:not([data-sidebar-style]) .deznav {
                left: -100% !important;
                transition: none !important;
            }

            body:not([data-sidebar-style]) #main-wrapper.menu-toggle .deznav {
                left: 0 !important;
                transition: none !important;
            }
        }

        @media only screen and (max-width: 47.9375rem) {
            .nav-label {
                margin: 0.75rem 0 0.25rem !important;
                padding: 1.25rem 1.75rem 0.625rem !important;
            }

            .deznav .metismenu a {
                padding-left: 1.75rem !important;
                padding-right: 1.75rem !important;
            }

            .deznav .metismenu > li > a {
                display: flex;
                align-items: center;
            }

            .deznav .metismenu > li a > i {
                width: 1.5rem;
                margin-right: 0.75rem !important;
                padding-right: 0 !important;
                text-align: center;
            }

            .deznav .metismenu ul a {
                padding-left: 4.25rem !important;
            }
        }
    </style>

	@yield('css')

</head>
