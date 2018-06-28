importScripts('https://storage.googleapis.com/workbox-cdn/releases/3.3.0/workbox-sw.js');
if (!workbox) {
    throw 'workbox did not load!';
}

const SW_VERSION = '2018062800';

// Debug
workbox.setConfig({debug: true});
const SW_LOGLEVEL = workbox.core.LOG_LEVELS.debug; // debug, log, warn, error, silent
workbox.core.setLogLevel(SW_LOGLEVEL);

// Routes
const OFFLINE_URL = 'offline/index.html';
const EXTERNAL_FONTS_ROUTES = 'https:\/\/fonts\.(?:googleapis|gstatic)\.com\/.*';
const EXTERNAL_JS_ROUTES = 'https:\/\/cdnjs\.cloudflare.\com\/ajax\/.*';
const FONTS_ROUTES = '\/theme\/font\.php\/.*'; // .*.(?:ttf|woff|woff2|eot)
const CSS_ROUTES = '\/theme\/styles\.php\/.*';
const YUI_ROUTES = '\/theme\/yui\_combo\.php\?.*';
const JS_ROUTES = '\/lib\/javascript\.php\/.*';
const IMAGES_ROUTES = '\/theme\/image\.php\/.*'; // .*.(?:png|jpg|jpeg|svg|gif)
const REQUIREJS_ROUTES = '\/lib\/requirejs\.php\/.*';
const OFFLINE_FALLBACK_WHITELISTED_ROUTES = '\/.*';
const OFFLINE_FALLBACK_BLACKLISTED_ROUTES = [
    JS_ROUTES,
    CSS_ROUTES,
    FONTS_ROUTES,
    EXTERNAL_FONTS_ROUTES,
    EXTERNAL_JS_ROUTES,
    IMAGES_ROUTES,
    YUI_ROUTES,
    REQUIREJS_ROUTES
].join('|');

// App shell-ready views
const MOD_URL_ROUTES = '\/mod\/url\/view\.php\?.*';

// const moodleViews =
//     '\/|' +
//     '\/admin\/search\.php.*|' +
//     '\/blog\/index\.php.*|' +
//     '\/calendar\/view\.php.*|' +
//     '\/course\/view\.php.*|' +
//     '\/grade\/report\/user\/index\.php.*|' +
//     '\/user\/view\.php.*|' +
//     '\/user\/profile\.php.*|' +
//     '\/user\/files\.php.*|' +
//     '\/user\/index\.php.*|' +
//     '\/login\/index\.php.*|' +
//     '\/mod\/forum\/discuss\.php.*|' +
//     '\/mod\/forum\/view\.php.*|' +
//     '\/mod\/book\/view\.php.*|' +
//     '\/mod\/page\/view\.php.*|' +
//     '\/mod\/book\/view\.php.*|' +
//     '\/mod\/quiz\/view\.php.*|' +
//     '\/mod\/workshop\/view\.php.*|' +
//     '\/mod\/quiz\/attempt\.php.*|' +
//     '\/mod\/glossary\/view\.php.*|' +
//     '\/mod\/workshop\/submission\.php.*|' +
//     '\/mod\/data\/view\.php.*|' +
//     '\/mod\/quiz\/summary\.php.*|' +
//     '\/mod\/wiki\/view\.php.*|' +
//     '\/mod\/choice\/view\.php.*|' +
//     '\/mod\/quiz\/edit\.php.*|' +
//     '\/mod\/feedback\/view\.php.*|' +
//     '\/mod\/lesson\/view\.php.*|' +
//     '\/mod\/chat\/view\.php.*|' +
//     '\/mod\/wiki\/view\.php.*|' +
//     '\/mod\/lesson\/view\.php.*|' +
//     '\/mod\/chat\/report\.php.*|' +
//     '\/mod\/folder\/view\.php.*|' +
//     '\/mod\/forum\/user\.php.*|' +
//     '\/mod\/assign\/view\.php.*|' +
//     '\/mod\/certificate\/view\.php.*|' +
//     '\/mod\/resource\/view\.php.*|' +
//     '\/mod\/survey\/view\.php.*|' +
//     '\/mod\/url\/view\.php.*' +
//     '\/my\/.*|';

/*
 * workbox docs:
 *
 * Available serving/caching strategies
 * ------------------------------------
 * workbox.strategies.networkFirst
 * workbox.strategies.networkOnly
 * workbox.strategies.cacheFirst
 * workbox.strategies.cacheOnly
 * workbox.strategies.StaleWhileRevalidate
 */

// Precached resources
workbox.precaching.precacheAndRoute([
    {
        "url": "offline/index.html",
        "revision": "c17216cc140f3223a24bbe9a4546f55d"
    },
    {
        "url": "offline/moodle-logo.png",
        "revision": "fd9df14eff3980f3eaaf744bd2484036"
    },
    {
        "url": "offline/favicon.ico",
        "revision": "f327a1ed56fe174f30eff79295199330"
    }
]);

// Dynamic caching of app shell-ready views
workbox.routing.registerRoute(
    new RegExp([
        MOD_URL_ROUTES
    ].join('|')),
    workbox.strategies.cacheFirst({
        cacheName: 'app-shell-ready-views-' + SW_VERSION
    })
);

// Dynamic caching of JS files
workbox.routing.registerRoute(
    new RegExp(JS_ROUTES),
    workbox.strategies.cacheFirst({
        cacheName: 'js-cache-' + SW_VERSION
    })
);

// Dynamic caching of RequireJS JS files
// TODO case they are served with the -1 instead of the versioning... ?
workbox.routing.registerRoute(
    new RegExp(REQUIREJS_ROUTES),
    workbox.strategies.cacheFirst({
        cacheName: 'requirejs-cache-' + SW_VERSION
    })
);

// Dynamic caching of CSS and JS YUI files
workbox.routing.registerRoute(
    new RegExp(YUI_ROUTES),
    workbox.strategies.cacheFirst({
        cacheName: 'yui-cache-' + SW_VERSION
    })
);

// Dynamic caching of CSS files
workbox.routing.registerRoute(
    new RegExp(CSS_ROUTES),
    workbox.strategies.cacheFirst({
        cacheName: 'css-cache-' + SW_VERSION
    })
);

// Dynamic caching of font files
workbox.routing.registerRoute(
    new RegExp(FONTS_ROUTES),
    workbox.strategies.cacheFirst({
        cacheName: 'fonts-cache-' + SW_VERSION,
        plugins: [new workbox.expiration.Plugin({
            maxEntries: 30
        })]
    })
);

// Dynamic caching of image files
workbox.routing.registerRoute(
    new RegExp(IMAGES_ROUTES),
    workbox.strategies.cacheFirst({
        cacheName: 'image-cache-' + SW_VERSION,
        plugins: [new workbox.expiration.Plugin({
            maxEntries: 60,
            maxAgeSeconds: 30 * 24 * 60 * 60 // 30 Days
        })]
    })
);

// Dynamic caching of external fonts
workbox.routing.registerRoute(
    new RegExp(EXTERNAL_FONTS_ROUTES),
    workbox.strategies.staleWhileRevalidate({
        cacheName: 'external-fonts-cache-' + SW_VERSION,
        plugins: [new workbox.expiration.Plugin({
            maxEntries: 30
        })]
    })
);

// Dynamic caching of external js libraries
workbox.routing.registerRoute(
    new RegExp(EXTERNAL_JS_ROUTES),
    workbox.strategies.staleWhileRevalidate({
        cacheName: 'external-js-cache-' + SW_VERSION,
        plugins: [new workbox.expiration.Plugin({
            maxEntries: 30
        })]
    })
);

// Offline fallback view
const moodleViewsStrategy = workbox.strategies.networkOnly();
const moodleViewsHandler = async(args) => {
    try {
        const response = await moodleViewsStrategy.handle(args);
        return response || caches.match(OFFLINE_URL);
    } catch (error) {
        return caches.match(OFFLINE_URL);
    }
};
const moodleViewNavigationRoute = new workbox.routing.NavigationRoute(moodleViewsHandler, {
    whitelist: [new RegExp(OFFLINE_FALLBACK_WHITELISTED_ROUTES)],
    blacklist: [new RegExp(OFFLINE_FALLBACK_BLACKLISTED_ROUTES)]
});
workbox.routing.registerRoute(moodleViewNavigationRoute);

/*
 * Push notifications
 */

function urlB64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

self.addEventListener('push', function(event) {
    console.log('[Service Worker] Push Received.');
    console.log(`[Service Worker] Push had this data: "${event.data.text()}"`);

    // Show custom notification
    const title = 'Push Codelab';
    const options = {
        body: 'Yay it works.',
        icon: 'images/icon.png',
        badge: 'images/badge.png'
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.');

    // Do something on notification click
    event.notification.close();
    event.waitUntil(clients.openWindow('https://developers.google.com/web/'));
});

self.addEventListener('pushsubscriptionchange', function(event) {
    console.log('[Service Worker]: \'pushsubscriptionchange\' event fired.');

    const applicationServerKey = urlB64ToUint8Array('BN16o4Vep3uUcxOJmhlHO56Mi_m6Kz-Bepj0f05x86DqsrdJgHpeyZRqgDpiod4zMlPWMyxdKRBW2I0hUALZeCw');
    event.waitUntil(self.registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    }).then(function(newSubscription) {
        console.log('[Service Worker] New subscription endpoint: ', newSubscription.endpoint);
    }));
});

self.addEventListener('fetch', function(e){

});
