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
    public function create(): View
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
        }

        return view('auth.register', [
            'referralCode'      => $inviter?->referral_code,
            'invitedByName'     => $inviter?->name ?? ($chapterInvitation?->invitedBy?->name),
            'invitationPlanet'  => $chapterInvitation?->chapter?->name ?? $invitationPlanet,
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
        $chapterToken = $request->session()->get('chapter_invitation_token');

        if (! $chapterToken && $inviter) {
            $inviterPlanetId = $inviter->activePlanetId()
                ?? $inviter->planets()->value('chapters.id');

            if ($inviterPlanetId) {
                MemberProfile::$adminOverrideLimit = true;
                try {
                    $user->memberProfile?->update(['active_chapter_id' => $inviterPlanetId]);
                } finally {
                    MemberProfile::$adminOverrideLimit = false;
                }

                DB::table('chapter_members')->updateOrInsert(
                    ['chapter_id' => $inviterPlanetId, 'user_id' => $user->id],
                    ['status' => 'active', 'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        event(new Registered($user));

        Auth::login($user);

        $request->session()->forget('registration_referral_code');

        if ($chapterToken) {
            $invitation = ChapterInvitation::with(['chapter', 'invitedBy'])
                ->where('token', $chapterToken)
                ->where('status', 'pending')
                ->first();

            if ($invitation && $invitation->isValid()) {
                try {
                    $invitation->accept($user);

                    // Notifica email al leader/invitante
                    if ($invitation->invitedBy && $invitation->invitedBy->email) {
                        Mail::to($invitation->invitedBy->email)
                            ->send(new InvitationAcceptedMail($invitation, $user));
                    }
                } catch (\Throwable) {
                    // Non bloccare la registrazione se qualcosa va storto
                }
            }

            $request->session()->forget('chapter_invitation_token');
        }

        return redirect(route('dashboard', absolute: false));
    }
}
