<div class="card h-auto px-md-2 pt-md-2">
    <div class="card-header py-0 d-flex flex-wrap justify-content-between align-items-center mx-sm-4 px-0">
        <ul class="nav nav-underline gap-3 nav-scroll nav-scroll-auto-xl px-3 px-sm-0" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="{{ route('pic-attendance.attendance') }}" class="nav-link py-3 px-1 border-3 {{ request()->routeIs('pic-attendance.attendance*') ? 'active' : '' }}">Attendance</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('pic-attendance.leave') }}" class="nav-link py-3 px-1 border-3 {{ request()->routeIs('pic-attendance.leave*') ? 'active' : '' }}">Leave</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('pic-attendance.overtime') }}" class="nav-link py-3 px-1 border-3 {{ request()->routeIs('pic-attendance.overtime*') ? 'active' : '' }}">Overtime</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('pic-attendance.task') }}" class="nav-link py-3 px-1 border-3 {{ request()->routeIs('pic-attendance.task*') ? 'active' : '' }}">Task</a>
            </li>
        </ul>
    </div>
</div>
