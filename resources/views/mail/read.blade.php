@extends('layouts.main')

@section('title', 'Email Read')

@section('css')
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
    </style>
@endsection

@section('navbarTitle', 'Email')

@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Email',
    'current' => 'Read',
    'homeRoute' => 'dashboard',
])

<div class="card border-0 mb-0 h-auto">
    <div class="card-body p-0">
        <div class="row gx-0">
            @include('mail.partials.sidebar', ['active' => 'read'])

            <div class="col-xxl-10 col-xl-9 col-lg-8">
                <div class="email-right-column">
                    <div class="d-flex justify-content-between p-3">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-square btn-sm tp-btn-light btn-primary"><i class="fa fa-archive"></i></button>
                            <button type="button" class="btn btn-square btn-sm tp-btn-light btn-primary"><i class="fa fa-exclamation-circle"></i></button>
                            <button type="button" class="btn btn-square btn-sm tp-btn-light btn-danger"><i class="fa fa-trash"></i></button>
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
                    @if (session('mail_status'))
                        <div class="alert alert-success mx-3">{{ session('mail_status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mx-3">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="right-box-padding p-0">
                        <div class="read-wapper dz-scroll" id="read-content">
                            <div class="read-content">
                                <div class="pt-3 d-sm-flex d-block justify-content-between border-bottom">
                                    <div class="clearfix mb-3 d-flex">
                                        <div class="avatar avatar-sm me-2 bg-primary text-white d-flex align-items-center justify-content-center rounded-circle">R</div>
                                        <div class="clearfix ms-2">
                                            <h5 class="text-primary mb-0">{{ $message['from'] ?? 'Email' }}</h5>
                                            <p class="mb-0">{{ $message['date'] ?? '' }}</p>
                                        </div>
                                    </div>
                                    <div class="clearfix mb-3">
                                        <button type="button" class="btn btn-square btn-primary btn-sm light" data-mail-reply-toggle><i class="fa fa-reply"></i></button>
                                        <button type="button" class="btn btn-square btn-primary btn-sm light"><i class="fas fa-arrow-right"></i></button>
                                        <button type="button" class="btn btn-square btn-danger btn-sm light"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="mb-2 mt-3">
                                    <span>{{ $message['time'] ?? '' }}</span>
                                    <h5 class="my-1 text-primary">{{ $message['subject'] ?? 'Email tidak tersedia' }}</h5>
                                    <p>To: <a>{{ $account->email }}</a></p>
                                </div>
                                <div class="read-content-body">
                                    @if ($readError)
                                        <div class="alert alert-warning">{{ $readError }}</div>
                                    @elseif ($message)
                                        <div class="lh-lg">{!! nl2br(e($message['body'])) !!}</div>
                                    @endif
                                </div>
                                @if (! empty($message['attachments']))
                                    <div class="border-top pt-3 mt-4">
                                        <h5 class="mb-3"><i class="fa fa-paperclip me-1"></i> Attachments</h5>
                                        <div class="mail-attachments">
                                            @foreach ($message['attachments'] as $attachment)
                                                <div class="mail-attachment-item">
                                                    @if ($attachment['is_image'])
                                                        <img src="{{ route('applicant.email.attachment', [$message['uid'], $attachment['id'], 'inline' => 1]) }}" alt="{{ $attachment['filename'] }}" class="mail-attachment-preview mb-2">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center bg-light rounded mb-2" style="height: 120px;">
                                                            <i class="fa-regular fa-file fs-1 text-primary"></i>
                                                        </div>
                                                    @endif
                                                    <div class="fw-semibold text-break">{{ $attachment['filename'] }}</div>
                                                    <div class="small text-muted mb-2">{{ $attachment['content_type'] }} · {{ number_format($attachment['size'] / 1024, 1) }} KB</div>
                                                    <a href="{{ route('applicant.email.attachment', [$message['uid'], $attachment['id']]) }}" class="btn btn-primary btn-sm w-100">
                                                        <i class="fa fa-download me-1"></i> Download
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="clearfix border-top border-bottom py-3 my-3">
                                    <button class="btn btn-primary btn-sm me-2"><i class="fa-solid fa-forward"></i> Forward</button>
                                    <button type="button" class="btn btn-secondary btn-sm" data-mail-reply-toggle><i class="fa-solid fa-reply"></i> Reply</button>
                                </div>
                                @if ($message)
                                    <form action="{{ route('applicant.email.reply', $message['uid']) }}" method="POST" enctype="multipart/form-data" id="mailReplyForm" class="{{ old('body') ? '' : 'd-none' }}">
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

@section('script')
    <script>
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
