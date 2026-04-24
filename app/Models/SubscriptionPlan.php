<?php

namespace App\Models;

use App\Enums\SubscriptionPlanType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'plan_type',
        'price_monthly', 'price_yearly', 'trial_days',
        'features', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'plan_type'     => SubscriptionPlanType::class,
            'price_monthly' => 'decimal:2',
            'price_yearly'  => 'decimal:2',
            'trial_days'    => 'integer',
            'features'      => 'array',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
        ];
    }

    // ── Scope ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ── Relazioni ──────────────────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class, 'plan_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function hasTrial(): bool
    {
        return $this->trial_days > 0;
    }

    public function includesPage(): bool
    {
        return $this->plan_type?->includesPage() ?? false;
    }

    public function formattedPrice(string $billing = 'monthly'): string
    {
        $price = $billing === 'yearly' ? $this->price_yearly : $this->price_monthly;
        if ((float) $price === 0.0) {
            return 'Gratuito';
        }
        return '€' . number_format((float) $price, 2, ',', '.') . ($billing === 'yearly' ? '/anno' : '/mese');
    }
}
