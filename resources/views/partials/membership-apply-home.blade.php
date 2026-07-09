{{-- ╔══════════════════════════════════════════════════════════════════╗
     ║  KOMMUNITY — Sezione "Candidatura di ammissione" (homepage)      ║
     ║  Inclusa da welcome.blade.php · testi in lang/{it,en}/application ║
     ║  POST → route('membership.apply') · pianeta di default: Kosmos   ║
     ╚══════════════════════════════════════════════════════════════════╝ --}}
<style>
    .apply-section{background:linear-gradient(145deg,rgba(13,30,43,.92),rgba(7,17,26,.88));border-top:1px solid rgba(85,121,79,.28);border-bottom:1px solid rgba(70,93,112,.24)}
    .apply-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:4rem;align-items:start}
    .apply-points{list-style:none;margin:1.8rem 0 0;padding:0}
    .apply-points li{display:flex;align-items:flex-start;gap:.65rem;font-size:.85rem;line-height:1.6;color:rgba(214,228,236,.86);margin-top:.85rem;font-weight:600}
    .apply-points li::before{content:"✓";flex-shrink:0;display:flex;align-items:center;justify-content:center;width:1.35rem;height:1.35rem;border-radius:999px;font-size:.7rem;font-weight:900;color:var(--brand4);background:rgba(85,121,79,.16);border:1px solid rgba(85,121,79,.4)}
    .apply-card{border-radius:1rem;padding:2.1rem 2rem}
    .apply-card h3{font-size:1.25rem;font-weight:800;letter-spacing:-.02em;margin:0 0 .5rem}
    .apply-card .apply-sub{font-size:.82rem;color:var(--muted);line-height:1.65;margin:0 0 1.4rem}
    .apply-field{margin-top:1rem}
    .apply-field label{display:block;font-size:.72rem;font-weight:800;letter-spacing:.03em;color:rgba(214,228,236,.85);margin-bottom:.35rem}
    .apply-field input[type=text],.apply-field input[type=email],.apply-field input[type=tel]{width:100%;box-sizing:border-box;padding:.72rem .85rem;border-radius:.55rem;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.045);color:var(--text);font-size:.85rem;outline:none;transition:border-color .2s}
    .apply-field input:focus{border-color:var(--brand3)}
    .apply-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem}
    .apply-type{display:grid;grid-template-columns:1fr 1fr;gap:.55rem}
    .apply-type label{display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.68rem .5rem;border:1px solid rgba(255,255,255,.14);border-radius:.55rem;background:rgba(255,255,255,.03);font-size:.78rem;font-weight:700;color:var(--text);cursor:pointer;margin:0;transition:.2s}
    .apply-type input{accent-color:var(--brand4)}
    .apply-type label:has(input:checked){border-color:var(--brand3);background:rgba(111,163,103,.10)}
    .apply-hint{font-size:.66rem;color:var(--muted);margin:.3rem 0 0}
    .apply-privacy{font-size:.66rem;color:var(--muted);line-height:1.55;margin:.8rem 0 0;text-align:center}
    .apply-errors{background:rgba(220,38,38,.12);border:1px solid rgba(248,113,113,.4);color:#fecaca;border-radius:.55rem;padding:.7rem .9rem;font-size:.76rem;line-height:1.6;margin:1rem 0 0}
    .apply-errors p{margin:0}
    .apply-success{text-align:center;padding:1.5rem .5rem}
    .apply-success .apply-success-ic{display:flex;align-items:center;justify-content:center;width:3.4rem;height:3.4rem;border-radius:999px;margin:0 auto 1rem;background:rgba(111,163,103,.14);border:1px solid rgba(111,163,103,.5);color:var(--brand4);font-size:1.4rem;font-weight:900}
    .apply-success h3{margin:0 0 .5rem}
    .apply-success p{font-size:.85rem;color:var(--muted);line-height:1.7;margin:0}
    .apply-submit{width:100%;margin-top:1.4rem}
    @media(max-width:1200px){.apply-grid{grid-template-columns:1fr}}
    @media(max-width:520px){.apply-row{grid-template-columns:1fr}.apply-card{padding:1.5rem 1.15rem}}
</style>

<section id="candidatura" class="apply-section km-section">
    <div class="km-wrap apply-grid">
        <div>
            <span class="badge badge-green" style="margin-bottom:1.2rem"><span class="badge-dot"></span>{{ __('application.home_badge') }}</span>
            <h2 class="section-title">{{ __('application.home_title_1') }}<br><span class="accent">{{ __('application.home_title_2') }}</span></h2>
            <p class="section-copy">{{ __('application.home_text') }}</p>
            <ul class="apply-points">
                <li>{{ __('application.home_point_1') }}</li>
                <li>{{ __('application.home_point_2') }}</li>
                <li>{{ __('application.home_point_3') }}</li>
            </ul>
        </div>

        <div class="apply-card glass">
            @if(session('membership_applied'))
                <div class="apply-success" role="status">
                    <span class="apply-success-ic">✓</span>
                    <h3>{{ __('application.success_title') }}</h3>
                    <p>{{ __('application.success_text') }}</p>
                </div>
            @else
                <h3>{{ __('application.form_title') }}</h3>
                <p class="apply-sub">{{ __('application.form_subtitle') }}</p>

                @if($errors->any())
                    <div class="apply-errors" role="alert">
                        @foreach($errors->all() as $error)
                            <p>• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('membership.apply') }}" id="apply-form">
                    @csrf
                    {{-- Honeypot anti-bot: invisibile agli umani --}}
                    <input type="text" name="company_website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0">

                    <div class="apply-field">
                        <label for="ap-name">{{ __('application.form_name') }}</label>
                        <input type="text" id="ap-name" name="name" value="{{ old('name') }}" required autocomplete="name">
                    </div>

                    <div class="apply-row">
                        <div class="apply-field">
                            <label for="ap-email">{{ __('application.form_email') }}</label>
                            <input type="email" id="ap-email" name="email" value="{{ old('email') }}" required autocomplete="email">
                        </div>
                        <div class="apply-field">
                            <label for="ap-phone">{{ __('application.form_phone') }}</label>
                            <input type="tel" id="ap-phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel">
                        </div>
                    </div>

                    <div class="apply-field">
                        <label>{{ __('application.form_type') }}</label>
                        <div class="apply-type">
                            <label><input type="radio" name="applicant_type" value="privato" {{ old('applicant_type', 'privato') === 'privato' ? 'checked' : '' }}> {{ __('application.form_type_private') }}</label>
                            <label><input type="radio" name="applicant_type" value="azienda" {{ old('applicant_type') === 'azienda' ? 'checked' : '' }}> {{ __('application.form_type_company') }}</label>
                        </div>
                    </div>

                    <div class="apply-field">
                        <label for="ap-vat">{{ __('application.form_vat') }}</label>
                        <input type="text" id="ap-vat" name="vat_number" value="{{ old('vat_number') }}" autocomplete="off" {{ old('applicant_type') === 'azienda' ? 'required' : '' }}>
                        <p class="apply-hint">{{ __('application.form_vat_hint') }}</p>
                    </div>

                    <div class="apply-field">
                        <label for="ap-profession">{{ __('application.form_profession') }}</label>
                        <input type="text" id="ap-profession" name="profession" value="{{ old('profession') }}" required placeholder="{{ __('application.form_profession_ph') }}">
                    </div>

                    <div class="apply-field">
                        <label for="ap-referrer">{{ __('application.form_referrer') }}</label>
                        <input type="text" id="ap-referrer" name="referrer_name" value="{{ old('referrer_name') }}" placeholder="{{ __('application.form_referrer_ph') }}">
                        <p class="apply-hint">{{ __('application.form_referrer_hint') }}</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg apply-submit">
                        {{ __('application.form_submit') }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <p class="apply-privacy">{{ __('application.form_privacy') }}</p>
                </form>
            @endif
        </div>
    </div>
</section>

<script>
    (function () {
        // P.IVA obbligatoria solo per le aziende
        var vat = document.getElementById('ap-vat');
        document.querySelectorAll('#apply-form input[name="applicant_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (vat) vat.required = (radio.value === 'azienda' && radio.checked);
            });
        });

        // Dopo submit (errori o successo) riporta la vista sulla sezione
        var shouldScroll = {{ session('membership_applied') || $errors->any() ? 'true' : 'false' }};
        var section = document.getElementById('candidatura');
        if (shouldScroll && section) {
            setTimeout(function () { section.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 150);
        }
    }());
</script>
