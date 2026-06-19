<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\BannerClickController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MemberOnepageController;
use App\Http\Controllers\OneToOneController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileVideoAccessController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\BannerReportController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ChapterInviteController;
use App\Http\Controllers\MyInvitesController;
use App\Http\Controllers\PlanetChatController;
use App\Http\Controllers\PlanetContextController;
use Illuminate\Support\Facades\Route;

// ── Cambio lingua (disponibile anche senza auth) ──────────────────────────────
Route::get('/lingua/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// ── Inviti Pianeta (pubblici: funzionano anche senza login) ───────────────────
Route::get('/invita/{token}', [ChapterInviteController::class, 'show'])->name('chapter.invite');
Route::post('/invita/{token}/accetta', [ChapterInviteController::class, 'accept'])
    ->middleware('auth')
    ->name('chapter.invite.accept');

Route::get('/', function () {
    // Utenti già autenticati → dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    // Passa capitoli e pagine CMS alla homepage
    $chapters  = \App\Models\Chapter::with('city')
        ->where('is_active', true)
        ->orderBy('name')
        ->get();
    $navPages  = \App\Models\Page::forNav();
    $footerPages = \App\Models\Page::forFooter();
    return view('welcome', compact('chapters', 'navPages', 'footerPages'));
})->name('home');

// ── Newsletter iscrizione ─────────────────────────────────────────────────────
Route::post('/newsletter', [PageController::class, 'newsletter'])->name('newsletter.subscribe');

// ── Pagine CMS pubbliche ──────────────────────────────────────────────────────
Route::get('/pagina/{slug}', [PageController::class, 'show'])->name('page.show');

Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::get('/banner/{bannerCampaign}/click', BannerClickController::class)
    ->middleware('auth')
    ->name('banners.click');

// ── Pagine membro pubbliche: visibili anche da link condiviso senza login ────
Route::get('/member/{slug}', [MemberOnepageController::class, 'show'])->name('members.show');
Route::get('/member/{slug}/referenze', [MemberOnepageController::class, 'referrals'])->name('members.referrals');
Route::get('/member/{slug}/recensioni', [MemberOnepageController::class, 'reviews'])->name('members.reviews');

// ── Biglietto da visita digitale (pubblico, nessun layout app) ───────────────
Route::get('/card/{slug}', [CardController::class, 'show'])->name('card.show');
Route::get('/card/{slug}/vcard', [CardController::class, 'vcard'])->name('card.vcard');

// ── Dashboard e onboarding: accessibili senza onboarding completato ─────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/onboarding/step', [OnboardingController::class, 'saveStep'])->name('onboarding.step');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::view('/faq', 'faq')->name('faq');
});

// ── Profilo: accessibile anche senza onboarding completato ───────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/suggestions', [ProfileController::class, 'storeSuggestion'])->name('profile.suggestions.store');
    Route::delete('/profile/gallery/{memberGalleryImage}', [ProfileController::class, 'destroyGalleryImage'])->name('profile.gallery.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile/banner', [ProfileController::class, 'destroyBanner'])->name('profile.banner.destroy');
    Route::delete('/profile/video', [ProfileController::class, 'destroyVideo'])->name('profile.video.destroy');
    Route::patch('/profile/video-visibility', [ProfileController::class, 'updateVideoVisibility'])->name('profile.video-visibility.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Notifiche e push: auth+verified ma NON richiedono onboarding completato ──
// (campanella sempre funzionante anche durante l'onboarding)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Push subscriptions (PWA): registrazione service worker appena dopo login
    Route::get('/push/vapid-public-key', [PushSubscriptionController::class, 'vapidPublicKey'])->name('push.vapid');
    Route::post('/push/subscribe',   [PushSubscriptionController::class, 'subscribe'])->middleware('throttle:30,1')->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->middleware('throttle:30,1')->name('push.unsubscribe');
    Route::post('/push/test',        [PushSubscriptionController::class, 'test'])->middleware('throttle:5,1')->name('push.test');

    // Abbonamenti: accessibili prima del completamento onboarding
    Route::get('/abbonamento', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/abbonamento', [SubscriptionController::class, 'request'])->name('subscriptions.request');
    Route::delete('/abbonamento/{subscription}', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // ── Chat di gruppo Pianeta (no onboarding required: accesso gestito dal controller) ──
    Route::get('/pianeta/chat', [PlanetChatController::class, 'redirect'])->name('planet.chat.redirect');
    Route::get('/pianeta/{chapter}/chat', [PlanetChatController::class, 'show'])->name('planet.chat.show');
    Route::post('/pianeta/{chapter}/chat', [PlanetChatController::class, 'store'])->name('planet.chat.store');
    Route::get('/pianeta/{chapter}/chat/poll', [PlanetChatController::class, 'poll'])->name('planet.chat.poll');
});

// ── Sezioni riservate: richiede onboarding completato ────────────────────────
Route::middleware(['auth', 'verified', 'onboarding'])->group(function () {
    // ── Switch Pianeta attivo ─────────────────────────────────────────────
    Route::post('/pianeta/{chapter}/switch', PlanetContextController::class . '@switch')
        ->name('planet.switch');

    Route::get('/directory', DirectoryController::class)->name('directory.index');
    Route::post('/members/{user}/video-access', [ProfileVideoAccessController::class, 'store'])->name('profile-video-access.store');
    Route::patch('/video-access/{profileVideoAccessRequest}', [ProfileVideoAccessController::class, 'respond'])->name('profile-video-access.respond');
    Route::delete('/video-access/{profileVideoAccessRequest}', [ProfileVideoAccessController::class, 'revoke'])->name('profile-video-access.revoke');

    Route::get('/members/search', [OneToOneController::class, 'searchMembers'])->name('members.search');
    Route::get('/members/{user}/slots', [OneToOneController::class, 'memberSlots'])->name('members.slots');

    Route::get('/one-to-one', [OneToOneController::class, 'index'])->name('one-to-ones.index');
    Route::post('/one-to-one', [OneToOneController::class, 'store'])->middleware('throttle:10,1')->name('one-to-ones.store');
    Route::patch('/one-to-one/{oneToOneRequest}/status', [OneToOneController::class, 'updateStatus'])->name('one-to-ones.status');
    Route::post('/one-to-one/availability', [OneToOneController::class, 'storeAvailability'])->name('one-to-ones.availability.store');
    Route::delete('/one-to-one/availability/{availabilitySlot}', [OneToOneController::class, 'destroyAvailability'])->name('one-to-ones.availability.destroy');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->middleware('throttle:10,1')->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{event}/register', [EventController::class, 'register'])->name('events.register');
    Route::delete('/events/{event}/register', [EventController::class, 'unregister'])->name('events.unregister');
    Route::post('/events/{event}/invite', [EventController::class, 'invite'])->name('events.invite');
    Route::patch('/events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::post('/forum', [ForumController::class, 'store'])->middleware('throttle:10,1')->name('forum.store');
    Route::post('/forum/category-proposals', [ForumController::class, 'storeCategoryProposal'])->name('forum.category-proposals.store');
    Route::get('/forum/{thread}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/forum/{thread}/reply', [ForumController::class, 'reply'])->middleware('throttle:20,1')->name('forum.reply');

    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::post('/conversations', [ConversationController::class, 'start'])->middleware('throttle:20,1')->name('conversations.start');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage'])->middleware('throttle:30,1')->name('conversations.messages.store');

    Route::get('/i-miei-inviti', [MyInvitesController::class, 'index'])->name('my.invites');
    Route::post('/i-miei-inviti/invita', [MyInvitesController::class, 'invite'])->middleware('throttle:10,1')->name('my.invites.invite');
    Route::delete('/i-miei-inviti/invita/{invitation}', [MyInvitesController::class, 'revoke'])->name('my.invites.revoke');

    Route::get('/referenze', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('/referenze', [ReferralController::class, 'store'])->middleware('throttle:10,1')->name('referrals.store');
    Route::patch('/referenze/{referral}/status', [ReferralController::class, 'updateStatus'])->name('referrals.status');
    Route::patch('/referenze/{referral}/acknowledge', [ReferralController::class, 'acknowledge'])->name('referrals.acknowledge');
    Route::patch('/referenze/{referral}/dichiara-valore', [ReferralController::class, 'declareValue'])->name('referrals.declare');
    Route::patch('/referenze/{referral}/valida-valore', [ReferralController::class, 'validateValue'])->name('referrals.validate');
    Route::patch('/referenze/{referral}/toggle-public', [ReferralController::class, 'togglePublic'])->name('referrals.toggle-public');
    Route::delete('/referenze/{referral}', [ReferralController::class, 'destroy'])->name('referrals.destroy');
});

// ── Admin: gestione cache (riservata ad admin / leader-capitolo) ──────────────
Route::middleware([
        'auth',
        'verified',
        'role:super-admin|admin-community|leader-capitolo',
    ])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/cache', [CacheController::class, 'index'])->name('cache.index');
        Route::post('/cache/clear', [CacheController::class, 'clear'])->name('cache.clear');
        Route::get('/banner-campaigns/{bannerCampaign}/export', [BannerReportController::class, 'export'])
            ->name('banner-campaigns.export');
    });

// ── Admin: impersona utente (start: solo super-admin | stop: qualsiasi auth) ──
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('/impersonate/{user}', [ImpersonateController::class, 'start'])
            ->name('impersonate.start')
            ->middleware('role:super-admin');
        Route::post('/impersonate/stop', [ImpersonateController::class, 'stop'])
            ->name('impersonate.stop');
    });

require __DIR__.'/auth.php';
