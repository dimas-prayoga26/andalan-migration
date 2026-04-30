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
            width: 42px !important;
            height: 42px !important;
            max-width: 42px !important;
            max-height: 42px !important;
            object-fit: contain;
        }

        .nav-header .brand-logo {
            display: flex !important;
            align-items: center;
            justify-content: flex-start !important;
            gap: 0.45rem;
            padding-left: 0.85rem !important;
            padding-right: 0.85rem !important;
        }

        .nav-header .brand-logo .logo-desktop {
            display: block;
            width: 70px !important;
            max-width: 70px !important;
            max-height: 70px !important;
            height: auto !important;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .nav-header .logo-company-name {
            display: block;
            flex: 1 1 auto;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 1.8rem;
            font-weight: 600;
            color: #000000;
            line-height: 1.2;
            margin-top: 10px;
        }

        #main-wrapper:not(.menu-toggle) .nav-header .brand-logo {
            justify-content: flex-start !important;
            padding-left: 0.7rem !important;
            padding-right: 0.5rem !important;
        }

        #main-wrapper:not(.menu-toggle)[data-sidebar-style=compact] .nav-header .brand-logo,
        #main-wrapper:not(.menu-toggle)[data-sidebar-style=mini] .nav-header .brand-logo {
            justify-content: flex-start !important;
            padding-left: 0.7rem !important;
            padding-right: 0.5rem !important;
        }

        #main-wrapper.superuser-logo-shift .nav-header {
            transition: all 0.2s ease !important;
        }

        #main-wrapper.superuser-logo-shift .nav-header .brand-logo {
            transition: all 0.2s ease !important;
        }

        #main-wrapper.superuser-logo-shift.menu-toggle .nav-header .brand-logo {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        #main-wrapper.menu-toggle .nav-header .brand-logo .logo-mobile {
            display: block;
        }

        #main-wrapper.menu-toggle .nav-header .brand-logo .logo-desktop {
            display: none;
        }

        #main-wrapper.menu-toggle .nav-header .brand-logo .logo-company-name {
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
            #main-wrapper:not(.superuser-logo-shift) .nav-header {
                transition: none !important;
            }

            #main-wrapper:not(.superuser-logo-shift) .nav-header .brand-logo,
            #main-wrapper:not(.superuser-logo-shift).menu-toggle .nav-header .brand-logo {
                justify-content: flex-start !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                gap: 0.35rem !important;
            }

            #main-wrapper.superuser-logo-shift:not(.menu-toggle) .nav-header .brand-logo {
                justify-content: flex-start !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                gap: 0.35rem !important;
            }

            #main-wrapper.superuser-logo-shift.menu-toggle .nav-header .brand-logo {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                gap: 0 !important;
            }

            .nav-header .brand-logo .logo-mobile {
                display: block;
                margin: 0 !important;
                transform: none !important;
            }

            .nav-header .brand-logo .logo-desktop {
                display: none;
            }

            .nav-header .brand-logo .logo-company-name {
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
