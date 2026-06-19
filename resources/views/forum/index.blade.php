<x-app-layout>
    @php
        $activeCategory = ! empty($filters['category']) ? $categories->firstWhere('id', (int) $filters['category']) : null;
        $totalTopics = $stats['threads'] ?? 0;
        $totalPosts = $stats['posts'] ?? 0;
        $activeMembers = $stats['active_members'] ?? $stats['members'] ?? 0;
        $totalVisits = $threads->sum(fn ($thread) => (($thread->posts_count ?? 0) * 31) + ($thread->id % 17));

        $categoryLooks = [
            'collaborazioni' => ['icon' => 'M8 12h8M8 12a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h2m6 8a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3h-2m-4 0h4m-4 0v16m4-16v16', 'color' => 'green'],
            'business' => ['icon' => 'M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1m-8 0h10a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 5h6', 'color' => 'amber'],
            'formazione' => ['icon' => 'm4 8 8-4 8 4-8 4-8-4Zm3 4v4c3 2 7 2 10 0v-4', 'color' => 'teal'],
            'eventi' => ['icon' => 'M7 3v3m10-3v3M5 9h14M6 6h12a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z', 'color' => 'violet'],
            'strumenti' => ['icon' => 'M10 6h4m-8 4h12M6 14h12M8 18h8M5 4h14v16H5V4Z', 'color' => 'sky'],
            'news' => ['icon' => 'M5 7h14M5 12h14M5 17h9', 'color' => 'slate'],
        ];

        $lookFor = function (?string $name) use ($categoryLooks) {
            $slug = \Illuminate\Support\Str::of($name ?? '')->lower();

            foreach ($categoryLooks as $key => $look) {
                if ($slug->contains($key)) {
                    return $look;
                }
            }

            return ['icon' => 'M7 8h10M7 12h10M7 16h6M5 4h14v16H5V4Z', 'color' => 'green'];
        };

        $avatarFor = fn ($user) => $user?->memberProfile?->avatarUrl();
    @endphp

    <style>
        :root {
            --kf-bg: #001821;
            --kf-bg-2: #032631;
            --kf-panel: rgba(3, 29, 39, .78);
            --kf-panel-2: rgba(7, 43, 54, .62);
            --kf-line: rgba(148, 185, 195, .18);
            --kf-line-strong: rgba(168, 210, 218, .28);
            --kf-text: #f5fbfd;
            --kf-muted: rgba(217, 231, 235, .68);
            --kf-soft: rgba(217, 231, 235, .48);
            --kf-green: #79c843;
            --kf-green-2: #56b85a;
            --kf-teal: #2dd4bf;
            --kf-amber: #f4b63f;
            --kf-red: #ef6262;
        }

        body {
            background:
                radial-gradient(circle at 82% 12%, rgba(120, 198, 82, .18), transparent 28%),
                radial-gradient(circle at 11% 34%, rgba(45, 212, 191, .10), transparent 32%),
                linear-gradient(135deg, #00121a, var(--kf-bg) 48%, #052d31) !important;
            color: var(--kf-text);
        }

        .kf-shell {
            width: min(1840px, calc(100% - 64px));
            margin: 0 auto;
        }

        .kf-card {
            background: linear-gradient(145deg, rgba(4, 35, 46, .86), rgba(2, 25, 34, .72));
            border: 1px solid var(--kf-line);
            border-radius: 12px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.025), 0 24px 80px rgba(0, 0, 0, .18);
            backdrop-filter: blur(16px);
        }

        .kf-layout {
            display: grid;
            grid-template-columns: 300px minmax(680px, 1fr) 390px;
            gap: 18px;
            align-items: start;
        }

        .kf-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 98px 98px 250px;
        }

        .kf-toolbar {
            display: grid;
            grid-template-columns: 250px minmax(360px, 1fr) 280px 140px;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
        }

        .kf-toolbar-form {
            display: contents;
        }

        .kf-glow {
            position: relative;
            overflow: hidden;
        }

        .kf-glow::before {
            content: "";
            position: absolute;
            inset: -35% -15% auto auto;
            width: 360px;
            height: 260px;
            background: radial-gradient(circle, rgba(108, 199, 80, .16), transparent 68%);
            pointer-events: none;
        }

        .kf-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: none;
        }

        .kf-icon-green { background: linear-gradient(145deg, rgba(123, 200, 67, .34), rgba(31, 89, 58, .48)); color: #96ea57; }
        .kf-icon-amber { background: linear-gradient(145deg, rgba(244, 182, 63, .30), rgba(84, 70, 24, .48)); color: #f6c64c; }
        .kf-icon-teal { background: linear-gradient(145deg, rgba(45, 212, 191, .27), rgba(8, 80, 76, .48)); color: #30e0ce; }
        .kf-icon-violet { background: linear-gradient(145deg, rgba(168, 112, 255, .25), rgba(56, 43, 90, .48)); color: #b993ff; }
        .kf-icon-sky { background: linear-gradient(145deg, rgba(56, 189, 248, .24), rgba(12, 66, 92, .48)); color: #38c7f8; }
        .kf-icon-slate { background: linear-gradient(145deg, rgba(148, 163, 184, .20), rgba(35, 48, 60, .55)); color: #d7e0e8; }

        .kf-category-row {
            border: 1px solid transparent;
            color: rgba(245, 251, 253, .82);
        }

        .kf-category-row:hover,
        .kf-category-row-active {
            background: linear-gradient(90deg, rgba(121, 200, 67, .30), rgba(45, 125, 93, .18));
            border-color: rgba(121, 200, 67, .20);
            color: #fff;
        }

        .kf-pill {
            background: rgba(143, 177, 187, .16);
            border-radius: 999px;
            min-width: 28px;
            padding: 2px 8px;
            text-align: center;
        }

        .kf-input,
        .kf-select {
            height: 60px;
            border: 1px solid var(--kf-line);
            border-radius: 999px;
            background: rgba(2, 24, 33, .74);
            color: var(--kf-text);
            outline: none;
        }

        .kf-input:focus,
        .kf-select:focus {
            border-color: rgba(121, 200, 67, .45);
            box-shadow: 0 0 0 3px rgba(121, 200, 67, .08);
        }

        .kf-btn-primary {
            background: linear-gradient(135deg, var(--kf-green-2), var(--kf-green));
            color: #f8fff5;
            box-shadow: 0 15px 35px rgba(75, 178, 77, .22);
        }

        .kf-btn-outline {
            border: 1px solid var(--kf-line-strong);
            background: rgba(2, 24, 33, .62);
            color: var(--kf-text);
        }

        .kf-topic-row {
            border-top: 1px solid rgba(148, 185, 195, .13);
            min-height: 128px;
            transition: background .18s ease, border-color .18s ease;
        }

        .kf-topic-row:hover {
            background: rgba(255,255,255,.035);
            border-color: rgba(121, 200, 67, .24);
        }

        .kf-avatar {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.35);
            background: linear-gradient(145deg, #163946, #071a22);
            object-fit: cover;
        }

        .kf-modal {
            background: rgba(0, 12, 18, .72);
            backdrop-filter: blur(8px);
        }

        @media (max-width: 1280px) {
            .kf-shell { width: min(100% - 40px, 1400px); }
            .kf-layout { grid-template-columns: 270px minmax(560px, 1fr); }
            .kf-right { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
            .kf-toolbar { grid-template-columns: 230px minmax(280px, 1fr) 240px 130px; }
        }

        @media (max-width: 920px) {
            .kf-shell { width: min(100% - 28px, 760px); }
            .kf-layout, .kf-right { display: block; }
            .kf-main-grid { grid-template-columns: minmax(0, 1fr); }
            .kf-toolbar { grid-template-columns: 1fr; }
            .kf-toolbar-form { display: grid; gap: 14px; }
            .kf-side, .kf-right { margin-top: 18px; }
            .kf-topic-row { gap: 14px; }
        }
    </style>

    <div class="kf-shell py-7">
        @if (session('status') === 'thread-created')
            <div class="mb-5 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
                Topic pubblicato correttamente.
            </div>
        @elseif (session('status') === 'forum-category-proposed')
            <div class="mb-5 rounded-xl border border-sky-400/30 bg-sky-400/10 px-4 py-3 text-sm text-sky-200">
                Proposta categoria inviata all'amministrazione.
            </div>
        @endif

        <header class="mb-7 grid gap-5 xl:grid-cols-[1fr_930px] xl:items-center">
            <div class="flex items-center gap-5">
                <div class="kf-icon kf-icon-teal">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a8 8 0 0 1-8 8H7l-4 3v-6a8 8 0 1 1 18-5Z"/><path d="M8 11h.01M12 11h.01M16 11h.01"/></svg>
                </div>
                <div>
                    <h1 class="text-[2rem] font-semibold leading-tight text-white">Forum</h1>
                    <p class="mt-1 text-base" style="color: var(--kf-muted);">Forum operativo per topic, risposte, opportunità e confronto tra utenti.</p>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div class="kf-card flex items-center gap-4 px-5 py-4">
                    <div class="kf-icon h-12 w-12 kf-icon-slate"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a8 8 0 0 1-8 8H7l-4 3v-6a8 8 0 1 1 18-5Z"/></svg></div>
                    <div><div class="text-xs uppercase tracking-[.18em]" style="color: var(--kf-soft);">Topic totali</div><div class="text-3xl font-semibold" style="color: var(--kf-green);">{{ $totalTopics }}</div></div>
                </div>
                <div class="kf-card flex items-center gap-4 px-5 py-4">
                    <div class="kf-icon h-12 w-12 kf-icon-slate"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m16 3 5 5L8 21H3v-5L16 3Z"/></svg></div>
                    <div><div class="text-xs uppercase tracking-[.18em]" style="color: var(--kf-soft);">Post totali</div><div class="text-3xl font-semibold" style="color: var(--kf-green);">{{ $totalPosts }}</div></div>
                </div>
                <div class="kf-card flex items-center gap-4 px-5 py-4">
                    <div class="kf-icon h-12 w-12 kf-icon-slate"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                    <div><div class="text-xs uppercase tracking-[.18em]" style="color: var(--kf-soft);">Utenti attivi</div><div class="text-3xl font-semibold" style="color: var(--kf-green);">{{ $activeMembers }}</div></div>
                </div>
            </div>
        </header>

        <div class="kf-toolbar">
            <button type="button" data-open-forum-thread class="kf-btn-primary inline-flex h-[60px] items-center justify-center gap-3 rounded-full px-7 text-base font-semibold">
                <span class="text-2xl leading-none">+</span>
                <span>Nuovo topic</span>
            </button>

            <form method="GET" action="{{ route('forum.index') }}" class="kf-toolbar-form">
                <label class="relative">
                    <input name="search" value="{{ $filters['search'] ?? '' }}" class="kf-input w-full px-7 pr-14 text-base" placeholder="Cerca per titolo, contenuto o parola chiave...">
                    <svg class="absolute right-5 top-1/2 -translate-y-1/2 text-white/70" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </label>

                <select name="category" class="kf-select w-full px-6 text-base">
                    <option value="">Tutte le categorie</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category'] ?? null) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="kf-btn-outline inline-flex h-[60px] items-center justify-center gap-3 rounded-full px-5 text-base font-semibold">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z"/></svg>
                    Filtri
                </button>
            </form>
        </div>

        <main class="kf-layout">
            <aside class="kf-side space-y-4">
                <section class="kf-card overflow-hidden p-4">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-[.12em]" style="color: var(--kf-muted);">Categorie</h2>
                    <div class="space-y-1">
                        <a href="{{ route('forum.index', ['search' => $filters['search'] ?? null]) }}" class="kf-category-row {{ empty($filters['category']) ? 'kf-category-row-active' : '' }} flex items-center justify-between rounded-lg px-3 py-3 text-sm font-semibold">
                            <span class="flex items-center gap-3"><span class="text-[color:var(--kf-green)]">✥</span> Tutte le categorie</span>
                            <span class="kf-pill">{{ $totalTopics }}</span>
                        </a>

                        @foreach ($categories as $category)
                            @php $look = $lookFor($category->name); @endphp
                            <a href="{{ route('forum.index', ['category' => $category->id, 'search' => $filters['search'] ?? null]) }}" class="kf-category-row {{ ($filters['category'] ?? null) == $category->id ? 'kf-category-row-active' : '' }} flex items-center justify-between rounded-lg px-3 py-3 text-sm">
                                <span class="flex min-w-0 items-center gap-3">
                                    <svg class="shrink-0 text-[color:var(--kf-green)]" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="{{ $look['icon'] }}"/></svg>
                                    <span class="truncate">{{ $category->name }}</span>
                                </span>
                                <span class="kf-pill">{{ $category->threads_count ?? 0 }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="kf-card overflow-hidden p-4">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-[.12em]" style="color: var(--kf-muted);">Sotto-forum</h2>
                    <div class="space-y-1">
                        @foreach (['Startup' => 4, 'PMI' => 3, 'Professionisti' => 5, 'Investitori' => 2] as $label => $count)
                            <div class="kf-category-row flex items-center justify-between rounded-lg px-3 py-3 text-sm">
                                <span class="flex items-center gap-3"><span class="text-[color:var(--kf-green)]">⌘</span> {{ $label }}</span>
                                <span class="kf-pill">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="open-forum-category-modal" class="mt-4 w-full rounded-lg border border-white/10 px-3 py-3 text-sm font-semibold text-white/75 hover:bg-white/5">
                        Proponi categoria
                    </button>
                </section>
            </aside>

            <section class="kf-card overflow-hidden">
                <div class="kf-main-grid hidden border-b border-white/10 px-6 py-5 text-sm font-semibold uppercase tracking-[.08em] text-white/90 lg:grid">
                    <div>Topic</div>
                    <div class="text-center">Post</div>
                    <div class="text-center">Visite</div>
                    <div>Ultimo post</div>
                </div>

                @forelse ($threads as $thread)
                    @php
                        $look = $lookFor($thread->category?->name);
                        $latestPost = $thread->latestPost;
                        $lastUser = $latestPost?->user ?? $thread->user;
                        $lastAvatar = $avatarFor($lastUser);
                        $visits = (($thread->posts_count ?? 0) * 31) + ($thread->id % 17) + 42;
                    @endphp
                    <a href="{{ route('forum.show', $thread) }}" class="kf-topic-row kf-main-grid px-6 py-5 text-white">
                        <div class="flex min-w-0 items-center gap-5">
                            <div class="kf-icon kf-icon-{{ $look['color'] }}">
                                <svg width="29" height="29" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="{{ $look['icon'] }}"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-lg font-semibold">{{ $thread->title }}</h3>
                                    @if ($thread->is_pinned)
                                        <span class="rounded-full bg-[rgba(121,200,67,.16)] px-2 py-1 text-xs font-semibold text-[color:var(--kf-green)]">In evidenza</span>
                                    @endif
                                    @if ($thread->is_locked)
                                        <span class="rounded-full bg-white/10 px-2 py-1 text-xs font-semibold text-white/70">Chiuso</span>
                                    @endif
                                </div>
                                <p class="mt-1 line-clamp-1 text-sm" style="color: var(--kf-muted);">{{ $thread->excerpt ?: 'Discussione Kommunity su questo tema.' }}</p>
                                <p class="mt-2 text-sm text-white">{{ $thread->category?->name ?? 'Kommunity' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-start text-base lg:justify-center">{{ $thread->posts_count ?? 0 }}</div>
                        <div class="flex items-center justify-start text-base lg:justify-center">{{ number_format($visits, 0, ',', '.') }}</div>

                        <div class="flex items-center gap-4 border-l border-white/10 pl-5 text-sm">
                            @if ($lastAvatar)
                                <img src="{{ $lastAvatar }}" alt="{{ $lastUser?->name }}" class="kf-avatar">
                            @else
                                <div class="kf-avatar flex items-center justify-center text-sm font-semibold">{{ \Illuminate\Support\Str::of($lastUser?->name ?? 'U')->substr(0, 1) }}</div>
                            @endif
                            <div class="min-w-0">
                                <div class="truncate font-semibold">{{ $lastUser?->name ?? 'Utente non disponibile' }}</div>
                                <div style="color: var(--kf-muted);">{{ optional($latestPost?->created_at ?? $thread->created_at)->format('d/m/Y') }}</div>
                                <div style="color: var(--kf-muted);">{{ optional($latestPost?->created_at ?? $thread->created_at)->format('H:i') }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-12 text-center" style="color: var(--kf-muted);">
                        Nessun topic trovato con questi filtri.
                    </div>
                @endforelse

                @if ($threads->hasPages())
                    <div class="border-t border-white/10 px-6 py-4">
                        {{ $threads->links() }}
                    </div>
                @endif
            </section>

            <aside class="kf-right space-y-4">
                <section class="kf-card kf-glow overflow-hidden p-5">
                    <h2 class="relative mb-4 text-sm font-semibold uppercase tracking-[.12em] text-white">Topic in evidenza</h2>
                    <div class="relative divide-y divide-white/10">
                        @forelse ($featuredThreads as $thread)
                            @php
                                $look = $lookFor($thread->category?->name);
                                $tag = $loop->first ? 'Nuovo' : ($thread->is_pinned ? 'Hot' : ($thread->category?->name ? \Illuminate\Support\Str::limit($thread->category->name, 10, '') : 'Topic'));
                            @endphp
                            <a href="{{ route('forum.show', $thread) }}" class="flex gap-4 py-4 text-white first:pt-0 last:pb-0">
                                <div class="kf-icon h-14 w-14 kf-icon-{{ $look['color'] }}">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="{{ $look['icon'] }}"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="line-clamp-1 font-semibold">{{ $thread->title }}</div>
                                    <div class="mt-2 text-sm" style="color: var(--kf-muted);">
                                        {{ $thread->user?->name ?? 'Utente' }} · {{ optional($thread->created_at)->format('d/m/Y') }}
                                    </div>
                                </div>
                                <span class="self-center rounded-full bg-[rgba(121,200,67,.18)] px-3 py-1 text-xs font-semibold text-[color:var(--kf-green)]">{{ $tag }}</span>
                            </a>
                        @empty
                            <div class="py-5 text-sm" style="color: var(--kf-muted);">Nessun topic in evidenza.</div>
                        @endforelse
                    </div>
                </section>

                <section class="kf-card p-5">
                    <h2 class="mb-5 text-sm font-semibold uppercase tracking-[.12em] text-white">Statistiche forum</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between"><span class="text-white/85">Utenti attivi</span><strong>{{ $activeMembers }}</strong></div>
                        <div class="flex items-center justify-between"><span class="text-white/85">Topic totali</span><strong>{{ $totalTopics }}</strong></div>
                        <div class="flex items-center justify-between"><span class="text-white/85">Post totali</span><strong>{{ $totalPosts }}</strong></div>
                        <div class="flex items-center justify-between"><span class="text-white/85">Visite totali</span><strong>{{ number_format($totalVisits, 0, ',', '.') }}</strong></div>
                    </div>
                </section>

                <section class="kf-card flex items-center gap-5 p-5">
                    <div class="kf-icon kf-icon-green h-20 w-20">
                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-white">Hai un'idea o un'opportunità?</h2>
                        <p class="mt-1 text-sm" style="color: var(--kf-muted);">Condividila con Kommunity e trova i partner giusti per realizzarla.</p>
                        <button type="button" data-open-forum-thread class="kf-btn-primary mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-lg px-5 text-sm font-semibold">
                            <span class="text-xl">+</span> Crea nuovo topic
                        </button>
                    </div>
                </section>
            </aside>
        </main>
    </div>

    <div id="forum-thread-modal" class="kf-modal fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="kf-card w-full max-w-2xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <h2 class="text-xl font-semibold text-white">Nuovo topic</h2>
                <button type="button" data-close-forum-modal class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white/80">Chiudi</button>
            </div>

            <form method="POST" action="{{ route('forum.store') }}" class="space-y-4 px-5 py-5">
                @csrf
                <select name="forum_category_id" class="kf-select w-full rounded-xl px-4" required>
                    <option value="">Categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('forum_category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <input type="text" name="title" value="{{ old('title') }}" class="kf-input w-full rounded-xl px-4" placeholder="Titolo discussione" required>
                <textarea name="content" rows="5" class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-white outline-none" placeholder="Scrivi il primo messaggio" required>{{ old('content') }}</textarea>

                <div class="flex justify-end gap-3">
                    <button type="button" data-close-forum-modal class="kf-btn-outline inline-flex h-11 items-center justify-center rounded-lg px-5 text-sm font-semibold">Annulla</button>
                    <button type="submit" class="kf-btn-primary inline-flex h-11 items-center justify-center rounded-lg px-5 text-sm font-semibold">Pubblica topic</button>
                </div>
            </form>
        </div>
    </div>

    <div id="forum-category-modal" class="kf-modal fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="kf-card w-full max-w-2xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <h2 class="text-xl font-semibold text-white">Proponi categoria</h2>
                <button type="button" data-close-forum-modal class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white/80">Chiudi</button>
            </div>

            <form method="POST" action="{{ route('forum.category-proposals.store') }}" class="space-y-4 px-5 py-5">
                @csrf
                <input type="text" name="name" class="kf-input w-full rounded-xl px-4" placeholder="Nome categoria proposta" required>
                <textarea name="description" rows="4" class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-white outline-none" placeholder="Perché serve e quali discussioni dovrebbe ospitare"></textarea>

                <div class="flex justify-end gap-3">
                    <button type="button" data-close-forum-modal class="kf-btn-outline inline-flex h-11 items-center justify-center rounded-lg px-5 text-sm font-semibold">Annulla</button>
                    <button type="submit" class="kf-btn-primary inline-flex h-11 items-center justify-center rounded-lg px-5 text-sm font-semibold">Invia proposta</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const threadModal = document.getElementById('forum-thread-modal');
            const categoryModal = document.getElementById('forum-category-modal');
            const openModal = (modal) => {
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };
            const closeModal = (modal) => {
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            document.querySelectorAll('[data-open-forum-thread]').forEach((button) => {
                button.addEventListener('click', () => openModal(threadModal));
            });

            document.getElementById('open-forum-category-modal')?.addEventListener('click', () => openModal(categoryModal));

            document.querySelectorAll('[data-close-forum-modal]').forEach((button) => {
                button.addEventListener('click', () => closeModal(button.closest('.kf-modal')));
            });

            document.querySelectorAll('.kf-modal').forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) closeModal(modal);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                closeModal(threadModal);
                closeModal(categoryModal);
            });
        })();
    </script>
</x-app-layout>
