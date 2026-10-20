@include('layouts.mainhead')

<body>

    @php
        $isSuperAdministratorLogoShift = auth()->user()?->isSuperAdministrator() ?? false;
    @endphp

	<div id="preloader">
		<div class="sk-three-bounce">
			<div class="sk-child sk-bounce1"></div>
			<div class="sk-child sk-bounce2"></div>
			<div class="sk-child sk-bounce3"></div>
		</div>
	</div>

    <!-- Start - Main Wrapper -->
    <div id="main-wrapper" class="{{ $isSuperAdministratorLogoShift ? 'superuser-logo-shift' : '' }}">

        <!-- Start - Nav header -->
         <div class="nav-header">
            <a href="{{ url('/') }}" class="brand-logo" aria-label="{{ $brandName ?? 'Andalan Bersama Group' }}">
                <img class="logo-mobile brand-logo-icon" src="{{ $brandLogoUrl ?? asset('images/images.png') }}" alt="{{ $brandName ?? 'Andalan Bersama Group' }} Icon" width="48">
                <span class="logo-desktop brand-logo-desktop">
                    <img class="brand-logo-icon" src="{{ $brandLogoUrl ?? asset('images/images.png') }}" alt="{{ $brandName ?? 'Andalan Bersama Group' }} Icon">
                    <span class="brand-logo-text">{{ $brandName ?? 'Dev' }}</span>
                </span>
            </a>

            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!-- End - Nav header -->
        @include('layouts.header')

        @include('layouts.sidebar')

        <main class="content-body">
			<div class="container-fluid">

                @yield('content')

            </div>
       	</main>

		@include('layouts.footer')

   </div>
    <!-- End - Main Wrapper -->
	@include('layouts.commonjs')

	@yield('script')
</body>

</html>
