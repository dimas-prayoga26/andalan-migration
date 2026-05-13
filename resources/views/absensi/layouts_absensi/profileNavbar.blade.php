<!-- Start - Profile Navigation -->
<div class="card-footer py-0 d-flex flex-wrap justify-content-between align-items-center mx-sm-4 px-0">
    <ul class="nav nav-underline gap-3 nav-scroll nav-scroll-auto-xl px-3 px-sm-0 absensi-tabs" id="tabMyProfileBottom" role="tablist">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link py-3 px-1 border-3 absensi-tab-btn" aria-selected="false">Overview</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('absensi') }}" class="nav-link py-3 px-1 border-3 absensi-tab-btn {{ request()->routeIs('absensi') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi') ? 'true' : 'false' }}">Presensi</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('absensi.izin') }}" class="nav-link py-3 px-1 border-3 absensi-tab-btn {{ request()->routeIs('absensi.izin*') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi.izin*') ? 'true' : 'false' }}">Izin / Cuti</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link py-3 px-1 border-3 absensi-tab-btn" aria-selected="false">List</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('absensi.dinas') }}" class="nav-link py-3 px-1 border-3 absensi-tab-btn {{ request()->routeIs('absensi.dinas') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi.dinas') ? 'true' : 'false' }}">Perjalanan Dinas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" data-href="{{ route('absensi.lembur') }}" class="nav-link py-3 px-1 border-3 absensi-tab-btn {{ request()->routeIs('absensi.lembur') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi.lembur') ? 'true' : 'false' }}">Lembur</button>
        </li>
    </ul>
</div>
<!-- End - Profile Navigation -->