/* ─────────────────────────────────────────────────────────────────────────
   Kommunity Push Client — gestione service worker + subscription.
   API pubblica:
       window.KommunityPush.init()
       window.KommunityPush.subscribe()
       window.KommunityPush.unsubscribe()
       window.KommunityPush.getState()  // 'unsupported' | 'denied' | 'granted-no-sub' | 'granted-sub' | 'default'
   ───────────────────────────────────────────────────────────────────────── */

(function () {
    'use strict';

    const SW_PATH = '/sw.js';
    const VAPID_ENDPOINT = '/push/vapid-public-key';

    /**
     * Converte VAPID public key da base64url a Uint8Array (richiesto da
     * pushManager.subscribe).
     */
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw     = window.atob(base64);
        const out     = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
        return out;
    }

    function isSupported() {
        return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    }

    async function getRegistration() {
        if (!isSupported()) return null;
        let reg = await navigator.serviceWorker.getRegistration();
        if (!reg) {
            reg = await navigator.serviceWorker.register(SW_PATH);
        }
        await navigator.serviceWorker.ready;
        return reg;
    }

    async function fetchVapidConfig() {
        const res = await fetch(VAPID_ENDPOINT, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('VAPID config non recuperabile');
        return res.json();
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function postJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type':   'application/json',
                'Accept':         'application/json',
                'X-CSRF-TOKEN':   csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });
        return res;
    }

    async function getState() {
        if (!isSupported()) return 'unsupported';
        if (Notification.permission === 'denied') return 'denied';
        if (Notification.permission === 'default') return 'default';

        const reg = await getRegistration();
        if (!reg) return 'unsupported';
        const sub = await reg.pushManager.getSubscription();
        return sub ? 'granted-sub' : 'granted-no-sub';
    }

    async function subscribe() {
        if (!isSupported()) {
            return { ok: false, reason: 'unsupported' };
        }

        const config = await fetchVapidConfig();
        if (!config.enabled || !config.public_key) {
            return { ok: false, reason: 'feature_disabled' };
        }

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            return { ok: false, reason: 'permission_' + permission };
        }

        const reg = await getRegistration();

        // Riusa subscription esistente se c'è
        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(config.public_key),
            });
        }

        // Comunica al server
        const subJson = sub.toJSON();
        const res = await postJson('/push/subscribe', {
            endpoint: subJson.endpoint,
            keys: subJson.keys,
        });

        if (!res.ok) {
            return { ok: false, reason: 'server_' + res.status };
        }

        return { ok: true };
    }

    async function unsubscribe() {
        if (!isSupported()) return { ok: false };

        const reg = await getRegistration();
        const sub = await reg.pushManager.getSubscription();
        if (!sub) return { ok: true };

        const endpoint = sub.endpoint;

        try {
            await sub.unsubscribe();
        } catch (e) { /* ignore */ }

        await postJson('/push/unsubscribe', { endpoint: endpoint });

        return { ok: true };
    }

    /**
     * Auto-init: registra solo il service worker (no consent prompt qui),
     * il banner gestisce il prompt di consenso quando l'utente clicca.
     */
    async function init() {
        if (!isSupported()) return;
        try {
            await getRegistration();
        } catch (e) {
            console.warn('[km-push] SW register failed:', e);
        }
    }

    window.KommunityPush = {
        init,
        subscribe,
        unsubscribe,
        getState,
        isSupported,
    };

    // Auto-init al DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
