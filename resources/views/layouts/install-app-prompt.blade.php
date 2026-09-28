@once
    @php
        $installPromptBrandName = $brandName ?? 'SIAP';
        $installPromptLogoUrl = $brandLogoUrl ?? asset('images/images.png');
        $installPromptScriptVersion = file_exists(public_path('assets/js/install-app-prompt.js'))
            ? filemtime(public_path('assets/js/install-app-prompt.js'))
            : time();
    @endphp

    <style>
        .install-app-prompt {
            position: fixed;
            right: 1rem;
            bottom: calc(env(safe-area-inset-bottom, 0px) + 1rem);
            left: 1rem;
            z-index: 1085;
            display: none;
            align-items: center;
            gap: 0.875rem;
            max-width: 34rem;
            margin: 0 auto;
            padding: 0.875rem;
            color: #071433;
            background: #fff;
            border: 1px solid rgba(40, 70, 199, 0.16);
            border-radius: 0.5rem;
            box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, 0.18);
        }

        .install-app-prompt:not([hidden]) {
            display: flex;
        }

        .install-app-prompt__logo {
            width: 2.625rem;
            height: 2.625rem;
            flex: 0 0 2.625rem;
            object-fit: contain;
        }

        .install-app-prompt__content {
            min-width: 0;
            flex: 1 1 auto;
        }

        .install-app-prompt__title {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .install-app-prompt__message {
            margin: 0.125rem 0 0;
            color: #6b7280;
            font-size: 0.75rem;
            line-height: 1.35;
        }

        .install-app-prompt__actions {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            flex: 0 0 auto;
        }

        .install-app-prompt__close {
            width: 2.25rem;
            height: 2.25rem;
            padding: 0;
        }

        @media (min-width: 769px) {
            .install-app-prompt {
                display: none !important;
            }
        }
    </style>

    <div
        class="install-app-prompt"
        data-install-app-prompt
        data-brand-name="{{ $installPromptBrandName }}"
        data-sw-url="{{ url('/sw.js') }}"
        hidden
        role="region"
        aria-label="Install application"
    >
        <img class="install-app-prompt__logo" src="{{ $installPromptLogoUrl }}" alt="{{ $installPromptBrandName }}">
        <div class="install-app-prompt__content">
            <p class="install-app-prompt__title">Install {{ $installPromptBrandName }}</p>
            <p class="install-app-prompt__message" data-install-app-message>Akses lebih cepat dari layar utama perangkat Anda.</p>
        </div>
        <div class="install-app-prompt__actions">
            <button type="button" class="btn btn-primary btn-sm" data-install-app-action>Install Application</button>
            <button type="button" class="btn btn-light btn-sm install-app-prompt__close" data-install-app-close aria-label="Tutup">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <script src="{{ asset('assets/js/install-app-prompt.js') }}?v={{ $installPromptScriptVersion }}"></script>
@endonce
