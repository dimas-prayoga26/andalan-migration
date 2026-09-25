@extends('layouts.main')

@section('title', 'Email Compose')

@section('css')
    <style>
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
    'current' => 'Compose',
    'homeRoute' => 'dashboard',
])

<div class="card border-0 mb-0 h-auto">
    <div class="card-body p-0">
        <div class="row gx-0">
            @include('mail.partials.sidebar', ['active' => 'compose'])

            <div class="col-xxl-10 col-xl-9 col-lg-8">
                <div class="email-right-column">
                    <div class="compose-wrapper" id="compose-content">
                        <div class="align-items-center justify-content-between d-flex d-lg-none mb-3">
                            <h4 class="mb-0">Email</h4>
                            <a class="mobile-email-panel btn btn-square btn-primary light btn-sm">
                                <i class="fa-solid fa-list-ul"></i>
                            </a>
                        </div>
                        <div class="compose-content">
                            @if (session('mail_status'))
                                <div class="alert alert-success">{{ session('mail_status') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <form action="{{ route('applicant.email.send') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-12 mb-3">
                                        <input type="email" name="to" value="{{ old('to') }}" class="form-control" placeholder="To" required>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" placeholder="Subject" required>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <textarea name="body" class="form-control" rows="7" placeholder="Type Message" required>{{ old('body') }}</textarea>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <h5 class="mt-2 mb-3"><i class="fa fa-paperclip me-1"></i> Attachment</h5>
                                        <input name="attachments[]" type="file" class="form-control" multiple data-mail-attachment-input>
                                        <div class="mail-selected-files d-none mt-2" data-mail-attachment-list></div>
                                    </div>
                                    <div class="col-sm-12 mt-2">
                                        <a href="{{ route('applicant.email.inbox') }}" class="btn btn-danger light"><i class="fa fa-times"></i> Discard</a>
                                        <button class="btn btn-primary ms-2" type="submit"><i class="fa fa-paper-plane"></i> Send</button>
                                    </div>
                                </div>
                            </form>
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
