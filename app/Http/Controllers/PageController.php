<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function show(string $slug): Response|\Illuminate\Contracts\View\View
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('page', compact('page'));
    }

    /** Iscrizione newsletter: salva email in SiteSetting come lista separata da virgola */
    public function newsletter(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['email' => 'required|email|max:255']);

        $email   = strtolower(trim($request->input('email')));
        $current = \App\Models\SiteSetting::get('newsletter_emails', '');
        $list    = array_filter(array_map('trim', explode(',', $current)));

        if (!in_array($email, $list, true)) {
            $list[] = $email;
            \App\Models\SiteSetting::set('newsletter_emails', implode(',', $list));
        }

        return back()->with('newsletter_success', true);
    }
}
