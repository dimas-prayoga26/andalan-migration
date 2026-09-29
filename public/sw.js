const SIAP_CACHE_VERSION = 'siap-pwa-v2';

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName !== SIAP_CACHE_VERSION)
                    .map((cacheName) => caches.delete(cacheName))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', () => {
    // Required for PWA installability. Requests still go directly to the network.
});

self.addEventListener('push', (event) => {
    let payload = {};

    try {
        payload = event.data ? event.data.json() : {};
    } catch (error) {
        payload = {
            title: 'SIAP',
            body: event.data ? event.data.text() : 'Ada notifikasi baru.',
        };
    }

    const title = payload.title || 'SIAP';
    const options = {
        body: payload.body || 'Ada notifikasi baru.',
        icon: payload.icon || '/images/images.png',
        badge: payload.badge || '/images/images.png',
        data: {
            url: payload.url || '/',
        },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : '/';

    event.waitUntil(self.clients.openWindow(targetUrl));
});
