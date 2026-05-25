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
use App\Services\ProfileAiRewriteService;
use App\Services\ProfileCompletionService;
use App\Support\VideoCompressor;
use App\Support\VideoUploadLimits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $user = $request->user()->load(['memberProfile.professions']);

        $professions = Cache::remember('professions_active_list', 3600, fn () =>
            Profession::query()->where('is_active', true)->orderBy('name')->get()
        );

        return view('profile.edit', [
            'user'              => $user,
            'profile'           => $user->memberProfile()->with(['chapter', 'primaryChapter', 'categories', 'professions', 'companyInterestTypes', 'professionsOfInterest', 'city'])->firstOrFail(),
            'userPlanets'       => $user->planets()->with('leaders')->orderBy('name')->get(),
            'profileCompletion' => (new ProfileCompletionService())->calculate($user),
            'galleryImages'     => $user->memberGalleryImages()->get(),
            'professions'       => $professions,
            // Usato per la sezione "tipologie professionisti da conoscere"
            'professionsForInterest' => $professions,
            'rootCategories'    => Cache::remember('root_categories_tree', 3600, fn () =>
                Category::query()->with(['activeChildren'])->whereNull('parent_id')->where('is_active', true)->orderBy('name')->get()
            ),
            'regions'           => Cache::remember('regions_list', 86400, fn () => Region::query()->orderBy('name')->get()),
            'provinces'         => Cache::remember('provinces_list', 86400, fn () => Province::query()->orderBy('name')->get()),
            'cities'            => Cache::remember('cities_list', 86400, fn () => City::query()->orderBy('name')->get()),
            'companyInterestTypes' => Cache::remember('company_interest_types_list', 3600, fn () =>
                CompanyInterestType::query()->where('is_active', true)->orderBy('name')->get()
            ),
            'referralLink'      => $user->referralRegistrationUrl(),
            'videoUploadLimits' => app(VideoUploadLimits::class),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(
        ProfileUpdateRequest $request,
        VideoCompressor $videoCompressor,
        ProfileAiRewriteService $profileAiRewriteService
    ): RedirectResponse
    {
        $this->guardAgainstUploadErrors(['avatar', 'logo', 'cover_image', 'intro_video']);

        $validated = $request->validated();
        $profile = $request->user()->memberProfile()->firstOrFail();
        $onepage = $request->user()->memberOnepage()->firstOrCreate(
            [],
            [
                'slug' => $request->user()->referral_code ?: 'membro-'.$request->user()->id,
                'title' => $request->user()->name,
                'hero_title' => $request->user()->name,
                'hero_subtitle' => null,
                'intro_text' => null,
                'about_text' => null,
                'services_text' => null,
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
            'show_online_status' => $request->boolean('show_online_status'),
            'show_read_receipts' => $request->boolean('show_read_receipts'),
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        foreach (['website', 'linkedin_url', 'facebook_url', 'instagram_url'] as $urlField) {
            if (!empty($validated[$urlField])) {
                $url = trim($validated[$urlField]);
                if ($url !== '' && !preg_match('#^https?://#i', $url)) {
                    $url = 'https://' . $url;
                }
                $validated[$urlField] = $url;
            }
        }

        $introVideoUrl = null;
        if (!empty($validated['intro_video_url'])) {
            $introVideoUrl = trim($validated['intro_video_url']);
            if ($introVideoUrl !== '' && !preg_match('#^https?://#i', $introVideoUrl)) {
                $introVideoUrl = 'https://' . $introVideoUrl;
            }
        }

        $useAiProfileRewrite = $request->boolean('use_ai_profile_rewrite');
        $profileTextFields = collect($validated)
            ->only(['short_bio', 'bio', 'services', 'skills', 'networking_goals'])
            ->all();

        $aiRewrote = false;
        if ($useAiProfileRewrite) {
            $rewritten = $profileAiRewriteService->rewrite(
                $profile->loadMissing(['user', 'profession', 'city']),
                $profileTextFields,
                $this->buildProfileAiContext($validated)
            );
            $aiRewrote = $profileAiRewriteService->isConfigured()
                && collect($rewritten)->contains(fn ($v, $k) => trim((string) $v) !== trim((string) ($profileTextFields[$k] ?? '')));
            $profileTextFields = $rewritten;
            $validated = array_merge($validated, $profileTextFields);
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
                'show_online_status',
                'show_read_receipts',
                'province_id',
                'company_interest_type_ids',
                'profession_interest_ids',
                'category_ids',
                'profession_ids',
            ])
            ->merge([
                'show_email' => $request->boolean('show_email'),
                'show_phone' => $request->boolean('show_phone'),
                'show_whatsapp' => $request->boolean('show_whatsapp'),
                'allow_whatsapp_contact' => $request->boolean('allow_whatsapp_contact'),
                // Il checkbox è un "esegui ora": dopo il salvataggio si azzera sempre.
                // L'utente deve rispuntarlo se vuole un'altra rielaborazione.
                'use_ai_profile_rewrite' => false,
                'onboarding_completed' => $profile->onboarding_completed
                    || $request->boolean('onboarding_completed')
                    || (
                        ! empty($validated['profession_ids'] ?? [])
                        && ! empty($validated['city_id'] ?? null)
                        && filled($validated['phone'] ?? null)
                    ),
                'status' => in_array($profile->status?->value, ['active', 'suspended'])
                    ? $profile->status->value
                    : (
                        ($profile->onboarding_completed
                            || $request->boolean('onboarding_completed')
                            || (! empty($validated['profession_ids'] ?? []) && ! empty($validated['city_id'] ?? null) && filled($validated['phone'] ?? null))
                        ) ? 'pending_approval' : $profile->status?->value
                    ),
                'profession_id' => collect($validated['profession_ids'] ?? [])->first(),
                'avatar' => $avatar,
                'logo' => $logo,
                'intro_video' => $introVideo,
                'intro_video_url' => $introVideoUrl,
                'intro_video_visibility' => $validated['intro_video_visibility'] ?? 'public',
            ])
            ->all();

        $request->user()->memberProfile()->update($profileData);
        $profile = $request->user()->memberProfile()->first();
        $profile?->companyInterestTypes()->sync($validated['company_interest_type_ids'] ?? []);
        $profile?->categories()->sync($validated['category_ids'] ?? []);
        // Auto-include i padri nella gerarchia: se seleziono "Marketing digitale",
        // aggiungo anche "Marketing" (e così via fino alla radice)
        $expandedProfIds = collect($validated['profession_ids'] ?? [])
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->flatMap(function (int $id): array {
                $ids = [$id];
                $prof = Profession::find($id);
                while ($prof?->parent_id) {
                    $ids[] = (int) $prof->parent_id;
                    $prof = Profession::find($prof->parent_id);
                }
                return $ids;
            })
            ->unique()
            ->values()
            ->all();
        $profile?->professions()->sync($expandedProfIds);
        $profile?->professionsOfInterest()->sync($validated['profession_interest_ids'] ?? []);

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

        Storage::disk('public')->makeDirectory('members/gallery');

        $galleryFiles = $request->file('gallery_images') ?? [];
        foreach ((array) $galleryFiles as $galleryImage) {
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

        $status = $aiRewrote ? 'profile-updated-ai' : 'profile-updated';

        return Redirect::route('profile.edit')->with('status', $status);
    }

    /**
     * @param array<string, mixed> $profileTextFields
     */
    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function buildProfileAiContext(array $validated): array
    {
        $professionIds = collect($validated['profession_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();
        $categoryIds = collect($validated['category_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();
        $companyInterestTypeIds = collect($validated['company_interest_type_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();
        $professionInterestIds = collect($validated['profession_interest_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();

        return [
            'azienda' => $validated['company_name'] ?? null,
            'professione_altro' => $validated['profession_other'] ?? null,
            'professioni' => empty($professionIds)
                ? null
                : Profession::query()->whereKey($professionIds)->orderBy('name')->pluck('name')->implode(', '),
            'categorie' => empty($categoryIds)
                ? null
                : Category::query()->whereKey($categoryIds)->orderBy('name')->pluck('name')->implode(', '),
            'tipologie_aziende_da_conoscere' => empty($companyInterestTypeIds)
                ? null
                : CompanyInterestType::query()->whereKey($companyInterestTypeIds)->orderBy('name')->pluck('name')->implode(', '),
            'professioni_da_incontrare' => empty($professionInterestIds)
                ? null
                : Profession::query()->whereKey($professionInterestIds)->orderBy('name')->pluck('name')->implode(', '),
            'citta' => empty($validated['city_id'] ?? null)
                ? null
                : City::query()->whereKey($validated['city_id'])->value('name'),
            'regione' => empty($validated['region_id'] ?? null)
                ? null
                : Region::query()->whereKey($validated['region_id'])->value('name'),
            'sito_web' => $validated['website'] ?? null,
            'linkedin' => $validated['linkedin_url'] ?? null,
        ];
    }

    public function storeSuggestion(Request $request): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return Redirect::route('profile.edit')->with('status', 'suggestion-created');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $profile = $request->user()->memberProfile()->firstOrFail();
        $this->deletePublicImage($profile->avatar);
        $profile->update(['avatar' => null]);

        return Redirect::route('profile.edit')->with('status', 'avatar-deleted');
    }

    public function destroyBanner(Request $request): RedirectResponse
    {
        $onepage = $request->user()->memberOnepage;
        if ($onepage && $onepage->cover_image) {
            $this->deletePublicImage($onepage->cover_image);
            $onepage->update(['cover_image' => null]);
        }

        return Redirect::route('profile.edit')->with('status', 'banner-deleted');
    }

    public function destroyVideo(Request $request): RedirectResponse
    {
        $profile = $request->user()->memberProfile()->firstOrFail();

        if ($profile->intro_video) {
            $this->deletePublicImage($profile->intro_video);
        }

        $profile->update([
            'intro_video'     => null,
            'intro_video_url' => null,
        ]);

        return Redirect::route('profile.edit')->with('status', 'video-deleted');
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

        Storage::disk('public')->makeDirectory($folder);

        $path = $file->store($folder, 'public');

        if (! $path) {
            Log::warning('storePublicFile: salvataggio fallito', [
                'folder' => $folder,
                'user'   => request()->user()?->id,
            ]);
            return null;
        }

        $this->deletePublicImage($currentUrl);

        return $path;
    }

    /**
     * Controlla i campi file di $_FILES e lancia ValidationException
     * con un messaggio chiaro se PHP ha rifiutato l'upload.
     *
     * @param array<int,string> $fields nomi dei field file da controllare
     */
    private function guardAgainstUploadErrors(array $fields): void
    {
        $errors = [];

        foreach ($fields as $field) {
            $entry = $_FILES[$field] ?? null;

            if (! is_array($entry)) {
                continue;
            }

            $code = $entry['error'] ?? UPLOAD_ERR_OK;

            if ($code === UPLOAD_ERR_OK || $code === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $errors[$field] = match ($code) {
                UPLOAD_ERR_INI_SIZE,
                UPLOAD_ERR_FORM_SIZE => 'File troppo grande. Massimo consentito dal server: '
                    . ini_get('upload_max_filesize')
                    . ' (post_max_size: ' . ini_get('post_max_size') . ').'
                    . ' Comprimi l\'immagine o contatta l\'amministratore per aumentare i limiti PHP.',
                UPLOAD_ERR_PARTIAL    => 'Caricamento interrotto a meta\'. Riprova con una connessione piu\' stabile.',
                UPLOAD_ERR_NO_TMP_DIR => 'Errore server: directory temporanea non disponibile. Contatta l\'amministratore.',
                UPLOAD_ERR_CANT_WRITE => 'Errore server: impossibile scrivere il file su disco. Contatta l\'amministratore.',
                UPLOAD_ERR_EXTENSION  => 'Errore server: estensione PHP ha bloccato l\'upload. Contatta l\'amministratore.',
                default               => 'Errore di upload (codice ' . $code . '). Riprova.',
            };
        }

        if (! empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    private function storePublicVideo(?UploadedFile $file, ?string $currentUrl, string $folder, VideoCompressor $videoCompressor): ?string
    {
        if (! $file) {
            return null;
        }

        Storage::disk('public')->makeDirectory($folder);

        $path = $videoCompressor->storeOptimized($file, $folder);

        if (! $path) {
            Log::warning('storePublicVideo: salvataggio fallito', [
                'folder' => $folder,
                'user'   => request()->user()?->id,
            ]);
            return null;
        }

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
