<div class="card h-auto px-md-2 pt-md-2" >
    <!-- Start - Profile Navigation -->
    <div class="card-header py-0 d-flex flex-wrap justify-content-between align-items-center mx-sm-4 px-0">
        <ul class="nav nav-underline gap-3 nav-scroll nav-scroll-auto-xl px-3 px-sm-0" id="tabMyProfileBottom" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="{{ route('admin-attendance.overview') }}" class="nav-link py-3 px-1 border-3 {{ request()->routeIs('admin-attendance.overview') ? 'active' : '' }}">Overview</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('admin-attendance.recap') }}" class="nav-link py-3 px-1 border-3 {{ request()->routeIs('admin-attendance.recap*') ? 'active' : '' }}">Attendance</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="hr-attendance-leave.html" class="nav-link py-3 px-1 border-3">Leave</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="hr-attendance-business-trip.html" class="nav-link py-3 px-1 border-3">Business Trip</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="hr-attendance-overtime.html" class="nav-link py-3 px-1 border-3">Overtime</a>
            </li>
        </ul>
    </div>
    <!-- End - Profile Navigation -->
</div>
