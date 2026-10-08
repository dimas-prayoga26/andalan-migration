@php
    $active = $active ?? 'inbox';
    $unreadCount = $unreadCount ?? null;
    $mailRoutePrefix = $mailRoutePrefix ?? 'applicant.email';
    $inboxIsActive = in_array($active, ['inbox', 'read'], true);
    $sentIsActive = $active === 'sent';
@endphp

<div class="col-xxl-2 col-xl-3 col-lg-4 email-left-body">
    <div class="email-left-column dz-scroll" id="email-left">
        <div class="mb-4">
            <a href="{{ route($mailRoutePrefix.'.compose') }}" class="btn btn-primary w-100">
                <i class="fa-solid fa-plus"></i> Compose
            </a>
        </div>
        <div class="mail-list-group">
            <a href="{{ route($mailRoutePrefix.'.inbox') }}" class="list-group-item {{ $inboxIsActive ? 'active' : '' }}">
                <i class="fa-regular fa-envelope"></i> Inbox
                @if ($unreadCount !== null)
                    <span class="badge badge-purple badge-sm float-end rounded">{{ $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route($mailRoutePrefix.'.sent') }}" class="list-group-item {{ $sentIsActive ? 'active' : '' }}">
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
    </div>
</div>
