<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\InvitationAcceptedMail;
use App\Models\ChapterInvitation;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View|RedirectResponse
    {
        $referralCode = request()->query('ref');

        if (is_string($referralCode) && $referralCode !== '') {
            session(['registration_referral_code' => $referralCode]);
        } else {
            session()->forget('registration_referral_code');
            $referralCode = null;
        }

        $inviter = User::query()->where('referral_code', $referralCode)->first();

        // Recupera il contesto invito-pianeta se presente in sessione
        $invitationPlanet = session('invitation_planet');
        $invitationBy     = session('invitation_by');

        // Se c'è un token pianeta in sessione e nessun nome invitante, usa quello del pianeta
        $chapterInvitation = null;
        if (session()->has('chapter_invitation_token')) {
            $chapterInvitation = ChapterInvitation::with(['chapter', 'invitedBy'])
                ->where('token', session('chapter_invitation_token'))
                ->where('status', 'pending')
                ->first();

            if ($chapterInvitation && ! $chapterInvitation->isValid()) {
                $chapterInvitation = null;
            }
        }

        if (! $chapterInvitation && ! $inviter) {
            return Redirect::route('login')
                ->with('error', 'La registrazione e disponibile solo tramite invito.');
        }

        // Il parametro ?planet=slug è supportato per gli utenti multi-pianeta,
        // ma viene accettato SOLO se l'invitante (ref) appartiene effettivamente a quel pianeta.
        // In questo modo chi riceve un link non può cambiare il nome del pianeta per iscriversi
        // a un pianeta dove l'invitante non è mai stato membro.
        $planetSlug = request()->query('planet');
        if (is_string($planetSlug) && $planetSlug !== '' && $inviter) {
            $planetBelongsToInviter = \DB::table('chapter_members')
                ->join('chapters', 'chapters.id', '=', 'chapter_members.chapter_id')
                ->where('chapters.slug', $planetSlug)
                ->where('chapter_members.user_id', $inviter->id)
                ->where('chapter_members.status', 'active')
                ->exists();

            if ($planetBelongsToInviter) {
                session(['referral_planet_slug' => $planetSlug]);
            } else {
                // L'invitante non appartiene al pianeta indicato: ignora il parametro
                session()->forget('referral_planet_slug');
            }
        } elseif (! is_string($planetSlug) || $planetSlug === '') {
            session()->forget('referral_planet_slug');
        }

        $referralPlanetName = null;
        $referralPlanetSlug = session('referral_planet_slug');
        if ($referralPlanetSlug) {
            $referralPlanetName = \App\Models\Chapter::query()
                ->where('slug', $referralPlanetSlug)
                ->value('name');
        } elseif ($inviter) {
            // Fallback: mostra il pianeta attivo dell'invitante
            $inviterPlanetId = $inviter->activePlanetId()
                ?? $inviter->planets()->value('chapters.id');
            if ($inviterPlanetId) {
                $referralPlanetName = \App\Models\Chapter::query()
                    ->where('id', $inviterPlanetId)
                    ->value('name');
            }
        }

        return view('auth.register', [
            'referralCode'      => $inviter?->referral_code,
            'invitedByName'     => $inviter?->name ?? ($chapterInvitation?->invitedBy?->name),
            'invitationPlanet'  => $chapterInvitation?->chapter?->name ?? $invitationPlanet ?? $referralPlanetName,
            'chapterInvitation' => $chapterInvitation,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $chapterToken = $request->session()->get('chapter_invitation_token');
        $validChapterInvitation = null;

        if ($chapterToken) {
            $validChapterInvitation = ChapterInvitation::query()
                ->where('token', $chapterToken)
                ->where('status', 'pending')
                ->first();

            if ($validChapterInvitation && ! $validChapterInvitation->isValid()) {
                $validChapterInvitation = null;
            }
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+().\s-]{6,30}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invited_by_name' => ['required', 'string', 'max:255', 'regex:/^\S+\s+\S+.*$/'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ], [
            'phone.required' => 'Il numero di telefono e obbligatorio.',
            'phone.regex' => 'Inserisci un numero di telefono valido.',
            'invited_by_name.required' => 'Il campo Invitato da e obbligatorio.',
            'invited_by_name.regex' => 'Inserisci nome e cognome della persona che ti ha invitato.',
        ]);

        $inviter = null;

        if ($request->filled('referral_code')) {
            $inviter = User::query()
                ->where('referral_code', $request->string('referral_code')->toString())
                ->first();
        }

        if (! $validChapterInvitation && ! $inviter) {
            throw ValidationException::withMessages([
                'invited_by_name' => 'Per registrarti devi usare un link di invito valido.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'invited_by_user_id' => $inviter?->id,
            'invited_by_name' => $inviter?->name ?? $request->string('invited_by_name')->toString(),
        ]);

        $user->assignRole(Role::findOrCreate('membro'));
        $user->memberProfile()->update([
            'phone' => $request->string('phone')->toString(),
        ]);

        // ── Gestione invito pianeta ───────────────────────────────────────────
        if (! $chapterToken && $inviter) {
            $inviterPlanetId = null;

            // Usa il pianeta dalla sessione SOLO se l'invitante ne è membro attivo.
            // Questo impedisce di modificare ?planet= nel link per iscriversi a un
            // pianeta arbitrario: il controllo avviene sia in create() che qui in store().
            $referralPlanetSlug = $request->session()->get('referral_planet_slug');
            if ($referralPlanetSlug) {
                $inviterPlanetId = DB::table('chapter_members')
                    ->join('chapters', 'chapters.id', '=', 'chapter_members.chapter_id')
                    ->where('chapters.slug', $referralPlanetSlug)
                    ->where('chapter_members.user_id', $inviter->id)
                    ->where('chapter_members.status', 'active')
                    ->value('chapter_members.chapter_id');
            }

            // Fallback: pianeta attivo dell'invitante
            if (! $inviterPlanetId) {
                $inviterPlanetId = $inviter->activePlanetId()
                    ?? $inviter->planets()->value('chapters.id');
            }

            if ($inviterPl