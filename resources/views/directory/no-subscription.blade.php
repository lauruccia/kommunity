<x-app-layout>
    @push('body-class') km-bg-dark @endpush

    <div class="km-shell" style="padding-top:5rem;padding-bottom:5rem;min-height:70vh;display:flex;align-items:center;">
        <div style="max-width:36rem;margin:0 auto;text-align:center;">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:4.5rem;height:4.5rem;border-radius:999px;background:rgba(139,197,63,.10);border:1px solid rgba(139,197,63,.25);margin-bottom:1.5rem;">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" style="color:var(--km-green-2)">
                    <path stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/>
                </svg>
            </div>

            <h1 style="font-size:1.6rem;font-weight:800;color:var(--km-text);line-height:1.2;">
                Abbonamento richiesto
            </h1>
            <p style="margin-top:.75rem;font-size:.95rem;line-height:1.7;color:var(--km-text-muted);">
                L'accesso alla Directory dei membri è riservato agli abbonati.
                Scegli un piano per connetterti con tutti i professionisti della community.
            </p>

            <div style="margin-top:2rem;display:flex;flex-wrap:wrap;gap:.75rem;justify-content:center;">
                <a href="{{ route('subscriptions.index') }}" class="km-button-primary">
                    Scopri i piani
                </a>
                <a href="{{ route('dashboard') }}" class="km-button-secondary">
                    Torna alla dashboard
                </a>
            </div>

            @auth
                @if(auth()->user()->pendingSubscription())
                    <div style="margin-top:2rem;padding:1rem 1.25rem;border-radius:1rem;border:1px solid rgba(245,158,11,.3);background:rgba(245,158,11,.07);">
                        <p style="font-size:.85rem;color:#FCD34D;font-weight:700;">⏳ Richiesta in attesa di approvazione</p>
                        <p style="margin-top:.35rem;font-size:.8rem;color:rgba(252,211,77,.7);line-height:1.5;">
                            Hai già inviato una richiesta di abbonamento. Riceverai una notifica non appena verrà approvata.
                        </p>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</x-app-layout>
