<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MemberOnepageController;
use App\Http\Controllers\OneToOneController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Cambio lingua (disponibile anche senza auth) ──────────────────────────────
Route::get('/lingua/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', function () {
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

// ── Dashboard e onboarding: accessibili senza onboarding completato ─────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/onboarding/step', [OnboardingController::class, 'saveStep'])->name('onboarding.step');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
});

// ── Profilo: accessibile anche senza onboarding completato ───────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/suggestions', [ProfileController::class, 'storeSuggestion'])->name('profile.suggestions.store');
    Route::delete('/profile/gallery/{memberGalleryImage}', [ProfileController::class, 'destroyGalleryImage'])->name('profile.gallery.destroy');
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
    // (l'utente potrebbe voler iscriversi prima di compilare il profilo)
    Route::get('/abbonamento', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/abbonamento', [SubscriptionController::class, 'request'])->name('subscriptions.request');
    Route::delete('/abbonamento/{subscription}', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
});

// ── Sezioni riservate: richiede onboarding completato ────────────────────────
Route::middleware(['auth', 'verified', 'onboarding'])->group(function () {
    Route::get('/directory', DirectoryController::class)->name('directory.index');
    Route::get('/member/{slug}', [MemberOnepageController::class, 'show'])->name('members.show');

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

    Route::get('/referenze', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('/referenze', [ReferralController::class, 'store'])->middleware('throttle:10,1')->name('referrals.store');
    Route::patch('/referenze/{referral}/status', [ReferralController::class, 'updateStatus'])->name('referrals.status');
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
    });

require __DIR__.'/auth.php';
