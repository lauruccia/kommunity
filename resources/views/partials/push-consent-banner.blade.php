{{-- ─────────────────────────────────────────────────────────────────────────
     Banner discreto consenso push notification (Feature #6 PWA).
     Si auto-attiva solo se:
       1. il feature flag pwa_push è ON
       2. il browser supporta Web Push
       3. l'utente non ha già accettato/rifiutato (km_push_choice cookie)
     Posizionato sopra il cookie banner (z-index 1100 vs 1000).
     Bilingue: usa le chiavi push.* di lang/it/ e lang/en/.
   ───────────────────────────────────────────────────────────────────────── --}}
@if(\App\Services\Features::enabled('pwa_push'))
<div id="km-push-banner"
     role="dialog"
     aria-label="{{ __('push.banner_aria') }}"
     style="position:fixed;top:1rem;right:1rem;max-width:380px;background:#0b0d12;color:#fff;border:1px solid rgba(158,240,199,.25);border-radius:14px;padding:1rem 1.15rem;box-shadow:0 12px 32px rgba(0,0,0,.45);z-index:1100;display:none;font-family:'Plus Jakarta Sans',system-ui,sans-serif;">
    <p style="margin:0 0 .35rem;font-size:.92rem;font-weight:700;letter-spacing:-.01em;">
        {{ __('push.banner_title') }}
    </p>
    <p style="margin:0 0 .85rem;font-size:.78rem;line-height:1.5;color:rgba(255,255,255,.78);">
        {{ __('push.banner_body') }}
    </p>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <button type="button"
                data-km-push="enable"
                style="background:#9ef0c7;color:#0b0d12;border:none;border-radius:999px;padding:.5rem 1rem;font-weight:700;font-size:.78rem;cursor:pointer;">
            {{ __('push.banner_enable') }}
        </button>
        <button type="button"
                data-km-push="later"
                style="background:transparent;color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:.5rem 1rem;font-weight:600;font-size:.78rem;cursor:pointer;">
            {{ __('push.banner_later') }}
        </button>
        <button type="button"
                data-km-push="never"
                style="background:transparent;color:rgba(255,255,255,.55);border:none;font-size:.72rem;cursor:pointer;text-decoration:underline;margin-left:auto;align-self:center;">
            {{ __('push.banner_never') }}
        </button>
    </div>
    <p id="km-push-status"
       style="margin:.6rem 0 0;font-size:.72rem;color:rgba(255,255,255,.55);min-height:1em;"></p>
</div>

<script src="{{ asset('js/km-push.js') }}" defer></script>
<script>
(function () {
    const banner    = document.getElementById('km-push-banner');
    const statusEl  = document.getElementById('km-push-status');
    const COOKIE    = 'km_push_choice';

    // Stringhe localizzate — iniettate da Blade
    const STR = {
        enabling: @json(__('push.banner_enabling')),
        enabled:  @json(__('push.banner_enabled')),
        failed:   @json(__('push.banner_failed')),
    };

    if (!banner) return;

    function readCookie(name) {
        return document.cookie.split('; ').reduce((acc, c) => {
            const [k, v] = c.split('=');
            return k === name ? decodeURIComponent(v || '') : acc;
        }, '');
    }
    function writeCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        const secure  = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax${secure}`;
    }

    async function maybeShow() {
        // Aspetta che km-push.js sia caricato
        if (!window.KommunityPush) {
            setTimeout(maybeShow, 200);
            return;
        }
        if (!window.KommunityPush.isSupported()) return;

        const choice = readCookie(COOKIE);
        if (choice === 'never' || choice === 'enabled') return;
        // 'later' lo rimostriamo dopo 7 giorni → cookie scade

        const state = await window.KommunityPush.getState();
        if (state === 'granted-sub' || state === 'denied') return;

        banner.style.display = 'block';
    }

    banner.addEventListener('click', async function (e) {
        const target = e.target.closest('[data-km-push]');
        if (!target) return;

        const action = target.getAttribute('data-km-push');

        if (action === 'enable') {
            statusEl.textContent = STR.enabling;
            const result = await window.KommunityPush.subscribe();
            if (result.ok) {
                writeCookie(COOKIE, 'enabled', 365);
                statusEl.textContent = STR.enabled;
                setTimeout(() => banner.style.display = 'none', 1500);
            } else {
                statusEl.textContent = STR.failed + ' (' + result.reason + ').';
            }
        }
        if (action === 'later') {
            writeCookie(COOKIE, 'later', 7);
            banner.style.display = 'none';
        }
        if (action === 'never') {
            writeCookie(COOKIE, 'never', 365);
            banner.style.display = 'none';
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', maybeShow);
    } else {
        maybeShow();
    }
})();
</script>
@endif
