(function () {
    const promptElement = document.querySelector('[data-install-app-prompt]');
    const launchScreen = document.querySelector('[data-pwa-launch-screen]');

    if (!promptElement) {
        return;
    }

    const installButton = promptElement.querySelector('[data-install-app-action]');
    const closeButton = promptElement.querySelector('[data-install-app-close]');
    const messageElement = promptElement.querySelector('[data-install-app-message]');
    const brandName = promptElement.getAttribute('data-brand-name') || 'SIAP';
    const swUrl = promptElement.getAttribute('data-sw-url') || '/sw.js';
    const launchShownKey = `siap-pwa-launch-shown:${window.location.host}`;
    const installStateKey = `siap-pwa-installed:${window.location.host}`;
    let deferredInstallPrompt = null;

    const isMobileViewport = () => window.matchMedia('(max-width: 768px)').matches;
    const isTouchDevice = () => navigator.maxTouchPoints > 0 || 'ontouchstart' in window;
    const isMobile = () => isMobileViewport() && isTouchDevice();
    const isIos = () => /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const pwaConfig = window.SIAP_PWA || {};

    const hasInstalledState = () => {
        try {
            return window.localStorage.getItem(installStateKey) === '1';
        } catch (error) {
            return false;
        }
    };

    const setInstalledState = () => {
        try {
            window.localStorage.setItem(installStateKey, '1');
        } catch (error) {}
    };

    const clearInstalledState = () => {
        try {
            window.localStorage.removeItem(installStateKey);
        } catch (error) {}
    };

    const showPrompt = (options = {}) => {
        const shouldIgnoreInstalledState = options.force === true;

        if (!isMobile() || isStandalone()) {
            return;
        }

        if (!shouldIgnoreInstalledState && hasInstalledState() && !isIos()) {
            return;
        }

        promptElement.removeAttribute('hidden');
    };

    const hidePrompt = () => {
        promptElement.setAttribute('hidden', 'hidden');
    };

    const showLaunchScreen = () => {
        if (!launchScreen || !isMobile() || !isStandalone() || window.sessionStorage.getItem(launchShownKey) === '1') {
            return;
        }

        window.sessionStorage.setItem(launchShownKey, '1');
        launchScreen.removeAttribute('hidden');

        window.setTimeout(() => {
            launchScreen.classList.add('is-hiding');

            window.setTimeout(() => {
                launchScreen.setAttribute('hidden', 'hidden');
                launchScreen.classList.remove('is-hiding');
            }, 380);
        }, 1350);
    };

    if (isStandalone()) {
        setInstalledState();
    }

    showLaunchScreen();

    const urlBase64ToUint8Array = (base64String) => {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let index = 0; index < rawData.length; index += 1) {
            outputArray[index] = rawData.charCodeAt(index);
        }

        return outputArray;
    };

    const browserName = () => {
        const userAgent = window.navigator.userAgent.toLowerCase();

        if (userAgent.includes('edg/')) {
            return 'Edge';
        }

        if (userAgent.includes('chrome/')) {
            return 'Chrome';
        }

        if (userAgent.includes('firefox/')) {
            return 'Firefox';
        }

        if (userAgent.includes('safari/')) {
            return 'Safari';
        }

        return 'Unknown';
    };

    const postSubscription = (subscription) => {
        if (!pwaConfig.subscribeUrl || !subscription) {
            return Promise.resolve();
        }

        const payload = subscription.toJSON();

        return window.fetch(pwaConfig.subscribeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': pwaConfig.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                endpoint: payload.endpoint,
                keys: payload.keys || {},
                expirationTime: payload.expirationTime || null,
                browser: browserName(),
                platform: window.navigator.platform || null,
            }),
        }).catch(() => {});
    };

    const syncDeviceSubscription = async (registration) => {
        if (!pwaConfig.isAuthenticated || !pwaConfig.vapidPublicKey || !pwaConfig.subscribeUrl) {
            return;
        }

        if (!('Notification' in window) || !('PushManager' in window) || !registration.pushManager) {
            return;
        }

        if (Notification.permission === 'denied') {
            return;
        }

        let permission = Notification.permission;

        if (permission === 'default') {
            if (!isStandalone()) {
                return;
            }

            permission = await Notification.requestPermission();
        }

        if (permission !== 'granted') {
            return;
        }

        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(pwaConfig.vapidPublicKey),
            });
        }

        await postSubscription(subscription);
    };

    if ('serviceWorker' in navigator && window.isSecureContext) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(swUrl, { updateViaCache: 'none' })
                .then((registration) => {
                    registration.update().catch(() => {});

                    return syncDeviceSubscription(registration);
                })
                .catch(() => {});
        });
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        clearInstalledState();
        showPrompt({ force: true });
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        setInstalledState();
        hidePrompt();

        if ('serviceWorker' in navigator && window.isSecureContext) {
            navigator.serviceWorker.ready
                .then((registration) => syncDeviceSubscription(registration))
                .catch(() => {});
        }
    });

    window.addEventListener('load', () => {
        if (isStandalone()) {
            setInstalledState();
            hidePrompt();
            return;
        }

        if (isIos()) {
            showPrompt({ force: true });
            return;
        }

        window.setTimeout(showPrompt, 1500);
    });

    closeButton?.addEventListener('click', () => {
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
