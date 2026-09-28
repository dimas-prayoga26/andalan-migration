(function () {
    const promptElement = document.querySelector('[data-install-app-prompt]');

    if (!promptElement) {
        return;
    }

    const installButton = promptElement.querySelector('[data-install-app-action]');
    const closeButton = promptElement.querySelector('[data-install-app-close]');
    const messageElement = promptElement.querySelector('[data-install-app-message]');
    const brandName = promptElement.getAttribute('data-brand-name') || 'SIAP';
    const swUrl = promptElement.getAttribute('data-sw-url') || '/sw.js';
    const dismissedKey = `siap-install-dismissed:${window.location.host}`;
    let deferredInstallPrompt = null;

    const isMobileViewport = () => window.matchMedia('(max-width: 768px)').matches;
    const isTouchDevice = () => navigator.maxTouchPoints > 0 || 'ontouchstart' in window;
    const isMobile = () => isMobileViewport() && isTouchDevice();
    const isIos = () => /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const wasDismissed = () => {
        const dismissedAt = Number(window.localStorage.getItem(dismissedKey) || 0);
        const sevenDays = 7 * 24 * 60 * 60 * 1000;

        return dismissedAt > 0 && Date.now() - dismissedAt < sevenDays;
    };

    const showPrompt = () => {
        if (!isMobile() || isStandalone() || wasDismissed()) {
            return;
        }

        promptElement.removeAttribute('hidden');
    };

    const hidePrompt = () => {
        promptElement.setAttribute('hidden', 'hidden');
    };

    if ('serviceWorker' in navigator && window.isSecureContext) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(swUrl).catch(() => {});
        });
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        showPrompt();
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        hidePrompt();
        window.localStorage.setItem(dismissedKey, String(Date.now()));
    });

    window.addEventListener('load', () => {
        if (isIos()) {
            showPrompt();
            return;
        }

        window.setTimeout(showPrompt, 1500);
    });

    closeButton?.addEventListener('click', () => {
        window.localStorage.setItem(dismissedKey, String(Date.now()));
        hidePrompt();
    });

    installButton?.addEventListener('click', async () => {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            hidePrompt();
            return;
        }

        if (messageElement) {
            messageElement.textContent = isIos()
                ? `Buka menu Share lalu pilih Add to Home Screen untuk install ${brandName}.`
                : `Buka menu browser lalu pilih Install app atau Add to Home screen untuk install ${brandName}.`;
        }
    });
})();
