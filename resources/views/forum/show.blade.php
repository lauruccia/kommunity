<x-app-layout>
    <x-slot name="header">
        <div class="km-panel overflow-hidden p-0">
            <div class="px-6 py-5" style="background: linear-gradient(180deg, rgba(232,239,247,0.96) 0%, rgba(214,226,238,0.96) 100%); color: #1f3445;">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.22em]" style="color: #6a8599;">Board index / {{ $thread->category?->name ?? 'Community' }}</div>
                        <h1 class="mt-2 text-3xl font-semibold">{{ $thread->title }}</h1>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm" style="color: #5f778b;">
                            <span>Topic aperto da {{ $thread->user->name }}</span>
                            <span>·</span>
                            <span>{{ $thread->created_at->format('d/m/Y H:i') }}</span>
                            @if ($thread->is_pinned)
                                <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background: #fef3c7; border-color: #f3d388; color: #9a6700;">Announcement</span>
                            @endif
                            @if ($thread->is_locked)
                                <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background: #e7eaee; border-color: #cfd7df; color: #51616f;">Locked</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('forum.index', ['category' => $thread->forum_category_id]) }}" class="inline-flex h-11 items-center justify-center rounded-full border px-5 text-sm font-semibold text-[#35526b]" style="background: #ffffff; border-color: #c8d7e2;">Torna al forum</a>
                        @unless($thread->is_locked)
                            <a href="#reply-box" class="inline-flex h-11 items-center justify-center rounded-full px-5 text-sm font-semibold text-white" style="background: #4f7d4a;">Rispondi</a>
                        @endunless
                    </div>
                </div>
            </div>
            <div class="bg-[#e7f0f7] px-5 py-3 text-sm font-semibold text-[#35526b]">
                <a href="{{ route('forum.index') }}" class="hover:text-[#0f76b0]">Board index</a>
                <span class="mx-2 text-[#7a93a5]">/</span>
                <a href="{{ route('forum.index', ['category' => $thread->forum_category_id]) }}" class="hover:text-[#0f76b0]">{{ $thread->category?->name ?? 'Community' }}</a>
                <span class="mx-2 text-[#7a93a5]">/</span>
                <span>{{ $thread->title }}</span>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="km-shell space-y-5">
            @if (session('status') === 'thread-replied')
                <div class="rounded-[0.9rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Risposta pubblicata correttamente.
                </div>
            @endif

            <div class="flex items-center justify-between rounded-[1rem] border border-[#cadce9] bg-[#eef5fb] px-5 py-3 text-sm text-[#4f6a7d]">
                <div>{{ $thread->posts->count() }} messaggi nella discussione</div>
                <div>{{ now()->format('d/m/Y H:i') }}</div>
            </div>

            @foreach ($thread->posts as $index => $post)
                <article id="post-{{ $post->id }}" class="overflow-hidden rounded-[1rem] border border-[#9fc2da] bg-white shadow-[0_12px_28px_rgba(60,79,94,0.10)]">
                    <div class="grid lg:grid-cols-[230px_minmax(0,1fr)]">
                        <aside class="border-b border-[#d9e8f2] bg-[#eef5fb] px-5 py-5 lg:border-b-0 lg:border-r">
                            <div class="text-lg font-semibold text-[#224159]">{{ $post->user->name }}</div>
                            <div class="mt-2 text-sm text-[#597487]">{{ $post->user->memberProfile?->company_name ?: 'Membro community' }}</div>
                            @if ($post->user->memberProfile?->city?->name)
                                <div class="mt-1 text-xs uppercase tracking-[0.14em] text-[#728ba0]">{{ $post->user->memberProfile->city->name }}</div>
                            @endif
                            <div class="mt-5 space-y-2 text-xs text-[#6c869a]">
                                <div>Messaggio #{{ $index + 1 }}</div>
                                <div>{{ $post->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </aside>

                        <div class="min-w-0">
                            <div class="flex items-center justify-between gap-4 px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.16em]" style="background: linear-gradient(180deg, #1794d3 0%, #0f76b0 100%); color: #ffffff;">
                                <div>{{ $thread->title }}</div>
                                <div class="flex items-center gap-2">
                                    <a href="#post-{{ $post->id }}" class="rounded-full border px-3 py-1 text-white" style="background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.30);">Link</a>
                                    @unless($thread->is_locked)
                                        <button
                                            type="button"
                                            class="rounded-full border px-3 py-1 text-white"
                                            style="background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.30);"
                                            data-quote-post
                                            data-post-id="{{ $post->id }}"
                                            data-post-author="{{ $post->user->name }}"
                                            data-post-content="{{ trim($post->content) }}"
                                        >
                                            Quota
                                        </button>
                                    @endunless
                                </div>
                            </div>

                            <div class="bg-[#f9fcfe] px-5 py-5">
                                <div class="prose prose-stone max-w-none text-sm leading-7 text-[#294256]">
                                    {!! nl2br(e($post->content)) !!}
                                </div>

                                @foreach ($post->replies as $reply)
                                    <div class="mt-5 rounded-[0.95rem] border border-[#cfe0ec] bg-white px-4 py-4">
                                        <div class="flex items-center justify-between gap-3 text-xs uppercase tracking-[0.14em] text-[#6f879a]">
                                            <span>Replica di {{ $reply->user->name }}</span>
                                            <span>{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="mt-3 text-sm leading-7 text-[#35526b]">{!! nl2br(e($reply->content)) !!}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach

            <div id="reply-box" class="km-panel overflow-hidden p-0">
                <div class="px-5 py-3 text-white" style="background: linear-gradient(180deg, #7ea1b8 0%, #607b8f 100%);">
                    <h2 class="text-xl font-semibold">Post reply</h2>
                </div>
                <form method="POST" action="{{ route('forum.reply', $thread) }}" class="space-y-4 bg-[#f7fbff] px-5 py-5">
                    @csrf
                    <input type="hidden" name="parent_id" id="forum-parent-id" value="">
                    <div id="forum-reply-context" class="hidden rounded-[0.9rem] border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                        <div class="flex items-center justify-between gap-3">
                            <span id="forum-reply-context-text"></span>
                            <button type="button" id="forum-clear-reply-context" class="font-semibold text-sky-900">Annulla risposta</button>
                        </div>
                    </div>
                    @if ($thread->is_locked)
                        <div class="rounded-[0.9rem] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Questa discussione e chiusa. Non puoi aggiungere altre risposte.
                        </div>
                    @else
                        <textarea name="content" id="forum-reply-textarea" rows="6" class="w-full rounded-[1rem] border border-[#cad8e4] bg-white px-4 py-3 text-[15px] text-[#294256] outline-none" placeholder="Scrivi la tua risposta o quota un messaggio sopra" required>{{ old('content') }}</textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full px-5 text-sm font-semibold text-white" style="background: #4f7d4a;">Pubblica risposta</button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    @unless($thread->is_locked)
        <script>
            (() => {
                const parentInput = document.getElementById('forum-parent-id');
                const replyBox = document.getElementById('reply-box');
                const replyContext = document.getElementById('forum-reply-context');
                const replyContextText = document.getElementById('forum-reply-context-text');
                const clearReplyContext = document.getElementById('forum-clear-reply-context');
                const textarea = document.getElementById('forum-reply-textarea');

                if (!parentInput || !replyBox || !replyContext || !replyContextText || !clearReplyContext || !textarea) {
                    return;
                }

                const resetReplyContext = () => {
                    parentInput.value = '';
                    replyContext.classList.add('hidden');
                    replyContextText.textContent = '';
                };

                document.querySelectorAll('[data-quote-post]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const author = button.dataset.postAuthor || 'Membro';
                        const content = (button.dataset.postContent || '').trim();
                        const excerpt = content.length > 220 ? `${content.slice(0, 220)}...` : content;
                        parentInput.value = button.dataset.postId || '';
                        replyContext.classList.remove('hidden');
                        replyContextText.textContent = `Stai rispondendo a ${author}: "${excerpt}"`;

                        const quoteBlock = `> ${author} ha scritto:\n> ${content.split('\n').join('\n> ')}\n\n`;

                        if (!textarea.value.includes(quoteBlock)) {
                            textarea.value = `${quoteBlock}${textarea.value}`.trimStart();
                        }

                        replyBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        textarea.focus();
                    });
                });

                clearReplyContext.addEventListener('click', resetReplyContext);
            })();
        </script>
    @endunless
</x-app-layout>
