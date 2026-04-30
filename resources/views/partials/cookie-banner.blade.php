{{-- ─────────────────────────────────────────────────────────────────────────
     Cookie banner GDPR — vanilla JS, zero dipendenze.
     - Si mostra finché l'utente non clicca Accetta o Solo necessari.
     - La preferenza viene memorizzata in cookie km_cookie_consent (1 anno).
     - Espone window.kmCookieConsent per check da altri script.
     - Link a privacy / termini / cookie policy dal CMS Page.
   ─────────────────────────────────────────────────────────────────────────── --}}
<div id="km-cookie-banner"
     role="dialog"
     aria-live="polite"
     aria-label="{{ __('Informativa cookie') }}"
     style="position:fixed;bottom:1rem;left:1rem;right:1rem;max-width:560px;margin:auto;background:#0b0d12;color:#fff;border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:1.1rem 1.25rem;box-shadow:0 18px 46px rgba(0,0,0,.45);z-index:1000;display:none;font-family:'Plus Jakarta Sans',system-ui,sans-serif;">
    <p style="margin:0 0 .35rem;font-size:.95rem;font-weight:700;letter-spacing:-.02em;">
        🍪 {{ __('Usiamo i cookie') }}
    </p>
    <p style="margin:0 0 .9rem;font-size:.82rem;line-height:1.5;color:rgba(255,255,255,.78);">
        {{ __('Utilizziamo cookie tecnici necessari al funzionamento del sito. Con il tuo consenso usiamo anche cookie di analisi anonima per migliorare l\'esperienza. Puoi cambiare idea in qualunque momento dalla') }}
        <a href="{{ route('page.show', ['slug' => 'cookie-policy']) }}" style="color:#9ef0c7;text-decoration:underline;">{{ __('cookie policy') }}</a>.
    </p>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
        <button type="button"
                data-km-cookie-action="accept"
                style="background:#9ef0c7;color:#0b0d12;border:none;border-radius:999px;padding:.55rem 1.15rem;font-weight:700;font-size:.82rem;cursor:pointer;">
            {{ __('Accetta tutto') }}
        </button>
        <button type="button"
                data-km-cookie-action="necessary"
                style="background:transparent;color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:.55rem 1.15rem;font-weight:600;font-size:.82rem;cursor:pointer;">
            {{ __('Solo necessari') }}
        </button>
        <a href="{{ route('page.show', ['slug' => 'privacy']) }}"
           style="margin-left:auto;color:rgba(255,255,255,.65);font-size:.78rem;text-decoration:underline;">
            {{ __('Privacy') }}
        </a>
    </div>
</div>

<script>
(function () {
    const COOKIE_NAME = 'km_cookie_consent';
    const banner = document.getElementById('km-cookie-banner');
    if (!banner) return;

    function readCookie(name) {
        return document.cookie.split('; ').reduce(function (acc, c) {
            const [k, v] = c.split('=');
            return k === name ? decodeURIComponent(v || '') : acc;
        }, '');
    }

    function writeCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        const secure  = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = name + '=' + encodeURIComponent(value)
            + '; expires=' + expires + '; path=/; SameSite=Lax' + secure;
    }

    const stored = readCookie(COOKIE_NAME);
    window.kmCookieConsent = stored || null;

    if (!stored) {
        banner.style.display = 'block';
    }

    banner.addEventListener('click', function (e) {
        const target = e.target.closest('[data-km-cookie-action]');
        if (!target) return;
        const action = target.getAttribute('data-km-cookie-action');
        const value  = action === 'accept' ? 'all' : 'necessary';
        writeCookie(COOKIE_NAME, value, 365);
        window.kmCookieConsent = value;
        banner.style.display = 'none';
        // Eventi per integrazioni future (analytics, ecc.)
        document.dispatchEvent(new CustomEvent('km:cookie-consent', { detail: { value: value } }));
    });
})();
</script>
