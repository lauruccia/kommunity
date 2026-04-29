<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumCategoryProposal;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'exists:forum_categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $threadsQuery = ForumThread::query()
            ->with([
                'category',
                'user.memberProfile',
                'latestPost.user.memberProfile',
            ])
            ->withCount('posts')
            ->orderByDesc('is_pinned')
            ->latest();

        $categories = ForumCategory::query()
            ->withCount('threads')
            ->with([
                'threads' => fn ($query) => $query
                    ->with(['latestPost.user.memberProfile'])
                    ->withCount('posts')
                    ->latest()
                    ->limit(1),
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $postCountsByCategory = ForumPost::query()
            ->join('forum_threads', 'forum_threads.id', '=', 'forum_posts.forum_thread_id')
            ->selectRaw('forum_threads.forum_category_id as category_id, count(forum_posts.id) as posts_count')
            ->groupBy('forum_threads.forum_category_id')
            ->pluck('posts_count', 'category_id');

        $categories->each(function (ForumCategory $category) use ($postCountsByCategory): void {
            $category->setAttribute('posts_count', (int) ($postCountsByCategory[$category->id] ?? 0));
        });

        $stats = [
            'threads' => ForumThread::query()->count(),
            'posts' => ForumPost::query()->count(),
            'members' => User::query()->count(),
            'active_members' => ForumPost::query()->distinct('user_id')->count('user_id'),
        ];

        $featuredThreads = ForumThread::query()
            ->with(['category', 'user.memberProfile', 'latestPost.user.memberProfile'])
            ->withCount('posts')
            ->orderByDesc('is_pinned')
            ->latest()
            ->limit(3)
            ->get();

        if (! empty($filters['category'])) {
            $threadsQuery->where('forum_category_id', $filters['category']);
        }

        if (! empty($filters['search'])) {
            $threadsQuery->where(function ($query) use ($filters): void {
                $query
                    ->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('excerpt', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('posts', fn ($postQuery) => $postQuery->where('content', 'like', '%'.$filters['search'].'%'));
            });
        }

        return view('forum.index', [
            'categories' => $categories,
            'threads' => $threadsQuery
                ->paginate(12)
                ->withQueryString(),
            'filters' => $filters,
            'proposalCount' => ForumCategoryProposal::query()
                ->where('user_id', $request->user()->id)
                ->count(),
            'stats' => $stats,
            'featuredThreads' => $featuredThreads,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'forum_category_id' => ['required', 'exists:forum_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $thread = ForumThread::query()->create([
            'forum_category_id' => $data['forum_category_id'],
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(6)),
            'excerpt' => Str::limit(strip_tags($data['content']), 150),
        ]);

        ForumPost::query()->create([
            'forum_thread_id' => $thread->id,
            'user_id' => $request->user()->id,
            'content' => $data['content'],
        ]);

        return redirect()->route('forum.show', $thread)->with('status', 'thread-created');
    }

    public function storeCategoryProposal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        ForumCategoryProposal::query()->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('status', 'forum-category-proposed');
    }

    public function show(ForumThread $thread): View
    {
        $thread->load([
            'category',
            'user',
            'posts' => fn ($query) => $query
                ->whereNull('parent_id')
                ->with(['user.memberProfile.city', 'replies.user.memberProfile.city', 'parent.user'])
                ->oldest(),
        ]);

        return view('forum.show', ['thread' => $thread]);
    }

    public function reply(Request $request, ForumThread $thread): RedirectResponse
    {
        abort_if($thread->is_locked, 403);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('forum_posts', 'id')],
        ]);

        $parent = null;

        if (! empty($data['parent_id'])) {
            $parent = ForumPost::query()
                ->where('forum_thread_id', $thread->id)
                ->findOrFail($data['parent_id']);
        }

        ForumPost::query()->create([
            'forum_thread_id' => $thread->id,
            'user_id' => $request->user()->id,
            'parent_id' => $parent?->id,
            'content' => $data['content'],
        ]);

        return back()->with('status', 'thread-replied');
    }
}
