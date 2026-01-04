/**
 * Service Worker for Nautilus PWA
 * 
 * Provides offline support and caching for better performance
 */

const CACHE_NAME = 'nautilus-v1.1.0';
const OFFLINE_URL = '/offline.html';

// Assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/store/dashboard',
    '/store/login',
    '/assets/css/modern-theme.css',
    '/assets/css/dashboard.css',
    '/assets/js/notifications.js',
    '/assets/js/keyboard-shortcuts.js',
    '/assets/js/theme-manager.js',
    '/assets/js/dashboard.js',
    OFFLINE_URL
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[ServiceWorker] Installing...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[ServiceWorker] Caching static assets');
                return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'reload' })));
            })
            .then(() => {
                console.log('[ServiceWorker] Installed successfully');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[ServiceWorker] Installation failed:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[ServiceWorker] Activating...');

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[ServiceWorker] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[ServiceWorker] Activated successfully');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip chrome-extension and other non-http requests
    if (!event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cached version and update cache in background
                    fetchAndCache(event.request);
                    return cachedResponse;
                }

                // Not in cache, fetch from network
                return fetch(event.request)
                    .then((response) => {
                        // Don't cache non-successful responses
                        if (!response || response.status !== 200 || response.type === 'error') {
                            return response;
                        }

                        // Cache successful responses
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    })
                    .catch(() => {
                        // Network failed, try to return offline page for navigation requests
                        if (event.request.mode === 'navigate') {
                            return caches.match(OFFLINE_URL);
                        }

                        // For other requests, return a generic offline response
                        return new Response('Offline', {
                            status: 503,
                            statusText: 'Service Unavailable',
                            headers: new Headers({
                                'Content-Type': 'text/plain'
                            })
                        });
                    });
            })
    );
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('[ServiceWorker] Background sync:', event.tag);

    if (event.tag === 'sync-data') {
        event.waitUntil(syncData());
    }
});

// Push notifications
self.addEventListener('push', (event) => {
    console.log('[ServiceWorker] Push received');

    const options = {
        body: event.data ? event.data.text() : 'New notification',
        icon: '/assets/images/icon-192x192.png',
        badge: '/assets/images/badge-72x72.png',
        vibrate: [200, 100, 200],
        tag: 'nautilus-notification',
        requireInteraction: false
    };

    event.waitUntil(
        self.registration.showNotification('Nautilus', options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[ServiceWorker] Notification clicked');

    event.notification.close();

    event.waitUntil(
        clients.openWindow('/store/dashboard')
    );
});

// Helper function to fetch and cache in background
function fetchAndCache(request) {
    return fetch(request)
        .then((response) => {
            if (response && response.status === 200) {
                const responseToCache = response.clone();
                caches.open(CACHE_NAME)
                    .then((cache) => {
                        cache.put(request, responseToCache);
                    });
            }
            return response;
        })
        .catch(() => {
            // Silently fail background updates
        });
}

// Helper function to sync data when back online
async function syncData() {
    try {
        // Get pending data from IndexedDB or localStorage
        // Send to server
        console.log('[ServiceWorker] Syncing data...');

        // This would be implemented based on your specific needs
        // For example, syncing offline sales, customer updates, etc.

        return Promise.resolve();
    } catch (error) {
        console.error('[ServiceWorker] Sync failed:', error);
        return Promise.reject(error);
    }
}

// Message handler for communication with main app
self.addEventListener('message', (event) => {
    console.log('[ServiceWorker] Message received:', event.data);

    if (event.data.action === 'skipWaiting') {
        self.skipWaiting();
    }

    if (event.data.action === 'clearCache') {
        event.waitUntil(
            caches.delete(CACHE_NAME)
                .then(() => {
                    return self.registration.unregister();
                })
        );
    }
});
