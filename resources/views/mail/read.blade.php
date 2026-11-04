@extends('layouts.main')

@section('title', ($mailFeatureTitle ?? 'Email').' Read')

@section('css')
    @include('mail.partials.account-selector-styles')
    <style>
        .mail-attachments {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 12px;
        }

        .mail-attachment-item {
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            padding: 12px;
        }

        .mail-attachment-preview {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            background: var(--bs-light);
        }

        .mail-reply-editor {
            border: 1px solid var(--bs-primary);
            border-radius: 8px;
            overflow: hidden;
        }

        .mail-reply-target {
            background: rgba(var(--bs-primary-rgb), .08);
            border-bottom: 1px solid rgba(var(--bs-primary-rgb), .2);
            padding: 12px 16px;
        }

        .mail-reply-textarea {
            border: 0;
            border-radius: 0;
            resize: vertical;
        }

        .mail-reply-textarea:focus {
            box-shadow: none;
        }

        .mail-selected-files {
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .mail-selected-file {
            align-items: center;
            display: flex;
            gap: 8px;
        }

        .mail-html-frame {
            background: #fff;
            border: 0;
            border-radius: 8px;
            display: block;
            height: 48px;
            width: 100%;
        }

        .mail-thread {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px 0;
        }

        /* Chat layout: messages from the opened account sit on the right, the other party on the left. */
        .mail-thread-item {
            display: flex;
            justify-content: flex-start;
        }

        .mail-thread-item.is-own {
            justify-content: flex-end;
        }

        .mail-thread-bubble {
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            max-width: 100%;
            overflow: hidden;
            width: 85%;
        }

        .mail-thread-item.is-own .mail-thread-bubble {
            border-color: rgba(var(--bs-primary-rgb), .3);
        }

        .mail-thread-toggle {
            align-items: flex-start;
            background: transparent;
            border: 0;
            display: flex;
            gap: 12px;
            padding: 12px 14px;
            text-align: left;
            width: 100%;
        }

        .mail-thread-item.is-own .mail-thread-toggle {
            flex-direction: row-reverse;
            text-align: right;
        }

        .mail-thread-item.is-own .mail-thread-open-link {
            text-align: right;
        }

        .mail-thread-heading {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-width: 0;
        }

        .mail-thread-sender {
            color: var(--bs-primary);
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .mail-thread-recipient,
        .mail-thread-snippet {
            color: #6b7280;
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mail-thread-toggle.collapsed .mail-thread-recipient,
        .mail-thread-toggle:not(.collapsed) .mail-thread-snippet {
            display: none;
        }

        .mail-thread-date {
            color: #6b7280;
            flex-shrink: 0;
            font-size: 13px;
            white-space: nowrap;
        }

        .mail-thread-body {
            padding: 12px 14px 14px;
        }

        @media (max-width: 575.98px) {
            .mail-thread-bubble {
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbarTitle', $mailFeatureTitle ?? 'Email')

@section('content')

@php
    $folder = $folder ?? 'inbox';
    $mailFeatureTitle = $mailFeatureTitle ?? 'Email';
    $mailRoutePrefix = $mailRoutePrefix ?? 'administration-finance.email';
    $isSentFolder = $folder === 'sent';
    $folderQuery = $isSentFolder ? ['folder' => 'sent'] : [];
@endphp

@include('layouts.breadcrumb', [
    'title' => $mailFeatureTitle,
    'current' => 'Read',
    'homeRoute' => 'dashboard',
])

@include('mail.partials.summary-cards')

@if ($message)
    <form method="POST" action="{{ route($mailRoutePrefix.'.destroy') }}" id="mail-delete-form" class="d-none"
        data-delete-confirmation-form
        data-delete-title="Hapus Email"
        data-delete-message="Email ini akan dihapus. Lanjutkan?"
        data-delete-confirm-button="Hapus"
        data-delete-cancel-button="Batal">
        @csrf
        <input type="hidden" name="folder" value="{{ $folder }}">
        <input type="hidden" name="uids[]" value="{{ $message['uid'] }}">
    </form>
@endif

<div class="card border-0 mb-0 h-auto">
    <div class="card-body p-0">
        <div class="row gx-0">
            @include('mail.partials.sidebar', ['active' => $isSentFolder ? 'sent' : 'read'])

            <div class="col-xxl-10 col-xl-9 col-lg-8">
                <div class="email-right-column">
                    <div class="d-flex justify-content-between p-3">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-square btn-sm tp-btn-light btn-primary"><i class="fa fa-archive"></i></button>
                            <button type="button" class="btn btn-square btn-sm tp-btn-light btn-primary"><i class="fa fa-exclamation-circle"></i></button>
                            <button type="submit" form="mail-delete-form" class="btn btn-square btn-sm tp-btn-light btn-danger" title="Hapus email" @disabled(! $message)><i class="fa fa-trash"></i></button>
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
                    @include('partials.swal-alert', ['type' => 'success', 'message' => session('mail_status')])

                    @if ($errors->any())
                        @include('partials.swal-alert', ['type' => 'error', 'message' => implode("\n", $errors->all())])
                    @endif

                    <div class="right-box-padding p-0">
                        <div class="read-wapper dz-scroll" id="read-content">
                            <div class="read-content">
                                <div class="pt-3 pb-3 d-sm-flex d-block justify-content-between align-items-start gap-3 border-bottom">
                                    <div>
                                        <h5 class="my-1 text-primary">{{ $message['subject'] ?? 'Email tidak tersedia' }}</h5>
                                        @if (count($thread ?? []) > 1)
                                            <span class="badge badge-sm light badge-primary">{{ count($thread) }} pesan dalam percakapan</span>
                                        @endif
                                    </div>
                                    <div class="clearfix mt-2 mt-sm-0 flex-shrink-0">
                                        <button type="button" class="btn btn-square btn-primary btn-sm light" data-mail-reply-toggle><i class="fa fa-reply"></i></button>
                                        <button type="button" class="btn btn-square btn-primary btn-sm light"><i class="fas fa-arrow-right"></i></button>
                                        <button type="submit" form="mail-delete-form" class="btn btn-square btn-danger btn-sm light" title="Hapus email" @disabled(! $message)><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                                @if ($readError)
                                    @include('partials.swal-alert', ['type' => 'warning', 'message' => $readError])
                                @endif
                                <div class="mail-thread">
                                    @foreach ($thread ?? [] as $threadItem)
                                        @php
                                            $threadMessage = $threadItem['message'];
                                            $threadFolderQuery = $threadItem['folder'] === 'sent' ? ['folder' => 'sent'] : [];
                                            $threadCollapseId = 'mailThreadMessage'.$loop->index;
                                            $threadSender = (string) ($threadMessage['from'] ?? 'Email');
                                            $threadSenderName = trim(preg_replace('/\s*<[^>]*>\s*/', '', $threadSender) ?? '', " \"'") ?: $threadSender;
                                            $threadVisibleAttachments = collect($threadMessage['attachments'] ?? [])
                                                ->reject(fn (array $attachment): bool => in_array((string) $attachment['id'], $threadItem['emailInlineAttachmentIds'], true));
                                        @endphp
                                        <div @class(['mail-thread-item', 'is-own' => $threadItem['isOwn']])>
                                            <div class="mail-thread-bubble">
                                                <button type="button" class="mail-thread-toggle {{ $threadItem['isCurrent'] ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#{{ $threadCollapseId }}" aria-expanded="{{ $threadItem['isCurrent'] ? 'true' : 'false' }}" aria-controls="{{ $threadCollapseId }}">
                                                    <span class="avatar avatar-sm bg-primary text-white d-flex align-items-center justify-content-center rounded-circle flex-shrink-0">{{ mb_strtoupper(mb_substr($threadSenderName, 0, 1)) }}</span>
                                                    <span class="mail-thread-heading">
                                                        <span class="mail-thread-sender">{{ $threadSender }}</span>
                                                        <span class="mail-thread-recipient">To: {{ $threadMessage['to'] ?? '' }}</span>
                                                        <span class="mail-thread-snippet">{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', (string) ($threadMessage['body'] ?? '')) ?? ''), 140) }}</span>
                                                    </span>
                                                    <span class="mail-thread-date">{{ $threadMessage['time'] ?? '' }}</span>
                                                </button>
                                                <div class="collapse {{ $threadItem['isCurrent'] ? 'show' : '' }}" id="{{ $threadCollapseId }}">
                                                    <div class="mail-thread-body">
                                                        @if (! empty($threadItem['emailHtmlDocument']))
                                                            <iframe class="mail-html-frame" data-mail-html-frame title="Isi email" sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox" referrerpolicy="no-referrer" srcdoc="{{ $threadItem['emailHtmlDocument'] }}"></iframe>
                                                        @else
                                                            <div class="lh-lg">{!! nl2br(e($threadMessage['body'] ?? '')) !!}</div>
                                                        @endif
                                                        @if ($threadVisibleAttachments->isNotEmpty())
                                                            <div class="border-top pt-3 mt-4">
                                                                <h5 class="mb-3"><i class="fa fa-paperclip me-1"></i> Attachments</h5>
                                                                <div class="mail-attachments">
                                                                    @foreach ($threadVisibleAttachments as $attachment)
                                                                        <div class="mail-attachment-item">
                                                                            @if ($attachment['is_image'])
                                                                                <img src="{{ route($mailRoutePrefix.'.attachment', [$threadMessage['uid'], $attachment['id'], 'inline' => 1, ...$threadFolderQuery]) }}" alt="{{ $attachment['filename'] }}" class="mail-attachment-preview mb-2">
                                                                            @else
                                                                                <div class="d-flex align-items-center justify-content-center bg-light rounded mb-2" style="height: 120px;">
                                                                                    <i class="fa-regular fa-file fs-1 text-primary"></i>
                                                                                </div>
                                                                            @endif
                                                                            <div class="fw-semibold text-break">{{ $attachment['filename'] }}</div>
                                                                            <div class="small text-muted mb-2">{{ $attachment['content_type'] }} · {{ number_format($attachment['size'] / 1024, 1) }} KB</div>
                                                                            <a href="{{ route($mailRoutePrefix.'.attachment', [$threadMessage['uid'], $attachment['id'], ...$threadFolderQuery]) }}" class="btn btn-primary btn-sm w-100">
                                                                                <i class="fa fa-download me-1"></i> Download
                                                                            </a>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @unless ($threadItem['isCurrent'])
                                                            <div class="mail-thread-open-link mt-3">
                                                                <a href="{{ route($mailRoutePrefix.'.read', [$threadMessage['uid'], ...$threadFolderQuery]) }}" class="small">
                                                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka pesan ini
                                                                </a>
                                                            </div>
                                                        @endunless
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="clearfix border-top border-bottom py-3 my-3">
                                    <button class="btn btn-primary btn-sm me-2"><i class="fa-solid fa-forward"></i> Forward</button>
                                    <button type="button" class="btn btn-secondary btn-sm" data-mail-reply-toggle><i class="fa-solid fa-reply"></i> Reply</button>
                                </div>
                                @if ($message)
                                    <form action="{{ route($mailRoutePrefix.'.reply', [$message['uid'], ...$folderQuery]) }}" method="POST" enctype="multipart/form-data" id="mailReplyForm" class="{{ old('body') ? '' : 'd-none' }}">
                                        @csrf
                                        <div class="mail-reply-editor mb-3">
                                            <div class="mail-reply-target d-flex align-items-start gap-2">
                                                <i class="fa-solid fa-reply mt-1 text-primary"></i>
                                                <div>
                                                    <div class="fw-semibold">Reply akan dikirim ke {{ $replyTo ?? $message['from'] }}</div>
                                                    <div class="small text-muted">Dari akun {{ $account->email }}</div>
                                                </div>
                                            </div>
                                            <textarea name="body" id="write-email" cols="30" rows="5" class="form-control mail-reply-textarea" placeholder="Write your reply..." required>{{ old('body') }}</textarea>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                                            <label class="btn btn-primary light mb-0">
                                                <i class="fa fa-paperclip me-1"></i> Attachment
                                                <input name="attachments[]" type="file" class="d-none" multiple data-mail-attachment-input>
                                            </label>
                                            <button class="btn btn-primary" type="submit">Send</button>
                                        </div>
                                        <div class="mail-selected-files d-none mb-3" data-mail-attachment-list></div>
                                    </form>
                                @endif
                            </div>
                        </div>
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
    @include('mail.partials.account-selector-script')
    <script>
        const resizeMailFrame = (frame) => {
            const frameDocument = frame.contentDocument;

            if (!frameDocument || !frameDocument.documentElement || frame.offsetParent === null) {
                return;
            }

            // Collapse first: scrollHeight never reports less than the frame's current height, so it could not shrink otherwise.
            frame.style.height = '0px';
            frame.style.height = `${Math.max(frameDocument.documentElement.scrollHeight, frameDocument.body ? frameDocument.body.scrollHeight : 0)}px`;
        };

        const resizeMailFramesIn = (container) => {
            container.querySelectorAll('[data-mail-html-frame]').forEach((frame) => {
                resizeMailFrame(frame);
                // Late-loading images change the height, so measure again a few times.
                [300, 1000, 2500].forEach((delay) => setTimeout(() => resizeMailFrame(frame), delay));
            });
        };

        document.querySelectorAll('[data-mail-html-frame]').forEach((frame) => {
            frame.addEventListener('load', () => resizeMailFramesIn(frame.parentElement));

            if (frame.contentDocument?.URL === 'about:srcdoc' && frame.contentDocument.readyState === 'complete') {
                resizeMailFramesIn(frame.parentElement);
            }
        });

        // Frames inside collapsed thread messages have no size until they are opened.
        document.addEventListener('shown.bs.collapse', (event) => resizeMailFramesIn(event.target));
        window.addEventListener('resize', () => document.querySelectorAll('[data-mail-html-frame]').forEach(resizeMailFrame));

        document.querySelectorAll('[data-mail-reply-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('mailReplyForm');
                const textarea = document.getElementById('write-email');

                if (!form) {
                    return;
                }

                form.classList.remove('d-none');
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });

                if (textarea) {
                    setTimeout(() => textarea.focus(), 250);
                }
            });
        });

        document.querySelectorAll('[data-mail-attachment-input]').forEach((input) => {
            const form = input.closest('form');
            const list = form?.querySelector('[data-mail-attachment-list]');

            if (!list) {
                return;
            }

            input.addEventListener('change', () => {
                const files = Array.from(input.files || []);

                list.classList.toggle('d-none', files.length === 0);
                list.replaceChildren();

                files.forEach((file) => {
                    const item = document.createElement('div');
                    const icon = document.createElement('i');
                    const name = document.createElement('span');
                    const size = document.createElement('span');

                    item.className = 'mail-selected-file';
                    icon.className = 'fa-regular fa-file text-primary';
                    name.className = 'text-break';
                    name.textContent = file.name;
                    size.className = 'small text-muted';
                    size.textContent = `${(file.size / 1024).toFixed(1)} KB`;

                    item.append(icon, name, size);
                    list.appendChild(item);
                });
            });
        });
    </script>
@endsection
