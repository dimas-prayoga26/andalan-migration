<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Title -->
	<base href="{{ asset('assets') }}/">
	<title>@yield('title')</title>

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

	<!-- All Required CSS -->
	<link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
	<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

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
            width: 48px;
            height: auto;
        }

        .nav-header .brand-logo .logo-desktop {
            display: block;
            height: auto;
            width: 210px;
            max-width: 100%;
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

            .nav-header .brand-logo .logo-desktop {
                display: none;
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
