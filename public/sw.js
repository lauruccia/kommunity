/* ─────────────────────────────────────────────────────────────────────────
   Kommunity Service Worker — gestione push notification + click.
   NON cache-first: questo SW serve solo per push, non per offline.
   Aggiornare questo file ne triggera l'aggiornamento sul client.
   ───────────────────────────────────────────────────────────────────────── */

const KOMMUNITY_SW_VERSION = 'km-sw-v1';

self.addEventListener('install', (event) => {
    // Attiva subito senza aspettare il vecchio SW
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    // Prendi controllo di tutte le tab aperte
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: 'Kommunity', body: event.data.text() };
        }
    }

    const title   = data.title || 'Kommunity';
    const options = {
        body:    data.body || '',
        icon:    data.icon || '/images/icon-192.png',
        badge:   data.badge || '/images/badge-72.png',
        tag:     data.tag || 'kommunity-' + Date.now(),
        data:    { url: data.url || '/dashboard' },
        renotify: !!data.renotify,
        requireInteraction: !!data.requireInteraction,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url) || '/dashboard';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientsList) => {
            // Se c'è già una tab Kommunity aperta, mettila a fuoco e naviga
            for (const client of clientsList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.focus();
                    if ('navigate' in client) {
                        return client.navigate(targetUrl);
                    }
                    return;
                }
            }
            // Altrimenti apri nuova finestra
            if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
            }
        })
    );
});

self.addEventListener('pushsubscriptionchange', (event) => {
    // Il browser ha rinnovato la subscription: dobbiamo ri-comunicarla al server.
    // Apri una tab silente (se possibile) che chiama navigator.serviceWorker.ready
    // — il client JS in km-push.js gestirà la nuova subscription.
    // (Per ora log only, gestito al prossimo refresh utente)
    console.log('[km-sw] pushsubscriptionchange — il client ri-soscriverà al prossimo accesso');
});
