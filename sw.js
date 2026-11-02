const CACHE_NAME = 'dailytor-v1';
const OFFLINE_URL = '/dailytor/offline.html'; // Fallback page for when offline

// Files to cache immediately when the service worker installs
const ASSETS_TO_CACHE = [
    '/dailytor/', // Base URL
    '/dailytor/index.php',
    '/dailytor/css/style.css',
    '/dailytor/js/app.js',
    '/dailytor/img/icon-192.png',
    '/dailytor/img/icon-512.png',
    OFFLINE_URL
];

// Install Event: Cache essential files
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[Service Worker] Caching offline assets');
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    // Force the waiting service worker to become the active service worker
    self.skipWaiting();
});

// Activate Event: Clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Removing old cache', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    // Claim control of all clients immediately
    self.clients.claim();
});

// Fetch Event: Serve cached content or fallback to offline.html
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    // API calls should never be cached, always try network
    if (event.request.url.includes('/api/')) {
        event.respondWith(
            fetch(event.request).catch(() => {
                // If API fails, return a custom offline JSON response
                return new Response(JSON.stringify({ hata: "İnternet bağlantınız yok. İşlem gerçekleştirilemedi." }), {
                    headers: { 'Content-Type': 'application/json' },
                    status: 503
                });
            })
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                // Background update for non-HTML assets (stale-while-revalidate pattern)
                fetch(event.request).then((networkResponse) => {
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, networkResponse);
                    });
                }).catch(() => {}); // Ignore network errors in background update
                return cachedResponse;
            }

            // If not in cache, try network
            return fetch(event.request).then((networkResponse) => {
                // Cache the newly fetched resource (optional, depending on strategy)
                /*
                const responseClone = networkResponse.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseClone);
                });
                */
                return networkResponse;
            }).catch(() => {
                // Network failed AND not in cache
                // If it's a page request, show offline.html
                if (event.request.mode === 'navigate' || event.request.headers.get('accept').includes('text/html')) {
                    return caches.match(OFFLINE_URL);
                }
            });
        })
    );
});
