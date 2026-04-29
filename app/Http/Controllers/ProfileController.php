<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Category;
use App\Models\City;
use App\Models\CompanyInterestType;
use App\Models\MemberGalleryImage;
use App\Models\ProfileSuggestion;
use App\Models\Profession;
use App\Models\Province;
use App\Models\Region;
use App\Support\VideoCompressor;
use App\Support\VideoUploadLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'profile' => $request->user()->memberProfile()->with(['chapter', 'categories', 'professions', 'companyInterestTypes', 'city'])->firstOrFail(),
            'galleryImages' => $request->user()->memberGalleryImages()->get(),
            'professions' => Profession::query()->where('is_active', true)->orderBy('name')->get(),
            'rootCategories' => Category::query()->with(['activeChildren'])->whereNull('parent_id')->where('is_active', true)->orderBy('name')->get(),
            'regions' => Region::query()->orderBy('name')->get(),
            'provinces' => Province::query()->orderBy('name')->get(),
            'cities' => City::query()->orderBy('name')->get(),
            'companyInterestTypes' => CompanyInterestType::query()->where('is_active', true)->orderBy('name')->get(),
            'referralLink' => $request->user()->referralRegistrationUrl(),
            'videoUploadLimits' => app(VideoUploadLimits::class),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, VideoCompressor $videoCompressor): RedirectResponse
    {
        $validated = $request->validated();
        $profile = $request->user()->memberProfile()->firstOrFail();
        $onepage = $request->user()->memberOnepage()->firstOrCreate(
            [],
            [
                'slug' => $request->user()->referral_code ?: 'membro-'.$request->user()->id,
                'title' => $request->user()->name,
                'hero_title' => $request->user()->name,
                'hero_subtitle' => 'Profilo professionale in costruzione',
                'intro_text' => 'Questo spazio verra popolato con la presentazione professionale del membro.',
                'about_text' => 'Kommunity genera automaticamente un mini sito personale per ogni iscritto.',
                'services_text' => 'Servizi e competenze saranno aggiornati durante l\'onboarding.',
                'cta_text' => 'Prenota un incontro one-to-one',
                'template' => 'minimal-professional',
                'is_active' => true,
                'visibility' => 'registered_users',
                'seo_title' => $request->user()->name.' | Kommunity',
                'seo_description' => 'Mini sito professionale di '.$request->user()->name.' su Kommunity.',
            ]
        );

        $avatar = $this->storePublicFile($request->file('avatar'), $profile->avatar, 'members/avatars') ?? $profile->avatar;
        $logo = $this->storePublicFile($request->file('logo'), $profile->logo, 'members/logos') ?? $profile->logo;
        $coverImage = $this->storePublicFile($request->file('cover_image'), $onepage->cover_image, 'members/covers') ?? $onepage->cover_image;
        $introVideo = $this->storePublicVideo($request->file('intro_video'), $profile->intro_video, 'members/videos', $videoCompressor) ?? $profile->intro_video;
        $fullName = trim($validated['first_name'].' '.$validated['last_name']);

        $request->user()->fill([
            'name' => $fullName,
            'email' => $validated['email'],
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // Normalizza URL: aggiunge https:// se manca lo schema (accetta input con/senza http/www)
        foreach (['website', 'linkedin_url', 'facebook_url', 'instagram_url'] as $urlField) {
            if (!empty($validated[$urlField])) {
                $url = trim($validated[$urlField]);
                if ($url !== '' && !preg_match('#^https?://#i', $url)) {
                    $url = 'https://' . $url;
                }
                $validated[$urlField] = $url;
            }
        }

        // Normalizza intro_video_url: aggiunge https:// se manca
        $introVideoUrl = null;
        if (!empty($validated['intro_video_url'])) {
            $introVideoUrl = trim($validated['intro_video_url']);
            if ($introVideoUrl !== '' && !preg_match('#^https?://#i', $introVideoUrl)) {
                $introVideoUrl = 'https://' . $introVideoUrl;
            }
        }

        $profileData = collect($validated)
            ->except([
                'name',
                'first_name',
                'last_name',
                'email',
                'avatar',
                'logo',
                'cover_image',
                'intro_video',
                'intro_video_url',
                'gallery_images',
                'province_id',
                'company_interest_type_ids',
                'category_ids',
                'profession_ids',
            ])
            ->merge([
                'show_email' => $request->boolean('show_email'),
                'show_phone' => $request->boolean('show_phone'),
                'show_whatsapp' => $request->boolean('show_whatsapp'),
                'allow_whatsapp_contact' => $request->boolean('allow_whatsapp_contact'),
                // is_visible_in_directory: gestito solo dall'admin, non sovrascriviamo mai
                'onboarding_completed' => $request->boolean('onboarding_completed'),
                'is_active' => true,
                // Non sovrascrivere status se già gestito dall'admin (active/suspended)
                'status' => in_array($profile->status?->value, ['active', 'suspended'])
                    ? $profile->status->value
                    : ($request->boolean('onboarding_completed') ? 'pending_approval' : $profile->status?->value),
                'profession_id' => collect($validated['profession_ids'] ?? [])->first(),
                'avatar' => $avatar,
                'logo' => $logo,
                'intro_video' => $introVideo,
                'intro_video_url' => $introVideoUrl,
            ])
            ->all();

        $request->user()->memberProfile()->update($profileData);
        $profile = $request->user()->memberProfile()->first();
        $profile?->companyInterestTypes()->sync($validated['company_interest_type_ids'] ?? []);
        $profile?->categories()->sync($validated['category_ids'] ?? []);
        $profile?->professions()->sync($validated['profession_ids'] ?? []);

        $onepage->update([
            'title' => $request->user()->name,
            'hero_title' => $request->user()->name,
            'hero_subtitle' => trim(($profile?->profession?->name ?? 'Professionista').' · '.($profile?->city?->name ?? 'Italia')),
            'intro_text' => $validated['short_bio'] ?? null,
            'about_text' => $validated['bio'] ?? null,
            'services_text' => $validated['services'] ?? null,
            'cta_text' => 'Prenota un incontro one-to-one',
            'cover_image' => $coverImage,
        ]);

        // Assicura che la directory esista sul disco public
        Storage::disk('public')->makeDirectory('members/gallery');

        $galleryFiles = $request->file('gallery_images') ?? [];
        foreach ((array) $galleryFiles as $galleryImage) {
            // Salta file nulli o non validi (upload parziali, errori PHP)
            if (! $galleryImage || ! ($galleryImage instanceof \Illuminate\Http\UploadedFile) || ! $galleryImage->isValid()) {
                continue;
            }
            try {
                $path = $galleryImage->store('members/gallery', 'public');
                if ($path) {
                    $request->user()->memberGalleryImages()->create([
                        'image_path' => $path,
                        'sort_order' => ((int) $request->user()->memberGalleryImages()->max('sort_order')) + 1,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Gallery upload fallito', ['error' => $e->getMessage(), 'user' => $request->user()->id]);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function storeSuggestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['profession', 'category', 'city', 'company_interest_type', 'other'])],
            'value' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        ProfileSuggestion::query()->create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'value' => $validated['value'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return Redirect::route('profile.edit')->with('status', 'suggestion-created');
    }

    public function destroyGalleryImage(Request $request, MemberGalleryImage $memberGalleryImage): RedirectResponse
    {
        abort_unless($memberGalleryImage->user_id === $request->user()->id, 403);

        $this->deletePublicImage($memberGalleryImage->image_path);
        $memberGalleryImage->delete();

        return Redirect::route('profile.edit')->with('status', 'gallery-image-deleted');
    }

    private function storePublicFile(?UploadedFile $file, ?string $currentUrl, string $folder): ?string
    {
        if (! $file) {
            return null;
        }

        $path = $file->store($folder, 'public');

        $this->deletePublicImage($currentUrl);

        return $path;
    }

    private function storePublicVideo(?UploadedFile $file, ?string $currentUrl, string $folder, VideoCompressor $videoCompressor): ?string
    {
        if (! $file) {
            return null;
        }

        $path = $videoCompressor->storeOptimized($file, $folder);

        $this->deletePublicImage($currentUrl);

        return $path;
    }

    private function deletePublicImage(?string $url): void
    {
        if (! $url) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path)) {
            return;
        }

        if (str_contains($path, '/storage/')) {
            $relativePath = ltrim(substr($path, strpos($path, '/storage/') + 9), '/');
        } else {
            $relativePath = ltrim($url, '/');
        }

        if ($relativePath !== '') {
            Storage::disk('public')->delete($relativePath);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
