<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MemberOnepageController;
use App\Http\Controllers\OneToOneController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Cambio lingua (disponibile anche senza auth) ──────────────────────────────
Route::get('/lingua/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/directory', DirectoryController::class)->name('directory.index');
    Route::get('/member/{slug}', [MemberOnepageController::class, 'show'])->name('members.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/gallery/{memberGalleryImage}', [ProfileController::class, 'destroyGalleryImage'])->name('profile.gallery.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/one-to-one', [OneToOneController::class, 'index'])->name('one-to-ones.index');
    Route::post('/one-to-one', [OneToOneController::class, 'store'])->name('one-to-ones.store');
    Route::patch('/one-to-one/{oneToOneRequest}/status', [OneToOneController::class, 'updateStatus'])->name('one-to-ones.status');
    Route::post('/one-to-one/availability', [OneToOneController::class, 'storeAvailability'])->name('one-to-ones.availability.store');
    Route::delete('/one-to-one/availability/{availabilitySlot}', [OneToOneController::class, 'destroyAvailability'])->name('one-to-ones.availability.destroy');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{event}/register', [EventController::class, 'register'])->name('events.register');
    Route::delete('/events/{event}/register', [EventController::class, 'unregister'])->name('events.unregister');

    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');
    Route::post('/forum/category-proposals', [ForumController::class, 'storeCategoryProposal'])->name('forum.category-proposals.store');
    Route::get('/forum/{thread}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/forum/{thread}/reply', [ForumController::class, 'reply'])->name('forum.reply');

    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::post('/conversations', [ConversationController::class, 'start'])->name('conversations.start');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage'])->name('conversations.messages.store');

    Route::get('/referenze', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('/referenze', [ReferralController::class, 'store'])->name('referrals.store');
    Route::patch('/referenze/{referral}/status', [ReferralController::class, 'updateStatus'])->name('referrals.status');

    // ── Abbonamenti ──────────────────────────────────────────────────────────
    Route::get('/abbonamento', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/abbonamento', [SubscriptionController::class, 'request'])->name('subscriptions.request');
    Route::delete('/abbonamento/{subscription}', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
});

// ─── ROTTA TEMPORANEA: backfill referral leggibili ───────────────────────────
// Dopo aver eseguito questa rotta una volta, eliminala e ricarica il file sul server.
Route::get('/admin-tools/backfill-referral-codes', function () {
    abort_unless(
        auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'admin-community']),
        403,
        'Accesso negato.'
    );

    $users = \App\Models\User::query()->whereNull('referral_code')->get();

    if ($users->isEmpty()) {
        return response('<p style="font-family:sans-serif">✅ Nessun utente senza referral_code. Tutto già aggiornato!</p>');
    }

    $results = [];

    foreach ($users as $user) {
        $base = \Illuminate\Support\Str::slug($user->name, '');
        if ($base === '') {
            $base = 'membro';
        }
        $code  = $base;
        $index = 2;
        while (\App\Models\User::query()->where('referral_code', $code)->exists()) {
            $code = $base . $index;
            $index++;
        }
        $user->forceFill(['referral_code' => $code])->saveQuietly();
        $results[] = "✅ {$user->name} → <strong>{$code}</strong>";
    }

    $list = implode('<br>', $results);

    return response(
        '<p style="font-family:sans-serif"><strong>Backfill completato per ' . count($results) . ' utenti:</strong></p>'
        . '<p style="font-family:sans-serif;line-height:2">' . $list . '</p>'
        . '<p style="font-family:sans-serif;color:red"><strong>Ora elimina questa rotta da routes/web.php e ricarica il file sul server!</strong></p>'
    );
})->middleware('auth');
// ─────────────────────────────────────────────────────────────────────────────

require __DIR__.'/auth.php';
