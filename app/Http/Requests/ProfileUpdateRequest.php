<?php

namespace App\Http\Requests;

use App\Support\VideoUploadLimits;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && (! $this->filled('first_name') || ! $this->filled('last_name'))) {
            $parts = preg_split('/\s+/', trim((string) $this->input('name')), 2);

            $this->merge([
                'first_name' => $this->input('first_name') ?: ($parts[0] ?? ''),
                'last_name' => $this->input('last_name') ?: ($parts[1] ?? ''),
            ]);
        }

        // Auto-compila regione e provincia dalla città selezionata: ogni comune
        // appartiene sempre a una provincia e a una regione, quindi i due campi
        // vengono derivati dalla città per garantirne coerenza e obbligatorietà.
        if ($this->filled('city_id')) {
            $city = \App\Models\City::query()->find($this->input('city_id'));

            if ($city) {
                $this->merge([
                    'region_id' => $city->region_id,
                    'province_id' => $city->province_id,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $videoLimits = app(VideoUploadLimits::class);

        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'company_name' => ['nullable', 'string', 'max:255'],
            'profession_id' => ['nullable', 'exists:professions,id'],
            'profession_other' => ['nullable', 'string', 'max:255'],
            'profession_ids' => ['required', 'array', 'min:1'],
            'profession_ids.*' => ['integer', 'exists:professions,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'province_id' => ['required', 'exists:provinces,id'],
            'company_interest_type_ids' => ['nullable', 'array'],
            'company_interest_type_ids.*' => ['integer', 'exists:company_interest_types,id'],
            'profession_interest_ids' => ['nullable', 'array'],
            'profession_interest_ids.*' => ['integer', 'exists:professions,id'],
            'short_bio' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'services' => ['nullable', 'string', 'max:3000'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'networking_goals' => ['nullable', 'string', 'max:2000'],
            'use_ai_profile_rewrite' => ['sometimes', 'boolean'],
            'website' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'intro_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:'.$videoLimits->maxSizeKilobytes()],
            'intro_video_url' => ['nullable', 'string', 'max:500'],
            'intro_video_visibility' => ['nullable', Rule::in(['public', 'on_request'])],
            'intro_video_duration_minutes' => ['nullable', Rule::in([2, 3, 5])],
            'gallery_images' => ['nullable', 'array', 'max:12'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'primary_chapter_id' => [
                'nullable',
                Rule::in($this->user()->planets()->pluck('chapters.id')->toArray()),
            ],
            'preferred_contact_method' => ['nullable', Rule::in(['email', 'phone', 'whatsapp', 'platform'])],
            'show_email' => ['sometimes', 'boolean'],
            'show_phone' => ['sometimes', 'boolean'],
            'show_whatsapp' => ['sometimes', 'boolean'],
            'allow_whatsapp_contact' => ['sometimes', 'boolean'],
            'show_online_status' => ['sometimes', 'boolean'],
            'show_read_receipts' => ['sometimes', 'boolean'],
            'is_visible_in_directory' => ['sometimes', 'boolean'],
            'onboarding_completed' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Logga gli errori di validazione quando il salvataggio profilo fallisce,
     * così è possibile capire quale campo ha bloccato il salvataggio del singolo
     * utente (utile in produzione: storage/logs/laravel.log).
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::warning('Profilo non salvato: validazione fallita', [
            'user_id' => $this->user()?->id,
            'email'   => $this->user()?->email,
            'errors'  => $validator->errors()->toArray(),
        ]);

        parent::failedValidation($validator);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $video = $this->file('intro_video');

            if (! $video) {
                return;
            }

            try {
                app(VideoUploadLimits::class)->assertDurationWithinLimit($video);
            } catch (\Illuminate\Validation\ValidationException $e) {
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add($field, $message);
                    }
                }
            }
        });
    }
}
