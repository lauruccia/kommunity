<?php

namespace App\Http\Requests;

use App\Support\VideoUploadLimits;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $videoLimits = app(VideoUploadLimits::class);

        return [
            'name' => ['required', 'string', 'max:255'],
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
            'profession_ids' => ['nullable', 'array'],
            'profession_ids.*' => ['integer', 'exists:professions,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'company_interest_type_ids' => ['nullable', 'array'],
            'company_interest_type_ids.*' => ['integer', 'exists:company_interest_types,id'],
            'short_bio' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'services' => ['nullable', 'string', 'max:3000'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'networking_goals' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:4096'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'cover_image' => ['nullable', 'image', 'max:6144'],
            'intro_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:'.$videoLimits->maxSizeKilobytes()],
            'intro_video_url' => ['nullable', 'string', 'max:500'],
            'intro_video_duration_minutes' => ['nullable', Rule::in([2, 3, 5])],
            'gallery_images' => ['nullable', 'array', 'max:12'],
            'gallery_images.*' => ['image', 'max:6144'],
            'preferred_contact_method' => ['nullable', Rule::in(['email', 'phone', 'whatsapp', 'platform'])],
            'show_email' => ['sometimes', 'boolean'],
            'show_phone' => ['sometimes', 'boolean'],
            'show_whatsapp' => ['sometimes', 'boolean'],
            'allow_whatsapp_contact' => ['sometimes', 'boolean'],
            'is_visible_in_directory' => ['sometimes', 'boolean'],
            'onboarding_completed' => ['sometimes', 'boolean'],
        ];
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
