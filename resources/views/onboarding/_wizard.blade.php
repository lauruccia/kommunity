{{--
    Onboarding Wizard — modal multi-step Alpine.js
    Include in dashboard.blade.php con: @include('onboarding._wizard')
    Visibile solo se $showOnboarding === true
--}}
@if($showOnboarding)
<div
    x-data="{
        step: 0,
        total: 5,
        saving: false,
        done: false,
        fields: {
            company_name:             @json(old('company_name', optional($user->memberProfile)->company_name ?? '')),
            short_bio:                @json(old('short_bio', optional($user->memberProfile)->short_bio ?? '')),
            networking_goals:         @json(old('networking_goals', optional($user->memberProfile)->networking_goals ?? '')),
            services:                 @json(old('services', optional($user->memberProfile)->services ?? '')),
            website:                  @json(old('website', optional($user->memberProfile)->website ?? '')),
            linkedin_url:             @json(old('linkedin_url', optional($user->memberProfile)->linkedin_url ?? '')),
            phone:                    @json(old('phone', optional($user->memberProfile)->phone ?? '')),
            preferred_contact_method: @json(old('preferred_contact_method', optional(optional($user->memberProfile)->preferred_contact_method)->value ?? 'email'))
        },
        error: '',
        get progress() { return Math.round((this.step / this.total) * 100); },
        async saveStep() {
            this.saving = true;
            this.error  = '';
            try {
                const res = await fetch('{{ route('onboarding.step') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.fields)
                });
                if (!res.ok) {
                    const json = await res.json();
                    const errs = json.errors ? Object.values(json.errors).flat() : [];
                    this.error = errs[0] || 'Errore nel salvataggio.';
                    return false;
                }
            } catch(e) {
                this.error = 'Connessione non riuscita. Riprova.';
                return false;
            } finally {
                this.saving = false;
            }
            return true;
        },
        async next() {
            if (this.step === 0) { this.step++; return; }
            const ok = await this.saveStep();
            if (ok) this.step = Math.min(this.step + 1, this.total);
        },
        prev() {
            this.step = Math.max(this.step - 1, 0);
            this.error = '';
        }
    }"
    style="position:fixed;inset:0;z-index:9998;display:flex;align-items:center;justify-content:center;padding:1rem;"
    x-cloak>

    {{-- Backdrop --}}
    <div style="position:absolute;inset:0;background:rgba(2,11,18,.88);backdrop-filter:blur(8px);"></div>

    {{-- Pannello wizard --}}
    <div class="relative w-full max-w-xl overflow-hidden"
         style="border-radius:2rem;border:1px solid rgba(255,255,255,.12);background:linear-gradient(135deg,#031822,#052532);box-shadow:0 40px 120px rgba(0,0,0,.7);max-height:calc(100vh - 2rem);overflow-y:auto;">

        {{-- Progress bar --}}
        <div style="height:4px;background:rgba(255,255,255,.08);">
            <div :style="'width:' + progress + '%;transition:width .4s ease;'"
                 style="height:100%;background:linear-gradient(90deg,#8BC53F,#2DD4BF);border-radius:4px;"></div>
        </div>

        {{-- Step indicator --}}
        <div class="flex items-center justify-between px-7 pt-6">
            <span style="color:#9AD84A;font-size:.68rem;font-weight:800;letter-spacing:.28em;text-transform:uppercase;">
                Kommunity
            </span>
            <span x-show="step > 0 && step < total" style="color:rgba(255,255,255,.4);font-size:.75rem;">
                Step <span x-text="step"></span> di <span x-text="total - 1"></span>
            </span>
        </div>

        {{-- ═══ STEP 0: Benvenuto ═══ --}}
        {{-- Nessun x-transition qui: è lo stato iniziale (step=0 all'avvio), una transition "enter"
             su opacity-0 lascerebbe il contenuto invisibile se i class CSS Tailwind vengono purgati --}}
        <div x-show="step === 0" class="px-7 pb-8 pt-5">
            <div style="font-size:3rem;line-height:1;margin-bottom:1rem;">👋</div>
            <h2 style="font-size:1.75rem;font-weight:900;color:#fff;line-height:1.15;letter-spacing:-.03em;">
                Benvenuto in<br><span style="color:#8BC53F;">Kommunity</span>
            </h2>
            <p style="margin-top:.75rem;color:#AAB7C4;font-size:.9rem;line-height:1.7;">
                Ci vogliono solo <strong style="color:#fff;">3 minuti</strong> per completare il profilo e rendere la tua presenza visibile in Kommunity.
            </p>
            <ul style="margin-top:1.25rem;space-y:.5rem;display:flex;flex-direction:column;gap:.5rem;">
                <li style="display:flex;align-items:center;gap:.6rem;color:#AAB7C4;font-size:.85rem;">
                    <span style="color:#8BC53F;font-weight:900;">✓</span> Profilo business visibile in directory
                </li>
                <li style="display:flex;align-items:center;gap:.6rem;color:#AAB7C4;font-size:.85rem;">
                    <span style="color:#8BC53F;font-weight:900;">✓</span> Richieste di One-to-One attivate
                </li>
                <li style="display:flex;align-items:center;gap:.6rem;color:#AAB7C4;font-size:.85rem;">
                    <span style="color:#8BC53F;font-weight:900;">✓</span> Accesso al forum e agli eventi
                </li>
            </ul>
            <button @click="next()"
                    style="margin-top:1.75rem;width:100%;padding:1rem;border-radius:1rem;font-size:.95rem;font-weight:900;background:linear-gradient(135deg,#8BC53F,#5f9d42);color:#061018;border:0;cursor:pointer;box-shadow:0 12px 32px rgba(139,197,63,.25);transition:.2s ease;"
                    onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                Inizia il setup →
            </button>
            <a href="{{ route('profile.edit') }}"
               style="display:block;margin-top:.85rem;text-align:center;font-size:.78rem;color:rgba(255,255,255,.35);text-decoration:none;transition:color .2s;"
               onmouseover="this.style.color='rgba(255,255,255,.65)'" onmouseout="this.style.color='rgba(255,255,255,.35)'">
                Salta per ora → completa dal profilo
            </a>
        </div>

        {{-- ═══ STEP 1: Chi sei ═══ --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" class="px-7 pb-8 pt-4">
            <p style="color:#9AD84A;font-size:.68rem;font-weight:800;letter-spacing:.28em;text-transform:uppercase;">Step 1</p>
            <h2 style="margin-top:.4rem;font-size:1.4rem;font-weight:900;color:#fff;">Chi sei?</h2>
            <p style="margin-top:.35rem;color:#AAB7C4;font-size:.83rem;line-height:1.6;">Come ti conosce il mondo professionale.</p>

            <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.4rem;letter-spacing:.06em;text-transform:uppercase;">
                        Azienda / Studio / Brand
                    </label>
                    <input type="text" x-model="fields.company_name" placeholder="Es. Studio Rossi & Associati"
                           style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:.9rem;padding:.75rem 1rem;color:#fff;font-size:.9rem;outline:none;transition:.2s;"
                           onfocus="this.style.borderColor='rgba(139,197,63,.5)'" onblur="this.style.borderColor='rgba(255,255,255,.14)'">
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.4rem;letter-spacing:.06em;text-transform:uppercase;">
                        Bio breve <span style="color:rgba(255,255,255,.3);font-weight:400;">(max 500 caratteri)</span>
                    </label>
                    <textarea x-model="fields.short_bio" rows="3" placeholder="Presentati in poche righe: cosa fai, per chi, con quali risultati..."
                              style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:.9rem;padding:.75rem 1rem;color:#fff;font-size:.9rem;outline:none;resize:vertical;transition:.2s;"
                              onfocus="this.style.borderColor='rgba(139,197,63,.5)'" onblur="this.style.borderColor='rgba(255,255,255,.14)'"></textarea>
                    <p style="margin-top:.3rem;font-size:.72rem;color:rgba(255,255,255,.3);" x-text="(fields.short_bio || '').length + '/500 caratteri'"></p>
                </div>
            </div>

            <div x-show="error" style="margin-top:.75rem;padding:.6rem .9rem;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);border-radius:.7rem;color:#fca5a5;font-size:.8rem;" x-text="error"></div>

            <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
                <button @click="prev()" style="padding:.8rem 1.25rem;border-radius:.9rem;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);color:rgba(255,255,255,.6);font-size:.85rem;cursor:pointer;">← Indietro</button>
                <button @click="next()" :disabled="saving"
                        x-text="saving ? 'Salvataggio...' : 'Avanti →'"
                        style="flex:1;padding:.8rem;border-radius:.9rem;background:linear-gradient(135deg,#8BC53F,#5f9d42);color:#061018;font-weight:900;font-size:.9rem;border:0;cursor:pointer;transition:.2s;">
                    Avanti →
                </button>
            </div>
        </div>

        {{-- ═══ STEP 2: La tua attività ═══ --}}
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" class="px-7 pb-8 pt-4">
            <p style="color:#9AD84A;font-size:.68rem;font-weight:800;letter-spacing:.28em;text-transform:uppercase;">Step 2</p>
            <h2 style="margin-top:.4rem;font-size:1.4rem;font-weight:900;color:#fff;">La tua attività</h2>
            <p style="margin-top:.35rem;color:#AAB7C4;font-size:.83rem;line-height:1.6;">Cosa offri e cosa cerchi in Kommunity.</p>

            <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.4rem;letter-spacing:.06em;text-transform:uppercase;">
                        Servizi e competenze
                    </label>
                    <textarea x-model="fields.services" rows="3" placeholder="Es. Consulenza fiscale, Dichiarativi, Pianificazione patrimoniale..."
                              style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:.9rem;padding:.75rem 1rem;color:#fff;font-size:.9rem;outline:none;resize:vertical;transition:.2s;"
                              onfocus="this.style.borderColor='rgba(139,197,63,.5)'" onblur="this.style.borderColor='rgba(255,255,255,.14)'"></textarea>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.4rem;letter-spacing:.06em;text-transform:uppercase;">
                        Obiettivi di networking
                    </label>
                    <textarea x-model="fields.networking_goals" rows="3" placeholder="Cosa speri di ottenere da Kommunity? Nuovi clienti, partner, fornitori..."
                              style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:.9rem;padding:.75rem 1rem;color:#fff;font-size:.9rem;outline:none;resize:vertical;transition:.2s;"
                              onfocus="this.style.borderColor='rgba(139,197,63,.5)'" onblur="this.style.borderColor='rgba(255,255,255,.14)'"></textarea>
                </div>
            </div>

            <div x-show="error" style="margin-top:.75rem;padding:.6rem .9rem;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);border-radius:.7rem;color:#fca5a5;font-size:.8rem;" x-text="error"></div>

            <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
                <button @click="prev()" style="padding:.8rem 1.25rem;border-radius:.9rem;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);color:rgba(255,255,255,.6);font-size:.85rem;cursor:pointer;">← Indietro</button>
                <button @click="next()" :disabled="saving"
                        x-text="saving ? 'Salvataggio...' : 'Avanti →'"
                        style="flex:1;padding:.8rem;border-radius:.9rem;background:linear-gradient(135deg,#8BC53F,#5f9d42);color:#061018;font-weight:900;font-size:.9rem;border:0;cursor:pointer;transition:.2s;">
                    Avanti →
                </button>
            </div>
        </div>

        {{-- ═══ STEP 3: Online ═══ --}}
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" class="px-7 pb-8 pt-4">
            <p style="color:#9AD84A;font-size:.68rem;font-weight:800;letter-spacing:.28em;text-transform:uppercase;">Step 3</p>
            <h2 style="margin-top:.4rem;font-size:1.4rem;font-weight:900;color:#fff;">La tua presenza online</h2>
            <p style="margin-top:.35rem;color:#AAB7C4;font-size:.83rem;line-height:1.6;">Dove ti trovano gli altri membri.</p>

            <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.4rem;letter-spacing:.06em;text-transform:uppercase;">
                        Sito web
                    </label>
                    <input type="url" x-model="fields.website" placeholder="https://www.tuosito.it"
                           style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:.9rem;padding:.75rem 1rem;color:#fff;font-size:.9rem;outline:none;transition:.2s;"
                           onfocus="this.style.borderColor='rgba(139,197,63,.5)'" onblur="this.style.borderColor='rgba(255,255,255,.14)'">
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.4rem;letter-spacing:.06em;text-transform:uppercase;">
                        Profilo LinkedIn
                    </label>
                    <input type="url" x-model="fields.linkedin_url" placeholder="https://linkedin.com/in/tuoprofilo"
                           style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:.9rem;padding:.75rem 1rem;color:#fff;font-size:.9rem;outline:none;transition:.2s;"
                           onfocus="this.style.borderColor='rgba(139,197,63,.5)'" onblur="this.style.borderColor='rgba(255,255,255,.14)'">
                </div>
            </div>

            <div x-show="error" style="margin-top:.75rem;padding:.6rem .9rem;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);border-radius:.7rem;color:#fca5a5;font-size:.8rem;" x-text="error"></div>

            <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
                <button @click="prev()" style="padding:.8rem 1.25rem;border-radius:.9rem;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);color:rgba(255,255,255,.6);font-size:.85rem;cursor:pointer;">← Indietro</button>
                <button @click="next()" :disabled="saving"
                        x-text="saving ? 'Salvataggio...' : 'Avanti →'"
                        style="flex:1;padding:.8rem;border-radius:.9rem;background:linear-gradient(135deg,#8BC53F,#5f9d42);color:#061018;font-weight:900;font-size:.9rem;border:0;cursor:pointer;transition:.2s;">
                    Avanti →
                </button>
            </div>
        </div>

        {{-- ═══ STEP 4: Contatti ═══ --}}
        <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" class="px-7 pb-8 pt-4">
            <p style="color:#9AD84A;font-size:.68rem;font-weight:800;letter-spacing:.28em;text-transform:uppercase;">Step 4</p>
            <h2 style="margin-top:.4rem;font-size:1.4rem;font-weight:900;color:#fff;">Come contattarti</h2>
            <p style="margin-top:.35rem;color:#AAB7C4;font-size:.83rem;line-height:1.6;">Come preferisci essere raggiunto dai membri.</p>

            <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.4rem;letter-spacing:.06em;text-transform:uppercase;">
                        Telefono / WhatsApp
                    </label>
                    <input type="tel" x-model="fields.phone" placeholder="+39 347 1234567"
                           style="width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:.9rem;padding:.75rem 1rem;color:#fff;font-size:.9rem;outline:none;transition:.2s;"
                           onfocus="this.style.borderColor='rgba(139,197,63,.5)'" onblur="this.style.borderColor='rgba(255,255,255,.14)'">
                </div>

                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:.5rem;letter-spacing:.06em;text-transform:uppercase;">
                        Metodo di contatto preferito
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                        @foreach(\App\Enums\ContactMethod::cases() as $method)
                        <label style="display:flex;align-items:center;gap:.6rem;padding:.7rem .9rem;border-radius:.8rem;cursor:pointer;transition:.2s;"
                               :style="fields.preferred_contact_method === '{{ $method->value }}' ? 'background:rgba(139,197,63,.15);border:1px solid rgba(139,197,63,.4);' : 'background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);'">
                            <input type="radio" x-model="fields.preferred_contact_method" value="{{ $method->value }}" style="accent-color:#8BC53F;">
                            <span style="font-size:.83rem;color:#fff;">{{ $method->label() }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div x-show="error" style="margin-top:.75rem;padding:.6rem .9rem;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);border-radius:.7rem;color:#fca5a5;font-size:.8rem;" x-text="error"></div>

            <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
                <button @click="prev()" style="padding:.8rem 1.25rem;border-radius:.9rem;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);color:rgba(255,255,255,.6);font-size:.85rem;cursor:pointer;">← Indietro</button>
                <button @click="next()" :disabled="saving"
                        x-text="saving ? 'Salvataggio...' : 'Avanti →'"
                        style="flex:1;padding:.8rem;border-radius:.9rem;background:linear-gradient(135deg,#8BC53F,#5f9d42);color:#061018;font-weight:900;font-size:.9rem;border:0;cursor:pointer;transition:.2s;">
                    Avanti →
                </button>
            </div>
        </div>

        {{-- ═══ STEP 5: Completato ═══ --}}
        <div x-show="step === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="px-7 pb-8 pt-4 text-center">
            <div style="font-size:3.5rem;margin-bottom:1rem;animation:bounceIn .5s ease;">🎉</div>
            <h2 style="font-size:1.6rem;font-weight:900;color:#fff;letter-spacing:-.03em;">Profilo completato!</h2>
            <p style="margin-top:.6rem;color:#AAB7C4;font-size:.88rem;line-height:1.7;">
                Il tuo profilo è stato inviato per la revisione. Ti notificheremo quando sarà approvato e visibile in directory.
            </p>

            <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:.75rem;align-items:stretch;">
                <div style="padding:1rem;border-radius:1.2rem;background:rgba(139,197,63,.08);border:1px solid rgba(139,197,63,.2);">
                    <p style="font-size:.78rem;color:#9AD84A;font-weight:800;letter-spacing:.15em;text-transform:uppercase;margin-bottom:.5rem;">Prossimi passi</p>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.4rem;text-align:left;">
                        <li style="display:flex;gap:.5rem;align-items:flex-start;color:#AAB7C4;font-size:.82rem;">
                            <span style="color:#8BC53F;margin-top:.1rem;">→</span> Carica avatar e foto profilo
                        </li>
                        <li style="display:flex;gap:.5rem;align-items:flex-start;color:#AAB7C4;font-size:.82rem;">
                            <span style="color:#8BC53F;margin-top:.1rem;">→</span> Aggiungi video di presentazione
                        </li>
                        <li style="display:flex;gap:.5rem;align-items:flex-start;color:#AAB7C4;font-size:.82rem;">
                            <span style="color:#8BC53F;margin-top:.1rem;">→</span> Completa gli slot di disponibilità per i 1:1
                        </li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('onboarding.complete') }}" style="display:contents;">
                    @csrf
                    <button type="submit"
                            style="padding:1rem;border-radius:1rem;background:linear-gradient(135deg,#8BC53F,#5f9d42);color:#061018;font-weight:900;font-size:.95rem;border:0;cursor:pointer;box-shadow:0 12px 32px rgba(139,197,63,.25);transition:.2s;"
                            onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        Vai alla dashboard →
                    </button>
                </form>

                <a href="{{ route('profile.edit') }}"
                   style="padding:.75rem;border-radius:1rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.55);font-size:.83rem;text-decoration:none;text-align:center;transition:.2s;"
                   onmouseover="this.style.color='rgba(255,255,255,.85)'" onmouseout="this.style.color='rgba(255,255,255,.55)'">
                    Completa il profilo avanzato
                </a>
            </div>
        </div>

    </div>
</div>

<style>
@keyframes bounceIn {
    0%   { transform: scale(0.3); opacity: 0; }
    50%  { transform: scale(1.08); }
    100% { transform: scale(1); opacity: 1; }
}
</style>
@endif
