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

        .pwa-launch-screen {
            position: fixed;
            inset: 0;
            z-index: 1095;
            display: none;
            place-items: center;
            padding: 2rem;
            color: #071433;
            background:
                radial-gradient(circle at 50% 32%, rgba(40, 70, 199, 0.12), transparent 13rem),
                linear-gradient(180deg, #ffffff 0%, #f6f8ff 100%);
        }

        .pwa-launch-screen:not([hidden]) {
            display: grid;
        }

        .pwa-launch-screen.is-hiding {
            animation: pwaLaunchFadeOut 360ms ease forwards;
        }

        .pwa-launch-screen__content {
            display: grid;
            justify-items: center;
            text-align: center;
        }

        .pwa-launch-screen__logo-wrap {
            position: relative;
            display: grid;
            width: 7.5rem;
            height: 7.5rem;
            place-items: center;
            margin-bottom: 1.25rem;
            border-radius: 1.75rem;
            background: #ffffff;
            box-shadow: 0 1.5rem 3rem rgba(15, 23, 42, 0.16);
            animation: pwaLaunchLogoIn 820ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
        }

        .pwa-launch-screen__logo-wrap::before {
            position: absolute;
            inset: -0.75rem;
            border: 1px solid rgba(40, 70, 199, 0.18);
            border-radius: 2.25rem;
            content: "";
            animation: pwaLaunchPulse 1200ms ease-out infinite;
        }

        .pwa-launch-screen__logo {
            width: 5.5rem;
            height: 5.5rem;
            object-fit: contain;
        }

        .pwa-launch-screen__title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0;
            line-height: 1.2;
        }

        .pwa-launch-screen__subtitle {
            margin: 0.375rem 0 0;
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .pwa-launch-screen__loader {
            display: inline-flex;
            gap: 0.375rem;
            margin-top: 1.5rem;
        }

        .pwa-launch-screen__loader span {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background: #2846c7;
            animation: pwaLaunchDot 900ms ease-in-out infinite;
        }

        .pwa-launch-screen__loader span:nth-child(2) {
            animation-delay: 120ms;
        }

        .pwa-launch-screen__loader span:nth-child(3) {
            animation-delay: 240ms;
        }

        @keyframes pwaLaunchLogoIn {
            from {
                opacity: 0;
                transform: translateY(0.75rem) scale(0.88);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes pwaLaunchPulse {
            from {
                opacity: 0.7;
                transform: scale(0.92);
            }

            to {
                opacity: 0;
                transform: scale(1.08);
            }
        }

        @keyframes pwaLaunchDot {
            0%, 80%, 100% {
                opacity: 0.35;
                transform: translateY(0);
            }

            40% {
                opacity: 1;
                transform: translateY(-0.25rem);
            }
        }

        @keyframes pwaLaunchFadeOut {
            to {
                opacity: 0;
                transform: scale(1.02);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .pwa-launch-screen,
            .pwa-launch-screen *,
            .pwa-launch-screen *::before {
                animation: none !important;
            }
        }

        @media (min-width: 769px) {
            .install-app-prompt,
            .pwa-launch-screen {
                display: none !important;
            }
        }
    </style>

    <div
        class="pwa-launch-screen"
        data-pwa-launch-screen
        hidden
        aria-hidden="true"
    >
        <div class="pwa-launch-screen__content">
            <div class="pwa-launch-screen__logo-wrap">
                <img class="pwa-launch-screen__logo" src="{{ $installPromptLogoUrl }}" alt="{{ $installPromptBrandName }}">
            </div>
            <p class="pwa-launch-screen__title">{{ $installPromptBrandName }}</p>
            <p class="pwa-launch-screen__subtitle">SIAP</p>
            <div class="pwa-launch-screen__loader" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>

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
