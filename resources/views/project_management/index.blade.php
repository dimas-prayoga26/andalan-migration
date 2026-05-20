@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
    <!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="{{ asset('assets-workload/images/favicon.png') }}" />
	<link href="{{ asset('assets-workload/vendor/jquery-nice-select/css/nice-select.css') }}" rel="stylesheet">
	<link href="{{ asset('assets-workload/vendor/owl-carousel/owl.carousel.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('assets-workload/vendor/nouislider/nouislider.min.css') }}">
	
	<!-- Style css -->
    {{-- <link href="{{ asset('assets-workload/css/style.css') }}" rel="stylesheet"> --}}
    <style>
        .project-kanban-page .kanban-bx {
            display: flex;
            width: 100%;
            overflow-x: auto;
            flex-wrap: nowrap;
            align-items: flex-start;
            gap: 1.25rem;
            padding-bottom: 0.5rem;
            margin-left: 0;
            margin-right: 0;
        }

        .project-kanban-page .kanban-bx .col {
            width: 360px;
            min-width: 360px;
            flex-grow: 0;
            flex-shrink: 0;
            flex-basis: 360px;
            padding-left: 0;
            padding-right: 0;
        }

        .project-kanban-page .kanban-bx .col .card {
            height: auto;
            cursor: grab;
            will-change: transform;
        }

        .project-kanban-page .kanban-bx .col .card.draggable-source--is-dragging {
            cursor: grabbing;
            opacity: 0.45;
        }

        .project-kanban-page .draggable.card {
            transition: none;
        }

        .project-kanban-page .kanban-user .users {
            display: flex;
        }

        .project-kanban-page .kanban-user .users li {
            margin-right: -10px;
        }

        .project-kanban-page .kanban-user .users li img {
            border-radius: 32px;
            height: 32px;
            width: 32px;
            border: 2px solid #fff;
        }

        .project-kanban-page .dropzoneContainer {
            min-height: 96px;
        }

        .project-kanban-page .dropzoneContainer > .sub-card {
            margin-bottom: 1.5rem;
            min-height: 32px;
        }

        .project-kanban-page .kanban-empty-state {
            min-height: 235px;
            border: 1px dashed #d8dde8;
            border-radius: 1rem;
            background: rgba(245, 248, 253, 0.5);
        }

        .project-kanban-page .dropzoneContainer:has(.draggable-handle) .kanban-empty-state {
            display: none;
        }

        .project-kanban-page .kanban-bx::-webkit-scrollbar {
            background-color: #ececec;
            width: 8px;
            height: 8px;
        }

        .project-kanban-page .kanban-bx::-webkit-scrollbar-thumb {
            background-color: #7e7e7e;
            border-radius: 10px;
        }

        .project-kanban-page .draggable-mirror {
            z-index: 1060 !important;
            pointer-events: none;
        }

        @media (max-width: 767.98px) {
            .project-kanban-page .kanban-bx {
                gap: 0.75rem;
                padding: 0 0.75rem 0.75rem;
                scroll-snap-type: x proximity;
                scroll-padding-left: 0.75rem;
                overscroll-behavior-x: contain;
            }

            .project-kanban-page .kanban-bx .col {
                width: 82vw;
                min-width: 82vw;
                max-width: 82vw;
                scroll-snap-align: start;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Reports')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Projects</h1></li>
            <li class="breadcrumb-item">
                <a href="index.html">
                    <svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Reports</li>
        </ol>
    </nav>
</div>
<!-- End - Page Title & Breadcrumb -->

<div class="tab-content d-flex flex-column project-kanban-page" id="tabContentMyProfileBottom" style="min-height: calc(100vh - 310px);">
    <div class="row">

        <div class="row kanban-bx">
			<div class="col">
				<div class="kanbanPreview-bx">
					<div class="draggable-zone dropzoneContainer">
						<div class="sub-card align-items-center d-flex justify-content-between mb-4">
							<div>
								<h4 class="fs-20 mb-0 font-w600">To-Do List (<span class="totalCount">24</span>)</h4>
							</div>
							<div class="plus-bx">
								<a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="sub-title">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#FFA7D7"/>
										</svg>
										Deisgner
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Create wireframe for landing page phase 1</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-design progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-warning">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#FFCF6D"/>
										</svg>
										Important
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Visual Graphic for Presentation to Client</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-warning progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic222.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-success">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#09BD3C"/>
										</svg>

										Databse
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Setup database for create API connection</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-success progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic222.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="kanbanPreview-bx">
					<div class="draggable-zone dropzoneContainer">
						<div class="sub-card align-items-center d-flex justify-content-between mb-4">
							<div>
								<h4 class="fs-20 mb-0 font-w600">On Progress(<span class="totalCount">2</span>)</h4>
							</div>
							<div class="plus-bx">
								<a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-sucess">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#09BD3C"/>
										</svg>
										UPDATE
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Update information in footer section</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-success progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-info">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#D653C1"/>
										</svg>
										Video
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Update information in footer section</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-info progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="kanbanPreview-bx">
					<div class="draggable-zone dropzoneContainer">
						<div class="sub-card align-items-center d-flex justify-content-between mb-4">
							<div>
								<h4 class="fs-20 mb-0 font-w600">Done(<span class="totalCount">3</span>)</h4>
							</div>
							<div class="plus-bx">
								<a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-danger">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#FC2E53"/>
										</svg>
										BUGS FIXING
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Update information in footer section</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-danger progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-danger">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#FC2E53"/>
										</svg>
										BUGS FIXING
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Update information in footer section</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-danger progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="sub-title">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#FFA7D7"/>
										</svg>
										HTML
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Create wireframe for landing page phase 1</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-design progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="kanbanPreview-bx">
					<div class="draggable-zone dropzoneContainer">
						<div class="sub-card align-items-center d-flex justify-content-between mb-4">
							<div>
								<h4 class="fs-20 mb-0 font-w600">Done(<span class="totalCount">3</span>)</h4>
							</div>
							<div class="plus-bx">
								<a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
							</div>
						</div>
                        <div class="kanban-empty-state"></div>
					</div>
				</div>
			</div>
			
			<div class="col">
				<div class="kanbanPreview-bx">
					<div class="draggable-zone dropzoneContainer">
						<div class="sub-card align-items-center d-flex justify-content-between mb-4">
							<div>
								<h4 class="fs-20 mb-0 font-w600">On Progress(<span class="totalCount">2</span>)</h4>
							</div>
							<div class="plus-bx">
								<a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-sucess">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#09BD3C"/>
										</svg>
										UPDATE
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Update information in footer section</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-success progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-info">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#D653C1"/>
										</svg>
										Video
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Update information in footer section</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-info progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="kanbanPreview-bx">
					<div class="draggable-zone dropzoneContainer">
						<div class="sub-card align-items-center d-flex justify-content-between mb-4">
							<div>
								<h4 class="fs-20 mb-0 font-w600">Done(<span class="totalCount">3</span>)</h4>
							</div>
							<div class="plus-bx">
								<a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-danger">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#FC2E53"/>
										</svg>
										BUGS FIXING
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Update information in footer section</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-danger progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="text-danger">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#FC2E53"/>
										</svg>
										BUGS FIXING
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Update information in footer section</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-danger progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
						<div class="card draggable-handle draggable">
							<div class="card-body">
								<div class="d-flex justify-content-between mb-2">
									<span class="sub-title">
										<svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="5" cy="5" r="5" fill="#FFA7D7"/>
										</svg>
										HTML
									</span>
									<div class="dropdown">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="3.5" cy="11.5" r="2.5" transform="rotate(-90 3.5 11.5)" fill="#717579"/>
												<circle cx="11.5" cy="11.5" r="2.5" transform="rotate(-90 11.5 11.5)" fill="#717579"/>
												<circle cx="19.5" cy="11.5" r="2.5" transform="rotate(-90 19.5 11.5)" fill="#717579"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0)">Delete</a>
											<a class="dropdown-item" href="javascript:void(0)">Edit</a>
										</div>
									</div>
								</div>
								<p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Create wireframe for landing page phase 1</a></p>
								<div class="progress default-progress my-4">
									<div class="progress-bar bg-design progress-animated" style="width: 45%; height:10px;" role="progressbar">
										<span class="sr-only">45% Complete</span>
									</div>
								</div>
								<div class="row justify-content-between align-items-center kanban-user">
									<ul class="users col-6">
										<li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
										<li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
									</ul>
									<div class="col-6 d-flex justify-content-end">
										<span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
        

    </div>
</div>

<!-- End - Content Body -->

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
        <!-- Required vendors -->
    <script src="{{ asset('assets-workload/vendor/global/global.min.js') }}"></script>
	<script src="{{ asset('assets-workload/vendor/jquery-nice-select/js/jquery.nice-select.min.js') }}"></script>
	
	<!-- Apex Chart -->
	<!-- Chart piety plugin files -->
   
	<!-- Dashboard 1 -->	
	<script src="{{ asset('assets-workload/vendor/draggable/draggable.js') }}"></script>
    <script>
        (function () {
            if (typeof window.Sortable === 'undefined' || typeof window.Sortable.default === 'undefined') {
                return;
            }

            var OriginalSortable = window.Sortable.default;
            var isMobileViewport = window.matchMedia('(max-width: 767.98px)').matches;
            var EDGE_THRESHOLD_PX = isMobileViewport ? 170 : 120;
            var MAX_SCROLL_SPEED_PX = isMobileViewport ? 32 : 24;

            function extractClientX(value) {
                if (!value) {
                    return null;
                }

                if (typeof value.clientX === 'number') {
                    return value.clientX;
                }

                if (value.touches && value.touches.length > 0 && typeof value.touches[0].clientX === 'number') {
                    return value.touches[0].clientX;
                }

                if (value.changedTouches && value.changedTouches.length > 0 && typeof value.changedTouches[0].clientX === 'number') {
                    return value.changedTouches[0].clientX;
                }

                return null;
            }

            function getClientXFromEvent(event) {
                var candidates = [
                    event && event.sensorEvent,
                    event && event.sensorEvent && event.sensorEvent.originalEvent,
                    event && event.data && event.data.sensorEvent,
                    event && event.data && event.data.sensorEvent && event.data.sensorEvent.originalEvent,
                    event && event.originalEvent,
                    event && event.data && event.data.originalEvent,
                    event
                ];

                for (var i = 0; i < candidates.length; i += 1) {
                    var clientX = extractClientX(candidates[i]);

                    if (typeof clientX === 'number') {
                        return clientX;
                    }
                }

                return null;
            }

            function attachKanbanAutoScroll(sortableInstance) {
                if (!sortableInstance || typeof sortableInstance.on !== 'function') {
                    return;
                }

                var board = document.querySelector('.project-kanban-page .kanban-bx');

                if (!board) {
                    return;
                }

                var isDragging = false;
                var latestClientX = null;
                var rafId = null;
                var onPointerMove = function (nativeEvent) {
                    if (!isDragging) {
                        return;
                    }

                    var clientX = extractClientX(nativeEvent);

                    if (typeof clientX === 'number') {
                        latestClientX = clientX;
                    }
                };

                function bindPointerListeners() {
                    window.addEventListener('mousemove', onPointerMove, { passive: true });
                    window.addEventListener('touchmove', onPointerMove, { passive: true });
                    window.addEventListener('pointermove', onPointerMove, { passive: true });
                }

                function unbindPointerListeners() {
                    window.removeEventListener('mousemove', onPointerMove);
                    window.removeEventListener('touchmove', onPointerMove);
                    window.removeEventListener('pointermove', onPointerMove);
                }

                function stopAutoScroll() {
                    isDragging = false;
                    latestClientX = null;
                    unbindPointerListeners();

                    if (rafId !== null) {
                        window.cancelAnimationFrame(rafId);
                        rafId = null;
                    }
                }

                function autoScrollTick() {
                    if (!isDragging) {
                        rafId = null;
                        return;
                    }

                    if (latestClientX === null) {
                        var dragMirror = document.querySelector('.draggable-mirror');

                        if (dragMirror) {
                            var mirrorRect = dragMirror.getBoundingClientRect();
                            latestClientX = mirrorRect.left + (mirrorRect.width / 2);
                        }
                    }

                    if (latestClientX === null) {
                        rafId = window.requestAnimationFrame(autoScrollTick);
                        return;
                    }

                    var boardRect = board.getBoundingClientRect();
                    var nextDelta = 0;

                    if (latestClientX < boardRect.left + EDGE_THRESHOLD_PX) {
                        var leftRatio = (boardRect.left + EDGE_THRESHOLD_PX - latestClientX) / EDGE_THRESHOLD_PX;
                        nextDelta = -Math.max(1, Math.round(leftRatio * MAX_SCROLL_SPEED_PX));
                    } else if (latestClientX > boardRect.right - EDGE_THRESHOLD_PX) {
                        var rightRatio = (latestClientX - (boardRect.right - EDGE_THRESHOLD_PX)) / EDGE_THRESHOLD_PX;
                        nextDelta = Math.max(1, Math.round(rightRatio * MAX_SCROLL_SPEED_PX));
                    }

                    if (nextDelta !== 0) {
                        board.scrollLeft += nextDelta;
                    }

                    rafId = window.requestAnimationFrame(autoScrollTick);
                }

                sortableInstance.on('drag:start', function (event) {
                    isDragging = true;
                    latestClientX = getClientXFromEvent(event);
                    bindPointerListeners();

                    if (rafId === null) {
                        rafId = window.requestAnimationFrame(autoScrollTick);
                    }
                });

                sortableInstance.on('drag:move', function (event) {
                    latestClientX = getClientXFromEvent(event);
                });

                sortableInstance.on('drag:stop', function () {
                    stopAutoScroll();
                });

                sortableInstance.on('sortable:stop', function () {
                    stopAutoScroll();
                });
            }

            window.Sortable.default = function (containers, options) {
                var sortableOptions = options || {};
                sortableOptions.mirror = Object.assign({}, sortableOptions.mirror || {}, {
                    appendTo: '.project-kanban-page .kanban-bx',
                    constrainDimensions: true
                });

                var sortableInstance = new OriginalSortable(containers, sortableOptions);
                attachKanbanAutoScroll(sortableInstance);

                return sortableInstance;
            };

            window.Sortable.default.prototype = OriginalSortable.prototype;
        })();
    </script>
    <script src="{{ asset('assets-workload/js/custom.min.js') }}"></script>
	<script src="{{ asset('assets-workload/js/dlabnav-init.js') }}"></script>
@endsection

