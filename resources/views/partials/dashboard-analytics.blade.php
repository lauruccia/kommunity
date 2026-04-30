{{-- ─────────────────────────────────────────────────────────────────────────
     Dashboard analytics personale (Feature #7)
     Variabili attese: $analytics (array dal MemberAnalyticsService)
   ───────────────────────────────────────────────────────────────────────── --}}
@php
    $a   = $analytics ?? null;
    $maxBar = collect($a['monthly_trend'] ?? [])->max('one_to_ones') ?: 1;
@endphp

@if($a)
<section class="km-dark-panel" style="padding:1.75rem 2rem;border-radius:18px;margin-top:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
        <div>
            <p class="km-eyebrow">📈 Le tue performance</p>
            <h2 style="margin-top:.4rem;font-size:1.4rem;font-weight:800;letter-spacing:-.02em;color:var(--km-text);">
                Cosa ti sta restituendo Kommunity
            </h2>
        </div>
        @if(! empty($a['subscription']['plan']))
            <span style="font-size:.78rem;color:rgba(255,255,255,.6);">
                Piano <strong style="color:#fff;">{{ $a['subscription']['plan'] }}</strong>
                @if(! empty($a['subscription']['since']))
                    · iscritto dal {{ $a['subscription']['since'] }}
                @endif
            </span>
        @endif
    </div>

    {{-- KPI cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-top:1.5rem;">
        <div class="km-dark-card" style="padding:1.1rem 1.25rem;border-radius:14px;">
            <p style="font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin:0;">1:1 completati</p>
            <p style="font-size:2.1rem;font-weight:900;letter-spacing:-.04em;margin:.35rem 0 0;color:#fff;">
                {{ number_format($a['one_to_ones']['completed']) }}
            </p>
            <p style="font-size:.78rem;color:rgba(255,255,255,.6);margin:.25rem 0 0;">
                +{{ number_format($a['one_to_ones']['last_30d']) }} negli ultimi 30 giorni
            </p>
        </div>

        <div class="km-dark-card" style="padding:1.1rem 1.25rem;border-radius:14px;">
            <p style="font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin:0;">Referral chiusi positivi</p>
            <p style="font-size:2.1rem;font-weight:900;letter-spacing:-.04em;margin:.35rem 0 0;color:var(--km-green);">
                {{ number_format($a['referrals']['won']) }}
            </p>
            <p style="font-size:.78rem;color:rgba(255,255,255,.6);margin:.25rem 0 0;">
                Su {{ number_format($a['referrals']['sent'] + $a['referrals']['received']) }} totali
            </p>
        </div>

        <div class="km-dark-card" style="padding:1.1rem 1.25rem;border-radius:14px;">
            <p style="font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin:0;">Valore referral generato</p>
            <p style="font-size:2.1rem;font-weight:900;letter-spacing:-.04em;margin:.35rem 0 0;color:#fff;">
                € {{ number_format($a['referrals']['won_value'], 0, ',', '.') }}
            </p>
            <p style="font-size:.78rem;color:rgba(255,255,255,.6);margin:.25rem 0 0;">
                Da affari conclusi grazie alla rete
            </p>
        </div>

        <div class="km-dark-card" style="padding:1.1rem 1.25rem;border-radius:14px;{{ $a['roi'] ? 'border-color:rgba(158,240,199,.35);' : '' }}">
            <p style="font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin:0;">ROI annuale sull'abbonamento</p>
            @if($a['roi'])
                <p style="font-size:2.1rem;font-weight:900;letter-spacing:-.04em;margin:.35rem 0 0;color:var(--km-green);">
                    {{ $a['roi']['formatted'] }}
                </p>
                <p style="font-size:.78rem;color:rgba(255,255,255,.6);margin:.25rem 0 0;">
                    Valore generato vs costo abbonamento
                </p>
            @else
                <p style="font-size:1.4rem;font-weight:700;margin:.35rem 0 0;color:rgba(255,255,255,.85);">—</p>
                <p style="font-size:.78rem;color:rgba(255,255,255,.6);margin:.25rem 0 0;">Attiva un abbonamento per calcolare il ROI</p>
            @endif
        </div>
    </div>

    {{-- Trend ultimi 6 mesi (bar chart CSS, nessuna libreria) --}}
    <div style="margin-top:1.75rem;">
        <p style="font-size:.78rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin:0 0 .9rem;">
            Ultimi 6 mesi · 1:1 completati e referral chiusi
        </p>
        <div style="display:grid;grid-template-columns:repeat({{ count($a['monthly_trend']) }}, 1fr);gap:.75rem;align-items:end;height:130px;">
            @foreach($a['monthly_trend'] as $m)
                @php
                    $h1 = max(2, ($m['one_to_ones'] / max(1, $maxBar)) * 100);
                    $h2 = max(2, ($m['referrals']  / max(1, $maxBar)) * 100);
                @endphp
                <div style="display:flex;flex-direction:column;align-items:center;gap:.4rem;">
                    <div style="display:flex;align-items:end;gap:.2rem;height:100px;width:100%;justify-content:center;">
                        <div title="1:1: {{ $m['one_to_ones'] }}"
                             style="background:rgba(158,240,199,.85);width:30%;height:{{ $h1 }}%;border-radius:4px 4px 0 0;"></div>
                        <div title="Referral chiusi: {{ $m['referrals'] }}"
                             style="background:rgba(255,255,255,.4);width:30%;height:{{ $h2 }}%;border-radius:4px 4px 0 0;"></div>
                    </div>
                    <span style="font-size:.7rem;color:rgba(255,255,255,.55);">{{ $m['month'] }}</span>
                </div>
            @endforeach
        </div>
        <div style="display:flex;gap:1.25rem;margin-top:.85rem;font-size:.72rem;color:rgba(255,255,255,.55);">
            <span style="display:inline-flex;align-items:center;gap:.4rem;">
                <span style="width:10px;height:10px;background:rgba(158,240,199,.85);border-radius:2px;"></span>
                1:1 completati
            </span>
            <span style="display:inline-flex;align-items:center;gap:.4rem;">
                <span style="width:10px;height:10px;background:rgba(255,255,255,.4);border-radius:2px;"></span>
                Referral chiusi
            </span>
        </div>
    </div>
</section>
@endif
