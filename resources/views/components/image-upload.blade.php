@props([
    'name',                  // attribute name (es. "avatar")
    'label',                 // label visibile
    'currentUrl' => null,    // URL immagine corrente (puo' essere null)
    'shape' => 'square',     // square (avatar) | wide (banner)
    'fallback' => null,      // testo (es. iniziali) se nessuna immagine
    'hint' => null,          // testo aiuto sotto
    'maxMb' => 4,            // limite MB lato client
    'accept' => 'image/png,image/jpeg,image/webp',
])

@php
    $componentId = 'imgup_' . $name;
    $isWide = $shape === 'wide';
    $aspect = $isWide ? 'aspect-[16/6]' : 'aspect-square';
    $maxSizeBytes = $maxMb * 1024 * 1024;
@endphp

<div x-data="{
        url: @js($currentUrl),
        hasFile: false,
        fileName: '',
        error: null,
        dragOver: false,
        maxBytes: {{ $maxSizeBytes }},
        accept: @js($accept),
        onSelect(event) {
            const file = event.target.files?.[0];
            if (! file) return;
            this.handleFile(file, event.target);
        },
        onDrop(event) {
            this.dragOver = false;
            const file = event.dataTransfer?.files?.[0];
            if (! file) return;
            const input = this.$refs.input;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            this.handleFile(file, input);
        },
        handleFile(file, input) {
            this.error = null;
            if (file.size > this.maxBytes) {
                this.error = 'File troppo grande (max {{ $maxMb }} MB).';
                input.value = '';
                return;
            }
            if (this.accept && ! this.accept.split(',').some(t => file.type === t.trim())) {
                this.error = 'Formato non supportato. Usa PNG, JPG o WEBP.';
                input.value = '';
                return;
            }
            this.url = URL.createObjectURL(file);
            this.hasFile = true;
            this.fileName = file.name;
        },
        clear() {
            this.url = @js($currentUrl);
            this.hasFile = false;
            this.fileName = '';
            this.error = null;
            this.$refs.input.value = '';
        }
     }"
     class="space-y-2">

    <div class="flex items-center justify-between">
        <label for="{{ $componentId }}" class="text-sm font-semibold text-stone-700">{{ $label }}</label>
        <span class="text-xs text-stone-400">PNG · JPG · WEBP &middot; max {{ $maxMb }} MB</span>
    </div>

    <div @click="$refs.input.click()"
         @dragover.prevent="dragOver = true"
         @dragleave.prevent="dragOver = false"
         @drop.prevent="onDrop($event)"
         :class="dragOver ? 'border-[color:var(--km-accent)] bg-[rgba(66,98,64,0.05)]' : 'border-stone-200 hover:border-stone-300 bg-white'"
         class="group relative cursor-pointer overflow-hidden rounded-2xl border-2 border-dashed transition">

        <div class="relative {{ $aspect }} w-full">
            {{-- Anteprima immagine --}}
            <template x-if="url">
                <img :src="url"
                     x-on:error="url = null"
                     alt="{{ $label }}"
                     class="h-full w-full object-cover {{ $isWide ? '' : 'rounded-t-xl' }}">
            </template>

            {{-- Fallback (no image) --}}
            <template x-if="! url">
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-stone-100 to-stone-200">
                    @if ($fallback)
                        <span class="text-3xl font-bold text-stone-400">{{ $fallback }}</span>
                    @else
                        <div class="flex flex-col items-center gap-1.5 text-stone-400">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="9" cy="9" r="2"/>
                                <path d="m21 15-5-5L5 21"/>
                            </svg>
                            <span class="text-[11px] font-semibold uppercase tracking-wider">Nessuna immagine</span>
                        </div>
                    @endif
                </div>
            </template>

            {{-- Overlay hover (desktop) --}}
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition group-hover:bg-black/35 group-hover:opacity-100">
                <span class="rounded-full border border-white/40 bg-white/15 px-3 py-1 text-xs font-bold text-white backdrop-blur-sm">
                    <span x-text="hasFile ? 'Cambia file' : (url ? 'Sostituisci' : 'Carica immagine')"></span>
                </span>
            </div>
        </div>

        {{-- Footer con nome file / azioni --}}
        <div class="flex items-center justify-between gap-2 border-t border-stone-100 bg-stone-50/80 px-3 py-2">
            <div class="min-w-0 flex-1 text-xs">
                <template x-if="hasFile">
                    <span class="flex items-center gap-1.5 truncate text-stone-700">
                        <svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                        <span class="truncate font-medium" x-text="fileName"></span>
                    </span>
                </template>
                <template x-if="! hasFile && url">
                    <span class="text-stone-500">Immagine attuale &middot; clicca per sostituire</span>
                </template>
                <template x-if="! hasFile && ! url">
                    <span class="text-stone-500">Trascina un file qui o clicca per selezionare</span>
                </template>
            </div>
            <button type="button"
                    x-show="hasFile"
                    @click.stop="clear()"
                    class="shrink-0 rounded-md px-2 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50">
                Annulla
            </button>
        </div>

        <input
            x-ref="input"
            id="{{ $componentId }}"
            name="{{ $name }}"
            type="file"
            accept="{{ $accept }}"
            class="sr-only"
            @change="onSelect($event)"
        >
    </div>

    <template x-if="error">
        <p class="text-xs font-medium text-rose-600" x-text="error"></p>
    </template>

    @if ($hint)
        <p class="text-xs text-stone-500">{{ $hint }}</p>
    @endif

    {{-- Errori validazione server --}}
    <x-input-error class="mt-1" :messages="$errors->get($name)" />
</div>
