<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\SubscriptionStatus;
use App\Models\MemberSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /** Pagina piani: mostra i piani attivi */
    public function index(): View
    {
        $plans = SubscriptionPlan::active()->get();
        $user  = auth()->user();

        $currentSubscription = $user->activeSubscription()
            ?? $user->pendingSubscription();

        return view('subscriptions.index', compact('plans', 'currentSubscription'));
    }

    /** Il membro invia richiesta di abbonamento */
    public function request(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id'          => ['required', 'exists:subscription_plans,id'],
            'payment_method'   => ['required', 'in:' . implode(',', array_column(PaymentMethod::cases(), 'value'))],
            'payment_reference'=> ['nullable', 'string', 'max:255'],
            'payment_notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        $user = auth()->user();

        // Blocca se ha già un abbonamento attivo o in attesa
        if ($user->activeSubscription()?->isAccessible()) {
            return back()->with('error', 'Hai già un abbonamento attivo.');
        }
        if ($user->pendingSubscription()) {
            return back()->with('error', 'Hai già una richiesta in attesa di approvazione.');
        }

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $subscription = MemberSubscription::create([
            'user_id'           => $user->id,
            'plan_id'           => $plan->id,
            'status'            => SubscriptionStatus::Pending,
            'payment_method'    => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'payment_notes'     => $validated['payment_notes'] ?? null,
            'requested_at'      => now(),
        ]);

        return redirect()->route('subscriptions.index')
            ->with('success', 'Richiesta inviata! Il tuo abbonamento verrà attivato dopo la verifica del pagamento.');
    }

    /** Il membro annulla la propria richiesta pendente */
    public function cancel(MemberSubscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === auth()->id(), 403);
        abort_unless($subscription->status === SubscriptionStatus::Pending, 403);

        $subscription->update(['status' => SubscriptionStatus::Cancelled]);

        return redirect()->route('subscriptions.index')
            ->with('success', 'Richiesta annullata.');
    }
}
