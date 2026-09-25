@php
    $active = $active ?? 'inbox';
    $unreadCount = $unreadCount ?? 0;
    $inboxIsActive = in_array($active, ['inbox', 'read'], true);
@endphp

<div class="col-xxl-2 col-xl-3 col-lg-4 email-left-body">
    <div class="email-left-column dz-scroll" id="email-left">
        <div class="mb-4">
            <a href="{{ route('applicant.email.compose') }}" class="btn btn-primary w-100">
                <i class="fa-solid fa-plus"></i> Compose
            </a>
        </div>
        <div class="mail-list-group">
            <a href="{{ route('applicant.email.inbox') }}" class="list-group-item {{ $inboxIsActive ? 'active' : '' }}">
                <i class="fa-regular fa-envelope"></i> Inbox
                <span class="badge badge-purple badge-sm float-end rounded">{{ $unreadCount }}</span>
            </a>
            <a class="list-group-item">
                <i class="fa-regular fa-paper-plane"></i> Sent
            </a>
            <a class="list-group-item">
                <i class="fa-regular fa-star"></i> Favorite
            </a>
            <a class="list-group-item">
                <i class="fa-regular fa-file"></i> Draft
            </a>
            <a class="list-group-item">
                <i class="fa-solid fa-tag"></i> Important
            </a>
            <a class="list-group-item">
                <i class="fa-regular fa-clock"></i> Scheduled
            </a>
            <a class="list-group-item">
                <i class="fa-solid fa-angle-down"></i> Move
            </a>
        </div>
        <form method="POST" action="{{ route('applicant.email.logout') }}" class="px-3 mt-4">
            @csrf
            <div class="small text-muted mb-2">Login sebagai</div>
            <div class="fw-semibold text-break mb-3">{{ $account->email }}</div>
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Keluar Email</button>
        </form>
    </div>
</div>
