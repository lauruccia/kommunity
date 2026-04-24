<div class="relative mt-2">
    {{-- Selected tags + trigger --}}
    <div @click="open = !open"
         class="km-input flex min-h-[44px] cursor-pointer flex-wrap items-center gap-1.5 py-2 pr-8">
        <template x-for="id in selected" :key="id">
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 py-0.5 pl-2.5 pr-1.5 text-xs font-medium text-emerald-800">
                <span x-text="options.find(o => o.id == id)?.label ?? id"></span>
                <button type="button" @click.stop="deselect(id)"
                        class="flex h-3.5 w-3.5 items-center justify-center rounded-full text-emerald-600 hover:bg-emerald-200 hover:text-emerald-900">
                    <svg viewBox="0 0 12 12" fill="currentColor" class="h-2.5 w-2.5"><path d="M2 2l8 8M10 2l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </span>
        </template>
        <span x-show="selected.length === 0" class="text-sm text-stone-400">Seleziona...</span>
        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-stone-400">
            <svg class="h-4 w-4 transition-transform duration-150" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </span>
    </div>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         @click.outside="open = false"
         class="absolute z-50 mt-1 w-full overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-lg">
        <div class="border-b border-stone-100 px-3 py-2">
            <input type="text" x-model="search"
                   @click.stop
                   placeholder="Cerca..."
                   class="w-full rounded-xl border-0 bg-stone-50 px-3 py-1.5 text-sm placeholder-stone-400 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
        </div>
        <div class="max-h-52 overflow-y-auto py-1">
            <template x-for="opt in filtered" :key="opt.id">
                <label class="flex cursor-pointer items-center gap-3 px-4 py-2.5 text-sm transition"
                       :class="isSelected(opt.id) ? 'bg-emerald-50 text-emerald-800 font-medium' : 'text-stone-700 hover:bg-stone-50'">
                    <input type="checkbox" :checked="isSelected(opt.id)" @change="toggle(opt.id)"
                           @click.stop
                           class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-300">
                    <span x-text="opt.label"></span>
                </label>
            </template>
            <p x-show="filtered.length === 0" class="px-4 py-3 text-sm text-stone-400">Nessun risultato.</p>
        </div>
    </div>

    {{-- Hidden inputs for form submission --}}
    <template x-for="id in selected" :key="id">
        <input type="hidden" :name="fieldName + '[]'" :value="id">
    </template>
</div>
