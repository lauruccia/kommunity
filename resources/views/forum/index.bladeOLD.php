<x-app-layout>
    <x-slot name="header">
        @php
            $activeCategory = !empty($filters['category']) ? $categories->firstWhere('id', (int) $filters['category']) : null;
        @endphp
        <div class="km-panel overflow-hidden p-0">
            <div class="px-6 py-5" style="background: linear-gradient(180deg, rgba(232,239,247,0.96) 0%, rgba(214,226,238,0.96) 100%); color: #1f3445;">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="rounded-[0.85rem] border px-4 py-3" style="background: rgba(255,255,255,0.70); border-color: rgba(120,150,175,0.28);">
                            <div style="font-size: 2rem; line-height: 1; font-weight: 700; letter-spacing: -0.04em; color: #0f76b0;">forum</div>
                            <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.22em; color: #5f778b;">Kommunity Board</div>
                        </div>
                        <div>
                            <h1 class="text-3xl font-semibold">Board discussioni membri</h1>
                            <p class="mt-1 text-sm" style="color: #5f778b;">Forum operativo per topic, risposte, opportunita e confronto tra membri.</p>
                        </div>
                    </div>
                    <div class="grid w-full gap-3 sm:w-auto sm:grid-cols-3">
                        <div class="rounded-[0.85rem] border px-4 py-3" style="background: rgba(255,255,255,0.72); border-color: rgba(120,150,175,0.28);">
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.18em; color: #6a8599;">Discussioni</div>
                            <div class="mt-1 text-2xl font-semibold text-[#1f3445]">{{ $stats['threads'] }}</div>
                        </div>
                        <div class="rounded-[0.85rem] border px-4 py-3" style="background: rgba(255,255,255,0.72); border-color: rgba(120,150,175,0.28);">
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.18em; color: #6a8599;">Messaggi</div>
                            <div class="mt-1 text-2xl font-semibold text-[#1f3445]">{{ $stats['posts'] }}</div>
                        </div>
                        <div class="rounded-[0.85rem] border px-4 py-3" style="background: rgba(255,255,255,0.72); border-color: rgba(120,150,175,0.28);">
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.18em; color: #6a8599;">Membri</div>
                            <div class="mt-1 text-2xl font-semibold text-[#1f3445]">{{ $stats['members'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-4 border-t border-stone-200 bg-white/80 px-5 py-3 xl:flex-row xl:items-center xl:justify-between">
                <div class="font-semibold text-[#35526b]">
                    <a href="{{ route('forum.index') }}" class="hover:text-[#0f76b0]">Board index</a>
                    @if ($activeCategory)
                        <span class="mx-2 text-[#7a93a5]">/</span>
                        <span>{{ $activeCategory->name }}</span>
                    @endif
                </div>
                <div class="grid w-full gap-3 sm:flex sm:w-auto sm:flex-wrap">
                    <button type="button" id="open-forum-thread-modal" class="inline-flex h-11 items-center justify-center rounded-full px-6 text-sm font-semibold text-white" style="background: #4f7d4a;">Nuovo topic</button>
                    <button type="button" id="open-forum-category-modal" class="inline-flex h-11 items-center justify-center rounded-full border px-6 text-sm font-semibold text-[#35526b]" style="background: #ffffff; border-color: #c8d7e2;">Proponi categoria</button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="km-shell space-y-5">
            @if (session('status') === 'thread-created')
                <div class="rounded-[0.9rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Thread pubblicato correttamente.
                </div>
            @elseif (session('status') === 'forum-category-proposed')
                <div class="rounded-[0.9rem] border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                    Proposta categoria inviata all'amministrazione.
                </div>
            @endif

            <section class="km-panel overflow-hidden p-0">
                <div class="flex flex-col gap-4 border-b border-[#cadce9] bg-[#eef5fb] px-5 py-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.24em] text-[#6a8599]">Indice forum</div>
                        <h2 class="mt-1 text-[2rem] font-semibold text-[#1f3445]">Categorie e sotto-forum</h2>
                    </div>
                    <form method="GET" class="grid gap-3 md:grid-cols-2 xl:w-[820px]">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="h-12 rounded-[1rem] border border-[#cad8e4] bg-white px-4 text-[15px] text-[#294256] outline-none" placeholder="Cerca per titolo, contenuto o parola chiave">
                        <select name="category" class="h-12 rounded-[1rem] border border-[#cad8e4] bg-white px-4 text-[15px] text-[#294256] outline-none">
                            <option value="">Tutte le categorie</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(($filters['category'] ?? null) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full px-6 text-sm font-semibold text-white" style="background: #4f7d4a;">Filtra</button>
                        <a href="{{ route('forum.index') }}" class="inline-flex h-12 items-center justify-center rounded-full border px-6 text-sm font-semibold text-[#35526b]" style="background: #ffffff; border-color: #c8d7e2;">Reset</a>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[48rem] text-sm xl:min-w-full">
                        <thead style="background: linear-gradient(180deg, #7ea1b8 0%, #607b8f 100%); color: #ffffff;">
                            <tr class="text-left text-[11px] font-semibold uppercase tracking-[0.18em]">
                                <th class="px-5 py-3">Forum</th>
                                <th class="w-[110px] px-4 py-3 text-center">Topics</th>
                                <th class="w-[110px] px-4 py-3 text-center">Posts</th>
                                <th class="w-[300px] px-5 py-3">Last post</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d8e8f2]">
                            @foreach ($categories as $category)
                                @php
                                    $latestThread = $category->threads->first();
                                    $latestPost = $latestThread?->latestPost;
                                @endphp
                                <tr class="bg-[#f8fbfe] transition hover:bg-[#eef5fb]">
                                    <td class="px-5 py-4">
                                        <div class="flex gap-4">
                                            <div class="mt-1 flex h-11 w-11 items-center justify-center rounded-full border border-[#a9c7dd] bg-white text-sm font-semibold text-[#0f76b0]">F</div>
                                            <div class="min-w-0">
                                                <a href="{{ route('forum.index', ['category' => $category->id, 'search' => $filters['search'] ?? null]).'#topics-list' }}" class="block">
                                                    <div class="text-lg font-semibold text-[#23425a] hover:text-[#0f76b0]">{{ $category->name }}</div>
                                                    <div class="mt-1 text-sm leading-6 text-[#5a7488]">{{ $category->description ?: 'Discussioni della community su questo tema.' }}</div>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center font-semibold text-[#23425a]">{{ $category->threads_count }}</td>
                                    <td class="px-4 py-4 text-center font-semibold text-[#23425a]">{{ $category->posts_count }}</td>
                                    <td class="px-5 py-4 text-sm text-[#4f6b80]">
                                        @if ($latestThread && $latestPost)
                                            <a href="{{ route('forum.show', $latestThread) }}" class="font-semibold text-[#0f76b0] hover:underline">{{ $latestThread->title }}</a>
                                            <div class="mt-1">by {{ $latestPost->user?->name }} · {{ $latestPost->created_at->format('d/m/Y H:i') }}</div>
                                        @else
                                            No posts
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="topics-list" class="km-panel overflow-hidden p-0">
                <div class="flex flex-col gap-4 border-b border-[#cadce9] bg-[#eef5fb] px-5 py-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.22em] text-[#6a8599]">Lista topic</div>
                        <h3 class="mt-1 text-[2rem] font-semibold text-[#1f3445]">{{ $activeCategory?->name ?? 'Tutte le discussioni' }}</h3>
                    </div>
                    <div class="text-sm text-[#587287]">{{ $threads->total() }} topic</div>
                </div>

                <div class="hidden grid-cols-[minmax(0,1fr)_96px_96px_250px] gap-4 px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] md:grid" style="background: linear-gradient(180deg, #7ea1b8 0%, #607b8f 100%); color: #ffffff;">
                    <div>Topics</div>
                    <div class="text-center">Replies</div>
                    <div class="text-center">Views</div>
                    <div>Last post</div>
                </div>

                @forelse ($threads as $thread)
                    @php
                        $replyCount = max(0, $thread->posts_count - 1);
                        $latestPost = $thread->latestPost;
                    @endphp
                    <a href="{{ route('forum.show', $thread) }}" class="grid gap-4 border-b border-[#d8e8f2] px-5 py-4 transition md:grid-cols-[minmax(0,1fr)_96px_96px_250px] {{ $loop->odd ? 'bg-[#f8fbfe]' : 'bg-[#eef5fb]' }} hover:bg-[#e7f2fa]">
                        <div class="min-w-0">
                            <div class="flex gap-4">
                                <div class="mt-1 flex h-11 w-11 items-center justify-center rounded-full border border-[#a9c7dd] bg-white text-sm font-semibold text-[#0f76b0]">{{ $thread->is_pinned ? 'A' : 'T' }}</div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($thread->is_pinned)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-amber-800">Announcement</span>
                                        @endif
                                        @if ($thread->is_locked)
                                            <span class="rounded-full bg-stone-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-stone-700">Locked</span>
                                        @endif
                                        <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#4b6880]">{{ $thread->category?->name ?? 'Community' }}</span>
                                    </div>
                                    <div class="mt-1 text-[1.05rem] font-semibold text-[#23425a] underline-offset-4 hover:underline">{{ $thread->title }}</div>
                                    <div class="mt-1 text-sm text-[#567185]">by {{ $thread->user->name }} · {{ $thread->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="mt-1 line-clamp-2 text-sm leading-6 text-[#607b8f]">{{ $thread->excerpt }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-start text-left text-[#23425a] md:justify-center md:text-center">
                            <div>
                                <div class="text-xl font-semibold">{{ $replyCount }}</div>
                                <div class="text-[11px] uppercase tracking-[0.14em] text-[#678298]">reply</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-start text-left text-[#23425a] md:justify-center md:text-center">
                            <div>
                                <div class="text-xl font-semibold">{{ $thread->posts_count }}</div>
                                <div class="text-[11px] uppercase tracking-[0.14em] text-[#678298]">view</div>
                            </div>
                        </div>
                        <div class="flex items-center text-sm text-[#4f6b80]">
                            @if ($latestPost)
                                <div>
                                    <div class="font-semibold text-[#23425a]">{{ $latestPost->user?->name }}</div>
                                    <div>{{ $latestPost->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                            @else
                                <div>No posts</div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-[#6a8498]">
                        Nessuna discussione trovata con questi filtri.
                    </div>
                @endforelse
            </section>

            <div class="km-panel p-4">
                {{ $threads->links() }}
            </div>
        </div>

        <div id="forum-thread-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-950/45 p-4">
            <div class="w-full max-w-2xl overflow-hidden rounded-[1rem] border border-[#9fc2da] bg-white shadow-[0_30px_80px_rgba(17,24,39,0.22)]">
                <div class="px-5 py-4" style="background: linear-gradient(180deg, #1a91cd 0%, #0f76b0 100%); color: #ffffff;">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-semibold">Nuovo topic</h2>
                        <button type="button" data-close-forum-modal class="rounded-full border px-4 py-2 text-sm font-semibold text-white" style="background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.30);">Chiudi</button>
                    </div>
                </div>
                <form method="POST" action="{{ route('forum.store') }}" class="space-y-4 px-5 py-5">
                    @csrf
                    <select name="forum_category_id" class="h-12 w-full rounded-[1rem] border border-[#cad8e4] bg-white px-4 text-[15px] text-[#294256] outline-none" required>
                        <option value="">Categoria</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('forum_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="title" value="{{ old('title') }}" class="h-12 w-full rounded-[1rem] border border-[#cad8e4] bg-white px-4 text-[15px] text-[#294256] outline-none" placeholder="Titolo discussione" required>
                    <textarea name="content" rows="5" class="w-full rounded-[1rem] border border-[#cad8e4] bg-white px-4 py-3 text-[15px] text-[#294256] outline-none" placeholder="Scrivi il primo messaggio" required>{{ old('content') }}</textarea>
                    <div class="flex justify-end gap-3">
                        <button type="button" data-close-forum-modal class="inline-flex h-11 items-center justify-center rounded-full border px-5 text-sm font-semibold text-[#35526b]" style="background: #ffffff; border-color: #c8d7e2;">Annulla</button>
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full px-5 text-sm font-semibold text-white" style="background: #4f7d4a;">Pubblica thread</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="forum-category-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-950/45 p-4">
            <div class="w-full max-w-2xl overflow-hidden rounded-[1rem] border border-[#9fc2da] bg-white shadow-[0_30px_80px_rgba(17,24,39,0.22)]">
                <div class="px-5 py-4" style="background: linear-gradient(180deg, #1a91cd 0%, #0f76b0 100%); color: #ffffff;">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold">Proponi categoria</h2>
                            <span class="rounded-full border px-3 py-1 text-xs font-medium" style="background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.30);">{{ $proposalCount }}</span>
                        </div>
                        <button type="button" data-close-forum-modal class="rounded-full border px-4 py-2 text-sm font-semibold text-white" style="background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.30);">Chiudi</button>
                    </div>
                </div>
                <form method="POST" action="{{ route('forum.category-proposals.store') }}" class="space-y-4 px-5 py-5">
                    @csrf
                    <input type="text" name="name" class="h-12 w-full rounded-[1rem] border border-[#cad8e4] bg-white px-4 text-[15px] text-[#294256] outline-none" placeholder="Nome categoria proposta" required>
                    <textarea name="description" rows="4" class="w-full rounded-[1rem] border border-[#cad8e4] bg-white px-4 py-3 text-[15px] text-[#294256] outline-none" placeholder="Perche serve e quali discussioni dovrebbe ospitare"></textarea>
                    <div class="flex justify-end gap-3">
                        <button type="button" data-close-forum-modal class="inline-flex h-11 items-center justify-center rounded-full border px-5 text-sm font-semibold text-[#35526b]" style="background: #ffffff; border-color: #c8d7e2;">Annulla</button>
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full px-5 text-sm font-semibold text-white" style="background: #4f7d4a;">Invia proposta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const bindModal = (openId, modalId) => {
                const openButton = document.getElementById(openId);
                const modal = document.getElementById(modalId);

                if (!openButton || !modal) {
                    return;
                }

                const close = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                };

                openButton.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });

                modal.querySelectorAll('[data-close-forum-modal]').forEach((button) => {
                    button.addEventListener('click', close);
                });

                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        close();
                    }
                });
            };

            bindModal('open-forum-thread-modal', 'forum-thread-modal');
            bindModal('open-forum-category-modal', 'forum-category-modal');

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                document.querySelectorAll('#forum-thread-modal, #forum-category-modal').forEach((modal) => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            });
        })();
    </script>
</x-app-layout>
