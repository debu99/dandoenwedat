importScripts('https://storage.googleapis.com/workbox-cdn/releases/3.5.0/workbox-sw.js');
workbox.routing.registerRoute(
    new RegExp('https://fonts.(?:googleapis|gstatic).com/(.*)'),
    workbox.strategies.networkFirst({
        cacheName: 'google-fonts-stylesheets',
        plugins: [
            new workbox.expiration.Plugin({
                maxEntries: 100,
            }),
            new workbox.cacheableResponse.Plugin({
                statuses: [0, 200],
            }),
        ],
    }),
);
workbox.routing.registerRoute(
    new RegExp('^https://(.*).(?:fontawesome).com/(.*)'),
    workbox.strategies.networkFirst({
        cacheName: 'google-fonts-webfonts',
        plugins: [
            new workbox.expiration.Plugin({
                maxEntries: 30,
            }),
            new workbox.cacheableResponse.Plugin({
                statuses: [0, 200],
            }),
        ],
    }),
);
var version = 'pwa-cache-version-1.0';
var offlinePage = 'sespwa/offline';
var urlBlacklist = ['/admin/', '/login/', '/install/', '/signup/'];
function updateStaticCache() {
    return caches.open(version)
        .then(cache => {
            return cache.addAll([
                //offlinePage
            ]);
        });
}
self.addEventListener('install', function (event) {
    event.waitUntil(function () {
        updateStaticCache()
            .then(function () {
                self.skipWaiting();
            })
    });
});
self.addEventListener('activate', function (event) {
    event.waitUntil(clearOldCaches().then(function () {
        return updateStaticCache().then(function () {
            return self.clients.claim();
        });
    }));
});
function clearOldCaches() {
    return caches.keys().then(keys => {
        return Promise.all(
            keys
                .filter(key => key.indexOf(version) !== 0)
                .map(key => caches.delete(key))
        );
    });
}
function isHtmlRequest(request) {
    return request.headers.get('Accept').indexOf('text/html') !== -1;
}
function isBlacklisted(url) {
    return urlBlacklist.filter(bl => url.indexOf(bl) == 0).length > 0;
}
function isCachableResponse(response) {
    return response && response.ok;
}
self.addEventListener('install', event => {
    event.waitUntil(
        updateStaticCache()
            .then(() => self.skipWaiting())
    );
});
self.addEventListener('activate', event => {
    event.waitUntil(
        clearOldCaches()
            .then(() => self.clients.claim())
    );
});
self.addEventListener('fetch', event => {
    let request = event.request;

    if (request.method !== 'GET') {

        if (!navigator.onLine && isHtmlRequest(request)) {
            return event.respondWith(caches.match(offlinePage));
        }
        return;
    }

    if (isHtmlRequest(request)) {

        event.respondWith(
            fetch(request)
                .then(response => {
                    if (isCachableResponse(response) && !isBlacklisted(response.url)) {
                        let copy = response.clone();
                        caches.open(version).then(cache => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request)
                        .then(response => {
                            if (!response && request.mode == 'navigate') {
                                return caches.match(offlinePage);
                            }
                            return response;
                        });
                })
        );
    } else {
        if (event.request.cache === 'only-if-cached' && event.request.mode !== 'same-origin')
            return
        event.respondWith(
            caches.match(request)
                .then(response => {
                    return response || fetch(request)
                        .then(response => {
                            if (isCachableResponse(response)) {
                                let copy = response.clone();
                                caches.open(version).then(cache => cache.put(request, copy));
                            }
                            return response;
                        })
                })
        );
    }
});