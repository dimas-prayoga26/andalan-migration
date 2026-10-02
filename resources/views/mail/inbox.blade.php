@extends('layouts.main')

@section('title', 'Email')

@section('css')
    <style>
        .mail-list-clean .message {
            min-height: 56px;
        }

        .mail-list-clean .message .email-hader {
            position: absolute;
            left: 0;
            width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mail-list-clean .message .email-subject {
            left: 280px;
            right: 135px;
            margin: 0;
        }

        .mail-list-clean .message .email-date {
            right: 15px;
            width: 120px;
            overflow: hidden;
            text-align: right;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mail-search-form {
            max-width: 340px;
            min-width: 260px;
        }

        .mail-search-form .input-group-text {
            background: #f8f9fa;
        }

        @media (max-width: 991.98px) {
            .mail-list-clean .message .email-hader,
            .mail-list-clean .message .email-subject,
            .mail-list-clean .message .email-date {
                position: static;
                width: auto;
                text-align: left;
            }

            .mail-list-clean .message .col-mail-2 {
                display: block;
                padding: 8px 15px 8px 90px;
            }

            .mail-search-form {
                max-width: none;
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Email')

@section('content')

@php
    $folder = $folder ?? 'inbox';
    $isSentFolder = $folder === 'sent';
    $folderRoute = $isSentFolder ? 'applicant.email.sent' : 'applicant.email.inbox';
    $folderLabel = $isSentFolder ? 'Sent' : 'Inbox';
    $messages = $messages ?? [];
    $searchQuery = $searchQuery ?? '';
    $totalInboxCount = $totalInboxCount ?? count($messages);
    $unreadCount = $isSentFolder ? null : collect($messages)->where('unread', true)->count();
@endphp

@include('layouts.breadcrumb', [
    'title' => 'Email',
    'current' => $folderLabel,
    'homeRoute' => 'dashboard',
])

<form method="POST" action="{{ route('applicant.email.destroy') }}" id="mail-bulk-delete-form" class="d-none"
    data-delete-confirmation-form
    data-delete-title="Hapus Email"
    data-delete-message="Email yang dipilih akan dihapus. Lanjutkan?"
    data-delete-confirm-button="Hapus"
    data-delete-cancel-button="Batal">
    @csrf
    <input type="hidden" name="folder" value="{{ $folder }}">
</form>

<div class="card border-0 mb-0 h-auto">
    <div class="card-body p-0">
        <div class="row gx-0">
            @include('mail.partials.sidebar', ['active' => $folder, 'unreadCount' => $unreadCount])

            <div class="col-xxl-10 col-xl-9 col-lg-8">
                <div class="email-right-column check-wrapper">
                    @include('partials.swal-alert', ['type' => 'success', 'message' => session('mail_status')])

                    @if ($errors->any())
                        @include('partials.swal-alert', ['type' => 'error', 'message' => implode("\n", $errors->all())])
                    @endif

                    <div class="d-sm-flex align-items-center justify-content-between gap-3 flex-wrap px-3 border-bottom">
                        <div class="d-flex justify-content-between py-3 py-sm-2 order-2 order-sm-3">
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-square btn-sm tp-btn-light btn-primary"><i class="fa fa-archive"></i></button>
                                <button type="button" class="btn btn-square btn-sm tp-btn-light btn-primary"><i class="fa fa-exclamation-circle"></i></button>
                                <button type="submit" form="mail-bulk-delete-form" class="btn btn-square btn-sm tp-btn-light btn-danger" title="Hapus email terpilih" data-mail-bulk-delete disabled><i class="fa fa-trash"></i></button>
                                <button type="button" class="btn btn-square btn-sm tp-btn-light btn-warning"><i class="fa fa-folder"></i></button>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-square btn-sm tp-btn-light btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="javascript:void(0);">Mark as Unread</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);">Add to Tasks</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);">Add Star</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);">Mute</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="d-block d-lg-none">
                                <a class="mobile-email-panel btn btn-square btn-sm tp-btn-light btn-primary">
                                    <i class="fa-solid fa-list-ul"></i>
                                </a>
                            </div>
                        </div>
                        <div class="d-flex order-1">
                            <div class="form-check custom-checkbox align-self-center mt-2">
                                <input type="checkbox" class="form-check-input check-all">
                            </div>
                            <ul class="nav nav-underline ms-2 ms-sm-3 gap-3" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-3 px-0 border-3 active" data-bs-toggle="pill" data-bs-target="#pills-important" type="button" role="tab" aria-selected="true">
                                        <i class="fa-regular fa-envelope me-1"></i> Important
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-3 px-0 border-3" data-bs-toggle="pill" data-bs-target="#pills-socials" type="button" role="tab" aria-selected="false">
                                        <i class="fa-regular fa-user me-1"></i> Socials
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-3 px-0 border-3" data-bs-toggle="pill" data-bs-target="#pills-promotion" type="button" role="tab" aria-selected="false">
                                        <i class="fa-solid fa-ticket me-1"></i> Promotion
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <form method="GET" action="{{ route($folderRoute) }}" class="mail-search-form order-3 order-sm-2 flex-grow-1 py-2 ms-sm-auto">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0">
                                    <i class="fa fa-search"></i>
                                </span>
                                <input type="search" name="search" value="{{ $searchQuery }}" class="form-control border-start-0" placeholder="Search email" aria-label="Search email">
                                @if ($searchQuery !== '')
                                    <a href="{{ route($folderRoute) }}" class="btn btn-outline-secondary" aria-label="Clear search">
                                        <i class="fa fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-important" role="tabpanel">
                            <div class="email-list mail-list-clean dz-scroll" id="emails">
                                @if ($inboxError)
                                    <div class="p-4">
                                        @include('partials.swal-alert', ['type' => 'warning', 'message' => $inboxError])
                                    </div>
                                @endif

                                @forelse ($messages as $mail)
                                    @php
                                        $readRouteParameters = $isSentFolder ? ['uid' => $mail['uid'], 'folder' => 'sent'] : ['uid' => $mail['uid']];
                                    @endphp
                                    <div class="message {{ $mail['unread'] ? 'unread' : '' }}">
                                        <div class="message-single">
                                            <div class="email-checkbox form-check custom-checkbox">
                                                <input type="checkbox" name="uids[]" value="{{ $mail['uid'] }}" form="mail-bulk-delete-form" class="form-check-input check-input" aria-label="Pilih email">
                                            </div>
                                            <label class="bookmark-btn">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <a href="{{ route('applicant.email.read', $readRouteParameters) }}" class="col-mail col-mail-2">
                                            <div class="email-hader">{{ $isSentFolder ? 'To: '.($mail['to'] ?: '-') : $mail['from'] }}</div>
                                            <div class="email-subject">
                                                {{ $mail['subject'] }} <span>{{ $mail['date'] }}</span>
                                            </div>
                                            <div class="email-date">{{ $mail['time'] }}</div>
                                        </a>
                                        <div class="on-hover">
                                            <a><i class="fa fa-archive"></i></a>
                                            <a class="ms-2"><i class="fa-regular fa-clock"></i></a>
                                            <form method="POST" action="{{ route('applicant.email.destroy') }}" class="d-inline ms-2"
                                                data-delete-confirmation-form
                                                data-delete-title="Hapus Email"
                                                data-delete-message="Hapus email &quot;{{ $mail['subject'] }}&quot;?"
                                                data-delete-confirm-button="Hapus"
                                                data-delete-cancel-button="Batal">
                                                @csrf
                                                <input type="hidden" name="folder" value="{{ $folder }}">
                                                <input type="hidden" name="uids[]" value="{{ $mail['uid'] }}">
                                                <button type="submit" class="btn btn-link p-0 text-danger" title="Hapus email"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    @if (! $inboxError)
                                        <div class="p-4 text-center text-muted">
                                            @if ($searchQuery !== '')
                                                Tidak ada email yang cocok dengan pencarian.
                                            @else
                                                {{ $isSentFolder ? 'Belum ada email terkirim.' : 'Inbox kosong.' }}
                                            @endif
                                        </div>
                                    @endif
                                @endforelse
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-socials" role="tabpanel">
                            <div class="email-list mail-list-clean dz-scroll">
                                <div class="message">
                                    <a href="{{ route('applicant.email.inbox') }}" class="col-mail col-mail-2">
                                        <div class="email-hader">Socials</div>
                                        <div class="email-subject">Belum ada email social.</div>
                                        <div class="email-date">Now</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-promotion" role="tabpanel">
                            <div class="email-list mail-list-clean dz-scroll">
                                <div class="message">
                                    <a href="{{ route('applicant.email.inbox') }}" class="col-mail col-mail-2">
                                        <div class="email-hader">Promotion</div>
                                        <div class="email-subject">Belum ada email promotion.</div>
                                        <div class="email-date">Now</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center bg-light px-3 py-2">
                        <small>
                            @if ($searchQuery !== '')
                                Showing {{ count($messages) }} result for "{{ $searchQuery }}" from {{ $totalInboxCount }} {{ strtolower($folderLabel) }} email
                            @else
                                Showing {{ count($messages) }} {{ strtolower($folderLabel) }} email
                            @endif
                        </small>
                        <nav aria-label="Email Inbox Pagination">
                            <ul class="pagination pagination-gutter pagination-sm mb-0">
                                <li class="page-item page-indicator"><a class="page-link" href="javascript:void(0)"><i class="fa fa-angle-left"></i></a></li>
                                <li class="page-item active"><a class="page-link">1</a></li>
                                <li class="page-item"><a class="page-link">2</a></li>
                                <li class="page-item"><a class="page-link">3</a></li>
                                <li class="page-item page-indicator"><a class="page-link" href="javascript:void(0)"><i class="fa fa-angle-right"></i></a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@include('settings.partials.delete-confirmation-swal')

@section('script')
    @stack('scripts')
    <script>
        (function () {
            var bulkDeleteButton = document.querySelector('[data-mail-bulk-delete]');

            if (!bulkDeleteButton) {
                return;
            }

            function syncBulkDeleteButton() {
                bulkDeleteButton.disabled = document.querySelectorAll('input[name="uids[]"][form="mail-bulk-delete-form"]:checked').length === 0;
            }

            document.addEventListener('change', function (event) {
                if (event.target.matches('.check-input, .check-all')) {
                    window.setTimeout(syncBulkDeleteButton, 0);
                }
            });

            syncBulkDeleteButton();
        })();
    </script>
@endsection
