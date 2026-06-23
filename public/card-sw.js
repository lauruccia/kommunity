/* ─────────────────────────────────────────────────────────────────────────
   Kommunity — Service Worker dedicato al BIGLIETTO DA VISITA (/card/{slug}).
   Scope: /card/  → NON interferisce con l'app autenticata né col push sw.js.

   Scopo: rendere la card disponibile OFFLINE dopo la prima apertura online,
   così che "Aggiungi a Home" apra il biglietto anche senza connessione.
   ───────────────────────────────────────────────────────────────────────── */

const CARD_CACHE = 'km-card-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k.startsWith('km-card-') && k !== CARD_CACHE)
                    .map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Solo GET; ignora tutto il resto (POST, ecc.)
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Non intercettare il download della vCard: deve sempre arrivare fresco
    // dal server come allegato (Content-Disposition: attachment).
    if (url.pathname.indexOf('/vcard') !== -1) return;

    // ── Pagina della card (navigazione) → network-first, fallback cache ──
    // Quando sei online vedi sempre i dati aggiornati; offline servi l'ultima
    // versione vista.
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CARD_CACHE).then((c) => c.put(req, copy)).catch(() => {});
                    return res;
                })
                .catch(() => caches.match(req))
        );
        return;
    }

    // ── Asset (CSS, font, avatar, immagini, brand) → cache-first ──
    // Velocità + disponibilità offline. Cache best-effort anche per le
    // risorse cross-origin (font.bunny.net): risposte opache incluse.
    event.respondWith(
        caches.match(req).then((cached) => {
            if (cached) return cached;
            return fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CARD_CACHE).then((c) => c.put(req, copy)).catch(() => {});
                    return res;
                })
                .catch(() => cached);
        })
    );
});
