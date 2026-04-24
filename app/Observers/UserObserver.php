<?php

namespace App\Observers;

use App\Enums\ContactMethod;
use App\Enums\MemberProfileStatus;
use App\Enums\OnepageVisibility;
use App\Models\MemberOnepage;
use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function created(User $user): void
    {
        $user->forceFill([
            'referral_code' => $this->uniqueReferralCode($user->name),
        ])->saveQuietly();

        $user->memberProfile()->create([
            'short_bio' => 'Nuovo membro della kommunity Kommunity.',
            'preferred_contact_method' => ContactMethod::Email,
            'is_visible_in_directory' => true,
            'is_active' => false,
            'onboarding_completed' => false,
            'status' => MemberProfileStatus::Draft,
        ]);

        $user->memberOnepage()->create([
            'slug' => $this->uniqueSlug($user->name),
            'title' => $user->name,
            'hero_title' => $user->name,
            'hero_subtitle' => 'Profilo professionale in costruzione',
            'intro_text' => 'Questo spazio verrà popolato con la presentazione professionale del membro.',
            'about_text' => 'Kommunity genera automaticamente un mini sito personale per ogni iscritto.',
            'services_text' => 'Servizi e competenze saranno aggiornati durante l\'onboarding.',
            'cta_text' => 'Richiedi un incontro one-to-one',
            'template' => 'minimal-professional',
            'is_active' => true,
            'visibility' => OnepageVisibility::RegisteredUsers,
            'seo_title' => $user->name.' | Kommunity',
            'seo_description' => 'Mini sito professionale di '.$user->name.' su Kommunity.',
        ]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'membro';
        $slug = $base;
        $index = 1;

        while (MemberOnepage::query()->where('slug', $slug)->exists()) {
            $index++;
            $slug = $base.'-'.$index;
        }

        return $slug;
    }

    protected function uniqueReferralCode(string $name): string
    {
        $base = Str::slug($name, '');

        if ($base === '') {
            $base = 'membro';
        }

        $code = $base;
        $index = 2;

        while (User::query()->where('referral_code', $code)->exists()) {
            $code = $base . $index;
            $index++;
        }

        return $code;
    }
}
