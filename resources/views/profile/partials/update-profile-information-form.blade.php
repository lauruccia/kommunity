<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    @php
        $nameParts = preg_split('/\s+/', trim((string) $user->name), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
    @endphp

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('patch')

        <div class="grid gap-6 lg:grid-cols-2">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-stone-950">Identita' e contatti</h2>
                <p class="mt-2 text-sm leading-7 text-stone-600">Dati base del membro e canali di contatto da usare nelle schede directory.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="first_name" :value="'Nome *'" />
                    <x-text-input id="first_name" name="first_name" type="text" class="mt-2 block w-full" :value="old('first_name', $firstName)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                </div>
                <div>
                    <x-input-label for="last_name" :value="'Cognome *'" />
                    <x-text-input id="last_name" name="last_name" type="text" class="mt-2 block w-full" :value="old('last_name', $lastName)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="email" :value="'Email *'" />
                    <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $user->email)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2 text-sm text-stone-600">
                            Email non verificata.
                            <button form="send-verification" class="font-medium text-[color:var(--km-accent-strong)] underline">Invia di nuovo il link</button>
                        </div>
                    @endif
                </div>

                <div>
                    <x-input-label for="phone" :value="'Telefono *'" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-2 block w-full" :value="old('phone', $profile->phone)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
                <div>
                    <x-input-label for="whatsapp_number" :value="'WhatsApp'" />
                    <x-text-input id="whatsapp_number" name="whatsapp_number" type="text" class="mt-2 block w-full" :value="old('whatsapp_number', $profile->whatsapp_number)" />
                    <x-input-error class="mt-2" :messages="$errors->get('whatsapp_number')" />
                </div>
                <div>
                    <x-input-label for="avatar" :value="'Foto profilo'" />
                    @if ($profile->avatarUrl())
                        <img src="{{ $profile->avatarUrl() }}" alt="Foto profilo attuale" class="mt-2 h-24 w-24 rounded-[1.4rem] border border-stone-200 object-cover shadow-sm">
                        <div class="mt-1.5">
                            <button type="submit"
                                    form="delete-avatar-form"
                                    onclick="return confirm('Eliminare la foto profilo?')"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-rose-500 hover:text-rose-700 transition">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                                Elimina foto
                            </button>
                        </div>
                    @endif
                    <input id="avatar" name="avatar" type="file" accept="image/*" class="km-input mt-2 block w-full py-2.5">
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
                {{-- BANNER con crop Cropper.js --}}
                <div x-data="kmBannerCropper">

                    <x-input-label :value="'Banner card e pagina'" />
                    <p class="mt-1 text-xs text-stone-500">Proporzione ideale 4:1 (es. 1500 × 375 px). L'editor ti permette di ritagliare qualsiasi immagine.</p>

                    {{-- Anteprima immagine attuale (se presente e non ancora sostituita) --}}
                    @if ($user->memberOnepage?->coverImageUrl())
                        <div x-show="!croppedPreview" class="mt-2">
                            <img src="{{ $user->memberOnepage->coverImageUrl() }}"
                                 alt="Banner attuale"
                                 class="h-20 w-full rounded-[1.4rem] border border-stone-200 object-cover object-top shadow-sm">
                        </div>
                        <div x-show="!croppedPreview" class="mt-1.5">
                            <button type="submit"
                                    form="delete-banner-form"
                                    onclick="return confirm('Eliminare il banner?')"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-rose-500 hover:text-rose-700 transition">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                                Elimina banner
                            </button>
                        </div>
                    @endif

                    {{-- Anteprima immagine ritagliata (dopo crop) --}}
                    <template x-if="croppedPreview">
                        <div class="mt-2">
                            <img :src="croppedPreview"
                                 class="h-20 w-full rounded-[1.4rem] border border-emerald-200 object-cover object-top shadow-sm">
                            <p class="mt-1 text-xs font-medium text-emerald-600">✓ Immagine ritagliata — premi Salva per applicarla</p>
                        </div>
                    </template>

                    {{-- Trigger file picker (no name — non viene inviata al server) --}}
                    <input id="cover_image_picker"
                           type="file"
                           accept="image/*"
                           class="km-input mt-2 block w-full py-2.5"
                           @change="openCropper($event)">

                    {{-- Input nascosto con name — viene riempito col blob ritagliato --}}
                    <input type="file" name="cover_image" x-ref="coverInput" class="hidden">

                    <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />

                    {{-- Modal di ritaglio --}}
                    <div x-show="showModal"
                         x-cloak
                         class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/75 p-4"
                         @keydown.escape.window="cancelCrop()">
                        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
                            <h3 class="font-serif text-xl font-semibold text-stone-900">Ritaglia il banner</h3>
                            <p class="mt-1 text-sm text-stone-500">Trascina per spostare · Scorri/pizzica per zoomare · Proporzione fissa 4:1</p>

                            <div class="mt-4 overflow-hidden rounded-xl bg-stone-100" style="max-height:260px;">
                                <img x-ref="cropImg" class="block max-w-full">
                            </div>

                            <div class="mt-5 flex justify-end gap-3">
                                <button type="button"
                                        @click="cancelCrop()"
                                        class="rounded-full border border-stone-300 px-5 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50 transition">
                                    Annulla
                                </button>
                                <button type="button"
                                        @click="confirmCrop()"
                                        class="rounded-full bg-[color:var(--km-accent)] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[color:var(--km-accent-strong)] transition">
                                    Usa questo ritaglio
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="referral_link" :value="'Referral link personale'" />
                    <input id="referral_link" type="text" readonly value="{{ url($referralLink) }}" class="km-input mt-2 block w-full bg-stone-50">
                    <p class="mt-2 text-xs text-stone-500">Condividi questo link per invitare nuovi membri. Se si registrano da qui, il campo "Invitato da" verra' compilato automaticamente.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 border-t border-stone-200 pt-8 lg:grid-cols-2">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-stone-950">Profilo business</h2>
                <p class="mt-2 text-sm leading-7 text-stone-600">Informazioni che definiscono posizionamento, mercato e ricerca nella directory.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-input-label for="company_name" :value="'Azienda o attivita'" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-2 block w-full" :value="old('company_name', $profile->company_name)" />
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>
                <label class="md:col-span-2 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    <input type="checkbox" name="use_ai_profile_rewrite" value="1" class="mt-1 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-300" @checked(old('use_ai_profile_rewrite', $profile->use_ai_profile_rewrite))>
                    <span>
                        <span class="block font-semibold">Usa l'AI per rendere il profilo piu' avvincente</span>
                        <span class="mt-1 block text-emerald-800/80">Quando salvi, i testi di bio, chi sono, servizi, competenze e obiettivi vengono rielaborati senza inventare informazioni nuove.</span>
                    </span>
                </label>
                {{-- Tipologie aziende/gruppi: create da admin, selezionabili dall'utente --}}
                <div class="md:col-span-2">
                    <x-input-label :value="'Tipologie aziende/gruppi che voglio conoscere'" />
                    @if ($companyInterestTypes->isEmpty())
                        <p class="mt-2 text-sm text-stone-500">Nessuna tipologia disponibile al momento.</p>
                    @else
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach ($companyInterestTypes as $type)
                                @php $checked = collect(old('company_interest_type_ids', $profile->companyInterestTypes->pluck('id')->all()))->contains($type->id); @endphp
                                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 transition hover:bg-stone-100 {{ $checked ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : '' }}">
                                    <input type="checkbox" name="company_interest_type_ids[]" value="{{ $type->id }}" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-300" @checked($checked)>
                                    {{ $type->name }}
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <x-input-error class="mt-2" :messages="$errors->get('company_interest_type_ids')" />
                </div>

                {{-- In quale settore lavori: multi-select sulle professioni --}}
                @php
                    $professionOptions = $professions->map(fn($p) => ['id' => $p->id, 'label' => $p->name])->values()->all();
                    $selectedProfIds   = collect(old('profession_ids', $profile->professions->pluck('id')->all()))->map(fn($v) => (int) $v)->values()->all();
                @endphp
                <div class="md:col-span-2"
                     x-data="kmMultiSelect(@js($professionOptions), @js($selectedProfIds), 'profession_ids')">
                    <x-input-label :value="'In quale settore lavori *'" />
                    @include('profile.partials._multiselect')
                    <x-input-error class="mt-2" :messages="$errors->get('profession_ids')" />
                </div>

                {{-- Pianeta: assegnato dall'admin, non modificabile dall'utente --}}
                <div class="md:col-span-2">
                    <x-input-label :value="'Pianeta'" />
                    @if ($profile->chapter)
                        <div class="mt-2 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                            <span class="text-sm font-medium text-emerald-800">Sei nel Pianeta <strong>{{ $profile->chapter?->name ?? 'Nessun pianeta' }}</strong></span>
                        </div>
                    @else
                        <p class="mt-2 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-500">
                            Non sei ancora assegnato a un Pianeta. L'admin ti assegnerà in base alla tua professione e disponibilità.
                        </p>
                    @endif
                </div>
                {{-- Localizzazione a cascata: Regione → Provincia → Città --}}
                @php
                    $alpineRegions   = $regions->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values()->all();
                    $alpineProvinces = $provinces->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'region_id' => $p->region_id])->values()->all();
                    $alpineCities    = $cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'province_id' => $c->province_id ?? null, 'region_id' => $c->region_id])->values()->all();
                @endphp
                <script>
                    window._locationData = {
                        regions:   @json($alpineRegions),
                        provinces: @json($alpineProvinces),
                        cities:    @json($alpineCities)
                    };
                </script>
                <div class="md:col-span-2"
                     x-data="kmLocationPicker('{{ old('region_id', $profile->region_id ?? '') }}', '{{ old('province_id', $profile->city?->province_id ?? '') }}', '{{ old('city_id', $profile->city_id ?? '') }}')">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="region_id" :value="'Regione'" />
                            <select id="region_id" name="region_id" class="km-input mt-2"
                                    x-model="regionId" @change="changeRegion()">
                                <option value="">Tutte le regioni</option>
                                <template x-for="r in regions" :key="r.id">
                                    <option :value="r.id" x-text="r.name" :selected="regionId == r.id"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <x-input-label :value="'Provincia'" />
                            <select name="province_id" class="km-input mt-2"
                                    x-model="provinceId" @change="changeProvince()">
                                <option value="">Tutte le province</option>
                                <template x-for="p in filteredProvinces" :key="p.id">
                                    <option :value="p.id" x-text="p.name" :selected="provinceId == p.id"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="city_id" :value="'Città *'" />
                            <select id="city_id" name="city_id" class="km-input mt-2"
                                    x-model="cityId" required>
                                <option value="">Seleziona città</option>
                                <template x-for="c in filteredCities" :key="c.id">
                                    <option :value="c.id" x-text="c.name" :selected="cityId == c.id"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('city_id')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="short_bio" :value="'Titolo / bio breve'" />
                    <textarea id="short_bio" name="short_bio" rows="3" class="km-input mt-2">{{ old('short_bio', $profile->short_bio) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="bio" :value="'Chi sono'" />
                    <textarea id="bio" name="bio" rows="5" class="km-input mt-2">{{ old('bio', $profile->bio) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="services" :value="'Servizi offerti'" />
                    <textarea id="services" name="services" rows="5" class="km-input mt-2">{{ old('services', $profile->services) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="skills" :value="'Competenze'" />
                    <textarea id="skills" name="skills" rows="4" class="km-input mt-2">{{ old('skills', $profile->skills) }}</textarea>
                </div>
            </div>
        </div>

        <div class="grid gap-6 border-t border-stone-200 pt-8 lg:grid-cols-2">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-stone-950">Presenza digitale e networking</h2>
                <p class="mt-2 text-sm leading-7 text-stone-600">Link social, obiettivi relazionali e preferenze di visibilita'.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-input-label for="networking_goals" :value="'Obiettivi di networking'" />
                    <textarea id="networking_goals" name="networking_goals" rows="4" class="km-input mt-2">{{ old('networking_goals', $profile->networking_goals) }}</textarea>
                </div>
                <div>
                    <x-input-label for="website" :value="'Sito web'" />
                    <x-text-input id="website" name="website" type="text" class="mt-2 block w-full" placeholder="es. miosito.it" :value="old('website', $profile->website)" />
                </div>
                <div>
                    <x-input-label for="linkedin_url" :value="'LinkedIn'" />
                    <x-text-input id="linkedin_url" name="linkedin_url" type="text" class="mt-2 block w-full" placeholder="es. linkedin.com/in/nome" :value="old('linkedin_url', $profile->linkedin_url)" />
                </div>
                <div>
                    <x-input-label for="facebook_url" :value="'Facebook'" />
                    <x-text-input id="facebook_url" name="facebook_url" type="text" class="mt-2 block w-full" placeholder="es. facebook.com/pagina" :value="old('facebook_url', $profile->facebook_url)" />
                </div>
                <div>
                    <x-input-label for="instagram_url" :value="'Instagram'" />
                    <x-text-input id="instagram_url" name="instagram_url" type="text" class="mt-2 block w-full" placeholder="es. instagram.com/profilo" :value="old('instagram_url', $profile->instagram_url)" />
                </div>
                <div>
                    <x-input-label for="logo" :value="'Logo azienda'" />
                    @if ($profile->logoUrl())
                        <img src="{{ $profile->logoUrl() }}" alt="Logo attuale" class="mt-2 h-24 w-24 rounded-[1.4rem] border border-stone-200 object-cover shadow-sm">
                    @endif
                    <input id="logo" name="logo" type="file" accept="image/*" class="km-input mt-2 block w-full py-2.5">
                    <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="preferred_contact_method" :value="'Metodo di contatto preferito'" />
                    <select id="preferred_contact_method" name="preferred_contact_method" class="km-input mt-2">
                        @foreach (['email' => 'Email', 'phone' => 'Telefono', 'whatsapp' => 'WhatsApp', 'platform' => 'Messaggistica interna'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('preferred_contact_method', $profile->preferred_contact_method?->value) == $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @php $hasExistingVideo = filled($profile->intro_video) || filled($profile->intro_video_url); @endphp
                <div x-data="{ guide: false, tab: 'youtube', maxDurationSeconds: {{ $videoUploadLimits->maxDurationSeconds() }}, maxSizeKb: {{ $videoUploadLimits->maxSizeKilobytes() }} }" class="md:col-span-2 rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">

                    {{-- Titolo + stato --}}
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-stone-500" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm12.553 1.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                            </svg>
                            <x-input-label for="intro_video_url" :value="'Video presentazione'" class="!mb-0" />
                        </div>
                        @if ($profile->videoEmbedUrl())
                            <span class="flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                Video attivo
                            </span>
                        @endif
                    </div>

                    {{-- Campo URL --}}
                    <input id="intro_video_url" name="intro_video_url" type="text"
                           class="km-input block w-full"
                           placeholder="Incolla qui il link YouTube o Vimeo del tuo video"
                           value="{{ old('intro_video_url', $profile->intro_video_url) }}">
                    <x-input-error class="mt-2" :messages="$errors->get('intro_video_url')" />
                    @if($hasExistingVideo)
                        <p class="mt-2 flex items-center gap-1.5 text-xs text-amber-600">
                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                            Hai già una videopresentazione attiva. Inserendo un nuovo link la sostituirai al salvataggio.
                        </p>
                    @endif

                    {{-- Anteprima video attivo --}}
                    @php
                        $previewEmbed = $profile->videoEmbedUrl();
                        $previewLocal = $profile->introVideoUrl();
                    @endphp
                    @if ($previewEmbed)
                        <div class="mt-3 overflow-hidden rounded-xl border border-stone-200 bg-black" style="aspect-ratio:16/9;">
                            <iframe src="{{ $previewEmbed }}"
                                    class="h-full w-full"
                                    frameborder="0"
                                    allow="autoplay; fullscreen"
                                    allowfullscreen
                                    loading="lazy">
                            </iframe>
                        </div>
                    @elseif ($previewLocal)
                        <div class="mt-3 overflow-hidden rounded-xl border border-stone-200 bg-black" style="aspect-ratio:16/9;">
                            <video src="{{ $previewLocal }}"
                                   controls
                                   preload="metadata"
                                   class="h-full w-full object-contain">
                            </video>
                        </div>
                    @endif

                    @if ($hasExistingVideo)
                        <div class="mt-2">
                            <button type="submit"
                                    form="delete-video-form"
                                    onclick="return confirm('Eliminare il video di presentazione?')"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-rose-500 hover:text-rose-700 transition">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                                Elimina video
                            </button>
                        </div>
                    @endif

                    {{-- Toggle guida — subito sotto il campo URL --}}
                    <button type="button" @click="guide = !guide"
                            class="mt-3 flex items-center gap-1.5 text-xs font-medium text-[color:var(--km-accent-strong)] hover:underline focus:outline-none">
                        <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="guide ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="guide ? 'Nascondi guida' : 'Come ottenere il link? Segui la guida in 3 passi →'"></span>
                    </button>

                    {{-- GUIDA COLLASSABILE --}}
                    <div x-show="guide" x-cloak x-transition class="mt-3 overflow-hidden rounded-[1.2rem] border border-stone-200 bg-white">

                        {{-- Tabs YouTube / Vimeo --}}
                        <div class="flex border-b border-stone-100">
                            <button type="button" @click="tab = 'youtube'"
                                    :class="tab === 'youtube' ? 'border-b-2 border-red-500 text-stone-900 bg-red-50/60' : 'text-stone-500 hover:text-stone-700'"
                                    class="flex flex-1 items-center justify-center gap-2 px-4 py-3 text-sm font-medium transition">
                                <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                                YouTube <span class="text-xs text-stone-400 font-normal">(gratuito)</span>
                            </button>
                            <button type="button" @click="tab = 'vimeo'"
                                    :class="tab === 'vimeo' ? 'border-b-2 border-sky-500 text-stone-900 bg-sky-50/60' : 'text-stone-500 hover:text-stone-700'"
                                    class="flex flex-1 items-center justify-center gap-2 px-4 py-3 text-sm font-medium transition">
                                <svg class="h-4 w-4 text-sky-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.977 6.416c-.105 2.338-1.739 5.543-4.894 9.609-3.268 4.247-6.026 6.37-8.29 6.37-1.409 0-2.578-1.294-3.553-3.881L5.322 11.4C4.603 8.816 3.834 7.522 3.01 7.522c-.179 0-.806.378-1.881 1.132L0 7.197a315.065 315.065 0 003.501-3.12C5.08 2.701 6.266 1.984 7.055 1.91c1.867-.18 3.016 1.1 3.447 3.838.465 2.953.789 4.789.971 5.507.539 2.45 1.131 3.674 1.776 3.674.502 0 1.256-.796 2.265-2.385 1.004-1.589 1.54-2.797 1.612-3.628.144-1.371-.395-2.061-1.614-2.061-.574 0-1.167.132-1.777.396 1.18-3.86 3.43-5.737 6.75-5.637 2.464.072 3.623 1.67 3.492 4.802z"/>
                                </svg>
                                Vimeo <span class="text-xs text-stone-400 font-normal">(gratuito)</span>
                            </button>
                        </div>

                        {{-- YouTube steps --}}
                        <div x-show="tab === 'youtube'" class="p-5">
                            <ol class="space-y-4">
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-600">1</span>
                                    <div>
                                        <p class="text-sm font-medium text-stone-800">Vai su YouTube e carica il video</p>
                                        <p class="mt-0.5 text-xs text-stone-500">Accedi con il tuo account Google, poi clicca sul pulsante <strong>+ Crea</strong> in alto a destra.</p>
                                        <a href="https://www.youtube.com/upload" target="_blank" rel="noopener"
                                           class="mt-1.5 inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
                                            Apri YouTube Upload
                                        </a>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-600">2</span>
                                    <div>
                                        <p class="text-sm font-medium text-stone-800">Imposta la visibilità su <em>"Non in elenco"</em></p>
                                        <p class="mt-0.5 text-xs text-stone-500">Nella schermata di caricamento, sotto <strong>Visibilità</strong>, scegli <strong>"Non in elenco"</strong> — il video non apparirà sul tuo profilo YouTube ma sarà accessibile solo tramite link.</p>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-600">3</span>
                                    <div>
                                        <p class="text-sm font-medium text-stone-800">Copia il link e incollalo nel campo qui sopra</p>
                                        <p class="mt-0.5 text-xs text-stone-500">A caricamento completato, clicca <strong>Copia link video</strong> (o copia l'URL dalla barra del browser) e incollalo nel campo qui sopra.</p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        {{-- Vimeo steps --}}
                        <div x-show="tab === 'vimeo'" class="p-5">
                            <ol class="space-y-4">
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-bold text-sky-600">1</span>
                                    <div>
                                        <p class="text-sm font-medium text-stone-800">Vai su Vimeo e carica il video</p>
                                        <p class="mt-0.5 text-xs text-stone-500">Accedi al tuo account Vimeo (o creane uno gratuito), poi clicca su <strong>New video</strong> in alto a destra.</p>
                                        <a href="https://vimeo.com/upload" target="_blank" rel="noopener"
                                           class="mt-1.5 inline-flex items-center gap-1 rounded-lg bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-600">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
                                            Apri Vimeo Upload
                                        </a>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-bold text-sky-600">2</span>
                                    <div>
                                        <p class="text-sm font-medium text-stone-800">Imposta la privacy su <em>"Only people with the link"</em></p>
                                        <p class="mt-0.5 text-xs text-stone-500">Durante il caricamento, nelle impostazioni <strong>Privacy</strong>, seleziona <strong>"Only people with the link"</strong> — il video non sarà pubblico ma visibile tramite link.</p>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-bold text-sky-600">3</span>
                                    <div>
                                        <p class="text-sm font-medium text-stone-800">Copia il link e incollalo nel campo qui sopra</p>
                                        <p class="mt-0.5 text-xs text-stone-500">A caricamento completato, clicca <strong>Copy link</strong> dalla pagina del video e incollalo nel campo qui sopra. Il formato sarà simile a: <code class="rounded bg-stone-100 px-1">vimeo.com/123456789</code></p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        {{-- Footer nota --}}
                        <div class="border-t border-stone-100 bg-stone-50 px-5 py-3 text-xs text-stone-400">
                            In alternativa puoi registrare direttamente dalla camera qui sotto — max 60 secondi.
                        </div>
                    </div>

                    {{-- Input nascosto per la camera: usato dal recorder Alpine --}}
                    <input id="intro_video" name="intro_video" type="file"
                           accept="video/mp4,video/quicktime,video/webm" style="display:none">
                    <x-input-error class="mt-2" :messages="$errors->get('intro_video')" />

                    {{-- ── Registra direttamente dalla camera ─────────────────── --}}
                    <div class="mt-3 rounded-2xl border border-stone-200 bg-white p-4"
                         x-data="kmVideoRecorder({{ $hasExistingVideo ? 'true' : 'false' }})">

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                                    <circle cx="10" cy="10" r="4"/>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm font-medium text-stone-800">Oppure registra direttamente dalla camera</p>
                            </div>
                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-600">max 1 minuto</span>
                        </div>

                        {{-- Stato: idle --}}
                        <template x-if="state === 'idle'">
                            <div class="mt-3">
                                <button type="button" @click="startCamera()"
                                        class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-100">
                                    <svg class="h-4 w-4 text-rose-500" viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm12.553 1.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
                                    Apri camera e registra
                                </button>
                                <p class="mt-1.5 text-xs text-stone-400">La registrazione si ferma automaticamente dopo 60 secondi. Il video viene ottimizzato prima del salvataggio.</p>
                            </div>
                        </template>

                        {{-- Stato: preview camera (pronto a registrare) --}}
                        <template x-if="state === 'preview'">
                            <div class="mt-3 space-y-2">
                                <div class="relative overflow-hidden rounded-2xl bg-black" style="aspect-ratio:16/9;">
                                    <video autoplay muted playsinline class="h-full w-full object-cover" style="transform:scaleX(-1);"
                                           x-init="$el.srcObject = _stream; $el.play().catch(() => {})"></video>
                                    <div class="absolute bottom-2 left-0 right-0 flex justify-center">
                                        <span class="rounded-full bg-black/50 px-3 py-1 text-xs text-white backdrop-blur">Premi REC per iniziare · Max 60"</span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" @click="startRecording()"
                                            class="flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                                        <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-white"></span> REC
                                    </button>
                                    <button type="button" @click="cancelCamera()"
                                            class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-sm text-stone-600 transition hover:bg-stone-100">
                                        Annulla
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- Stato: registrazione in corso --}}
                        <template x-if="state === 'recording'">
                            <div class="mt-3 space-y-2">
                                <div class="relative overflow-hidden rounded-2xl bg-black" style="aspect-ratio:16/9;">
                                    <video autoplay muted playsinline class="h-full w-full object-cover" style="transform:scaleX(-1);"
                                           x-init="$el.srcObject = _stream; $el.play().catch(() => {})"></video>

                                    {{-- Countdown centrato grande --}}
                                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="font-black tabular-nums drop-shadow-[0_2px_8px_rgba(0,0,0,0.7)] leading-none transition-colors duration-500"
                                                  style="font-size:clamp(3rem,10vw,5rem);"
                                                  :class="{
                                                      'text-emerald-300': (60 - elapsed) > 30,
                                                      'text-amber-300':   (60 - elapsed) <= 30 && (60 - elapsed) > 10,
                                                      'text-rose-400 animate-pulse': (60 - elapsed) <= 10
                                                  }"
                                                  x-text="60 - elapsed"></span>
                                            <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-white/60 drop-shadow">sec rimasti</span>
                                        </div>
                                    </div>

                                    {{-- Badge REC top-left --}}
                                    <div class="absolute top-2 left-2 flex items-center gap-1.5 rounded-full bg-rose-600/90 px-3 py-1 text-xs font-bold text-white backdrop-blur">
                                        <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>
                                        REC <span x-text="'0:' + String(60 - elapsed).padStart(2, '0')"></span>
                                    </div>

                                    {{-- Barra progresso --}}
                                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/20">
                                        <div class="h-full bg-rose-500 transition-all duration-1000"
                                             :style="'width:' + (elapsed / 60 * 100) + '%'"></div>
                                    </div>
                                </div>
                                <button type="button" @click="stopRecording()"
                                        class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-100">
                                    ■ Ferma registrazione
                                </button>
                            </div>
                        </template>

                        {{-- Stato: anteprima registrata --}}
                        <template x-if="state === 'recorded'">
                            <div class="mt-3 space-y-2">
                                <div class="overflow-hidden rounded-2xl bg-black" style="aspect-ratio:16/9;">
                                    <video controls class="h-full w-full object-contain"
                                           x-init="if (_blob) { $el.src = URL.createObjectURL(_blob); $el.load(); }"></video>
                                </div>
                                <p class="text-xs text-emerald-700 font-medium">✓ Video pronto. Premi "Salva profilo" per caricarlo.</p>
                                <div class="flex gap-2">
                                    <button type="button" @click="reRecord()"
                                            class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-sm text-stone-600 transition hover:bg-stone-100">
                                        Registra di nuovo
                                    </button>
                                    <button type="button" @click="cancelCamera()"
                                            class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-sm text-stone-400 transition hover:bg-stone-100">
                                        Rimuovi
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- Errore permessi --}}
                        <template x-if="state === 'error'">
                            <p class="mt-2 text-sm text-rose-600" x-text="errorMsg"></p>
                        </template>
                    </div>

                </div>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                    <input type="checkbox" name="show_email" value="1" class="rounded border-stone-300 text-[color:var(--km-accent)] focus:ring-emerald-300" @checked(old('show_email', $profile->show_email))>
                    Mostra email in directory e pagina personale
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                    <input type="checkbox" name="show_phone" value="1" class="rounded border-stone-300 text-[color:var(--km-accent)] focus:ring-emerald-300" @checked(old('show_phone', $profile->show_phone))>
                    Mostra telefono
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                    <input type="checkbox" name="show_whatsapp" value="1" class="rounded border-stone-300 text-[color:var(--km-accent)] focus:ring-emerald-300" @checked(old('show_whatsapp', $profile->show_whatsapp))>
                    Mostra pulsante WhatsApp
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                    <input type="checkbox" name="allow_whatsapp_contact" value="1" class="rounded border-stone-300 text-[color:var(--km-accent)] focus:ring-emerald-300" @checked(old('allow_whatsapp_contact', $profile->allow_whatsapp_contact))>
                    Permetti contatto diretto su WhatsApp
                </label>
                {{-- La visibilità in directory è gestita dall'admin, il membro appare sempre --}}
                <label class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 md:col-span-2">
                    <input type="checkbox" name="onboarding_completed" value="1" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-300" @checked(old('onboarding_completed', $profile->onboarding_completed))>
                    Confermo che i contenuti sono pronti per attivare la mia presenza nella kommunity
                </label>
            </div>
        </div>

        <div class="grid gap-6 border-t border-stone-200 pt-8 lg:grid-cols-2">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-stone-950">Gallery pagina personale</h2>
                <p class="mt-2 text-sm leading-7 text-stone-600">Carica immagini del tuo lavoro, progetti, location o presentazione. Verranno mostrate nella pagina personale.</p>
            </div>
            <div>
                {{-- Solo l'input di upload rimane nel form principale --}}
                <x-input-label for="gallery_images" :value="'Nuove immagini gallery'" />
                <input id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple class="km-input mt-2 block w-full py-2.5">
                <x-input-error class="mt-2" :messages="$errors->get('gallery_images')" />
                <x-input-error class="mt-2" :messages="$errors->get('gallery_images.*')" />
            </div>
        </div>

        <div class="flex items-center gap-4 border-t border-stone-200 pt-8">
            <x-primary-button>Salva profilo</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2400)" class="text-sm text-stone-600">Profilo aggiornato.</p>
            @endif
            @if (session('status') === 'profile-updated-ai')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)" class="text-sm font-medium text-emerald-700">✨ Profilo aggiornato e rielaborato dall'AI.</p>
            @endif
            @if (session('status') === 'gallery-image-deleted')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2400)" class="text-sm text-stone-600">Immagine gallery eliminata.</p>
            @endif
        </div>
    </form>

    @if ($profile->avatarUrl())
        <form id="delete-avatar-form" method="POST" action="{{ route('profile.avatar.destroy') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    @if ($user->memberOnepage?->coverImageUrl())
        <form id="delete-banner-form" method="POST" action="{{ route('profile.banner.destroy') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    @if ($hasExistingVideo)
        <form id="delete-video-form" method="POST" action="{{ route('profile.video.destroy') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <div class="mt-8 rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
        <div class="grid gap-6 lg:grid-cols-2">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-stone-950">Suggerisci un campo</h2>
                <p class="mt-2 text-sm leading-7 text-stone-600">Se non trovi professione, categoria, citta' o altra voce corretta, invia un suggerimento all'admin.</p>
                @if (session('status') === 'suggestion-created')
                    <p class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">Suggerimento inviato all'admin.</p>
                @endif
            </div>
            <form method="POST" action="{{ route('profile.suggestions.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <x-input-label for="suggestion_type" :value="'Tipo suggerimento *'" />
                    <select id="suggestion_type" name="type" class="km-input mt-2" required>
                        <option value="">Seleziona...</option>
                        <option value="profession" @selected(old('type') === 'profession')>Professione</option>
                        <option value="category" @selected(old('type') === 'category')>Categoria</option>
                        <option value="city" @selected(old('type') === 'city')>Citta'</option>
                        <option value="company_interest_type" @selected(old('type') === 'company_interest_type')>Tipologia azienda/gruppo</option>
                        <option value="other" @selected(old('type') === 'other')>Altro</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('type')" />
                </div>
                <div>
                    <x-input-label for="suggestion_value" :value="'Voce proposta *'" />
                    <x-text-input id="suggestion_value" name="value" type="text" class="mt-2 block w-full" :value="old('value')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('value')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="suggestion_notes" :value="'Note per admin'" />
                    <textarea id="suggestion_notes" name="notes" rows="3" class="km-input mt-2" placeholder="Aggiungi contesto utile per valutarla">{{ old('notes') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                </div>
                <div class="md:col-span-2">
                    <x-primary-button>Invia suggerimento</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Gallery esistente — FUORI dal form principale ──────────────────────
         I <form> di eliminazione devono essere a livello top, non annidati
         dentro il <form> del profilo (i browser ignorano i form annidati).  --}}
    @if ($galleryImages->isNotEmpty())
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($galleryImages as $galleryImage)
                <div class="overflow-hidden rounded-[1.6rem] border border-stone-200 bg-white shadow-sm">
                    <img src="{{ $galleryImage->imageUrl() }}"
                         alt="Immagine gallery"
                         class="h-36 w-full object-cover"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    {{-- Fallback se immagine non caricabile --}}
                    <div style="display:none" class="h-36 w-full items-center justify-center bg-stone-100 text-stone-400">
                        <svg class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="flex items-center justify-between gap-3 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-stone-500">Immagine {{ $loop->iteration }}</p>
                        <form method="POST" action="{{ route('profile.gallery.destroy', $galleryImage) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-xs font-semibold text-rose-600 hover:text-rose-800"
                                    onclick="return confirm('Eliminare questa immagine?')">
                                Elimina
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-6 rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 p-5 text-sm text-stone-500">
            Nessuna immagine caricata nella gallery.
        </div>
    @endif

</section>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" crossorigin="anonymous">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" crossorigin="anonymous"></script>
@endpush

<script>
document.addEventListener('alpine:init', () => {

    /* ── Banner cropper 4:1 ────────────────────────────────────────────── */
    Alpine.data('kmBannerCropper', () => ({
        showModal:     false,
        croppedPreview: null,
        _cropper:      null,

        openCropper(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.$refs.cropImg.src = e.target.result;
                this.showModal = true;
                this.$nextTick(() => {
                    if (this._cropper) this._cropper.destroy();
                    this._cropper = new Cropper(this.$refs.cropImg, {
                        aspectRatio: 4,          // 4:1 fisso
                        viewMode: 1,             // non uscire dal contenitore
                        dragMode: 'move',
                        autoCropArea: 1,
                        guides: true,
                        highlight: false,
                        cropBoxResizable: true,
                    });
                });
            };
            reader.readAsDataURL(file);
        },

        confirmCrop() {
            if (!this._cropper) return;
            const canvas = this._cropper.getCroppedCanvas({
                width: 1500,
                height: 375,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });
            // Anteprima
            this.croppedPreview = canvas.toDataURL('image/jpeg', 0.92);
            // Inietta blob nell'input nascosto con name="cover_image"
            canvas.toBlob((blob) => {
                const file = new File([blob], 'banner.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.coverInput.files = dt.files;
            }, 'image/jpeg', 0.92);
            this.showModal = false;
            this._cropper.destroy();
            this._cropper = null;
        },

        cancelCrop() {
            this.showModal = false;
            if (this._cropper) { this._cropper.destroy(); this._cropper = null; }
        },
    }));

    /* ── Multi-select generico ─────────────────────────────────────────── */
    Alpine.data('kmMultiSelect', (options, initialSelected, fieldName) => ({
        options:  options,
        selected: initialSelected.map(Number),
        fieldName: fieldName,
        open:   false,
        search: '',
        get filtered() {
            const q = this.search.toLowerCase();
            return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
        },
        isSelected(id) { return this.selected.includes(Number(id)); },
        toggle(id) {
            id = Number(id);
            this.isSelected(id) ? this.deselect(id) : this.selected.push(id);
        },
        deselect(id) { this.selected = this.selected.filter(s => s !== Number(id)); },
    }));

    /* ── Location picker (Regione → Provincia → Città) ────────────────── */
    Alpine.data('kmLocationPicker', (initRegion, initProvince, initCity) => ({
        regionId:   String(initRegion   ?? ''),
        provinceId: String(initProvince ?? ''),
        cityId:     String(initCity     ?? ''),
        get regions()   { return window._locationData.regions; },
        get provinces() { return window._locationData.provinces; },
        get cities()    { return window._locationData.cities; },
        get filteredProvinces() {
            return this.regionId
                ? this.provinces.filter(p => String(p.region_id) === this.regionId)
                : this.provinces;
        },
        get filteredCities() {
            if (this.provinceId) return this.cities.filter(c => String(c.province_id) === this.provinceId);
            if (this.regionId)   return this.cities.filter(c => String(c.region_id)   === this.regionId);
            return this.cities;
        },
        changeRegion()   { this.provinceId = ''; this.cityId = ''; },
        changeProvince() { this.cityId = ''; },
    }));

    /* ── Video recorder dalla camera ────────────────────────────────────── */
    Alpine.data('kmVideoRecorder', (hasExistingVideo = false) => ({
        state:            'idle',   // idle | preview | recording | recorded | error
        errorMsg:         '',
        elapsed:          0,
        hasExistingVideo: hasExistingVideo,
        _stream:          null,
        _recorder:  null,
        _chunks:    [],
        _timer:     null,
        _blob:      null,
        MAX_SEC:    60,

        async startCamera() {
            if (this.hasExistingVideo) {
                const ok = confirm(
                    'Hai già una videopresentazione attiva.\n\n' +
                    'Vuoi registrarne una nuova in sostituzione?\n' +
                    'Il video precedente sarà eliminato al salvataggio.'
                );
                if (!ok) return;
            }
            try {
                this._stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                this.state = 'preview';
                // srcObject viene assegnato da x-init sull'elemento video al momento del mount
            } catch (e) {
                this.state = 'error';
                this.errorMsg = 'Impossibile accedere alla camera. Controlla i permessi del browser e riprova.';
            }
        },

        startRecording() {
            this._chunks = [];
            this.elapsed = 0;
            const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp9')
                ? 'video/webm;codecs=vp9'
                : (MediaRecorder.isTypeSupported('video/webm') ? 'video/webm' : 'video/mp4');
            this._recorder = new MediaRecorder(this._stream, { mimeType });
            this._recorder.ondataavailable = e => { if (e.data.size > 0) this._chunks.push(e.data); };
            this._recorder.onstop = () => this._onRecordingStop();
            this._recorder.start(250);
            this.state = 'recording';
            // srcObject viene assegnato da x-init sull'elemento video al momento del mount
            this._timer = setInterval(() => {
                this.elapsed++;
                if (this.elapsed >= this.MAX_SEC) this.stopRecording();
            }, 1000);
        },

        stopRecording() {
            clearInterval(this._timer);
            if (this._recorder && this._recorder.state !== 'inactive') this._recorder.stop();
        },

        _onRecordingStop() {
            const ext  = this._chunks[0]?.type?.includes('mp4') ? 'mp4' : 'webm';
            const mime = this._chunks[0]?.type || 'video/webm';
            this._blob = new Blob(this._chunks, { type: mime });
            this.state = 'recorded';
            // src viene assegnato da x-init sull'elemento video al momento del mount
            // Attach al file input tramite DataTransfer (Chrome/Firefox/Safari 17+)
            try {
                const file = new File([this._blob], 'presentazione.' + ext, { type: mime });
                const dt   = new DataTransfer();
                dt.items.add(file);
                const input = document.getElementById('intro_video');
                if (input) input.files = dt.files;
            } catch (_) { /* Safari < 17: upload via file input rimane manuale */ }
            // Ferma stream camera
            this._stream?.getTracks().forEach(t => t.stop());
        },

        reRecord() {
            this._blob = null;
            this._chunks = [];
            this.elapsed = 0;
            const input = document.getElementById('intro_video');
            if (input) input.value = '';
            this.startCamera();
        },

        cancelCamera() {
            clearInterval(this._timer);
            this._recorder?.stop();
            this._stream?.getTracks().forEach(t => t.stop());
            this._blob = null;
            this._chunks = [];
            this.elapsed = 0;
            const input = document.getElementById('intro_video');
            if (input) input.value = '';
            this.state = 'idle';
        },
    }));

});
</script>
