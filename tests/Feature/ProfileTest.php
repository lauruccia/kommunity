<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\MemberGalleryImage;
use App\Models\ProfileVideoAccessRequest;
use App\Models\Profession;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $payload = $this->validProfilePayload($user, [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'short_bio' => 'Bio breve aggiornata',
            'bio' => 'Testo chi sono aggiornato',
            'services' => 'Servizi aggiornati',
            'skills' => 'Competenze aggiornate',
            'networking_goals' => 'Obiettivi aggiornati',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);

        $profile = $user->memberProfile()->first();
        $this->assertSame('Bio breve aggiornata', $profile->short_bio);
        $this->assertSame('Testo chi sono aggiornato', $profile->bio);
        $this->assertSame('Servizi aggiornati', $profile->services);
        $this->assertSame('Competenze aggiornate', $profile->skills);
        $this->assertSame('Obiettivi aggiornati', $profile->networking_goals);

        $onepage = $user->memberOnepage()->first();
        $this->assertSame('Bio breve aggiornata', $onepage->intro_text);
        $this->assertSame('Testo chi sono aggiornato', $onepage->about_text);
        $this->assertSame('Servizi aggiornati', $onepage->services_text);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();
        $payload = $this->validProfilePayload($user, [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $user->email,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_media_can_be_uploaded(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $payload = $this->validProfilePayload($user, [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 600),
            'gallery_images' => [
                UploadedFile::fake()->image('gallery-one.jpg'),
                UploadedFile::fake()->image('gallery-two.jpg'),
            ],
            'intro_video' => UploadedFile::fake()->create('intro.mp4', 256, 'video/mp4'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $profile = $user->memberProfile()->first();
        $onepage = $user->memberOnepage()->first();

        $this->assertNotNull($profile->avatar);
        $this->assertNotNull($profile->intro_video);
        $this->assertNotNull($onepage->cover_image);
        Storage::disk('public')->assertExists($profile->avatar);
        Storage::disk('public')->assertExists($profile->intro_video);
        Storage::disk('public')->assertExists($onepage->cover_image);

        $this->assertCount(2, $user->memberGalleryImages()->get());
        foreach ($user->memberGalleryImages as $galleryImage) {
            Storage::disk('public')->assertExists($galleryImage->image_path);
        }
    }

    public function test_profile_media_can_be_deleted(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        Storage::disk('public')->put('members/avatars/current.jpg', 'avatar');
        Storage::disk('public')->put('members/videos/current.mp4', 'video');
        Storage::disk('public')->put('members/covers/current.jpg', 'cover');
        Storage::disk('public')->put('members/gallery/current.jpg', 'gallery');

        $user->memberProfile()->update([
            'avatar' => 'members/avatars/current.jpg',
            'intro_video' => 'members/videos/current.mp4',
            'intro_video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);
        $user->memberOnepage()->update(['cover_image' => 'members/covers/current.jpg']);
        $galleryImage = $user->memberGalleryImages()->create([
            'image_path' => 'members/gallery/current.jpg',
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('profile.avatar.destroy'))
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'avatar-deleted');
        $this->assertNull($user->memberProfile()->first()->avatar);
        Storage::disk('public')->assertMissing('members/avatars/current.jpg');

        $this->actingAs($user)
            ->delete(route('profile.video.destroy'))
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'video-deleted');
        $profile = $user->memberProfile()->first();
        $this->assertNull($profile->intro_video);
        $this->assertNull($profile->intro_video_url);
        Storage::disk('public')->assertMissing('members/videos/current.mp4');

        $this->actingAs($user)
            ->delete(route('profile.banner.destroy'))
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'banner-deleted');
        $this->assertNull($user->memberOnepage()->first()->cover_image);
        Storage::disk('public')->assertMissing('members/covers/current.jpg');

        $this->actingAs($user)
            ->delete(route('profile.gallery.destroy', $galleryImage))
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'gallery-image-deleted');
        $this->assertNull(MemberGalleryImage::query()->find($galleryImage->id));
        Storage::disk('public')->assertMissing('members/gallery/current.jpg');
    }

    public function test_profile_video_access_requires_accepted_exchange(): void
    {
        $requester = User::factory()->create();
        $recipient = User::factory()->create();

        $requester->memberProfile()->update([
            'intro_video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'onboarding_completed' => true,
            'is_active' => true,
        ]);
        $recipient->memberProfile()->update([
            'intro_video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'onboarding_completed' => true,
            'is_active' => true,
        ]);

        $this->assertFalse($recipient->memberProfile->canViewIntroVideo($requester));

        $this->actingAs($requester)
            ->post(route('profile-video-access.store', $recipient))
            ->assertRedirect()
            ->assertSessionHas('status', 'video-access-requested');

        $accessRequest = ProfileVideoAccessRequest::query()
            ->where('requester_id', $requester->id)
            ->where('recipient_id', $recipient->id)
            ->firstOrFail();

        $this->assertSame('pending', $accessRequest->status);

        $this->actingAs($recipient)
            ->patch(route('profile-video-access.respond', $accessRequest), ['status' => 'accepted'])
            ->assertRedirect()
            ->assertSessionHas('status', 'video-access-accepted');

        $this->assertTrue($recipient->memberProfile->fresh()->canViewIntroVideo($requester));
        $this->assertTrue($requester->memberProfile->fresh()->canViewIntroVideo($recipient));
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    private function validProfilePayload(User $user, array $overrides = []): array
    {
        $region = Region::query()->firstOrCreate(
            ['slug' => 'lombardia'],
            ['name' => 'Lombardia', 'code' => 'LOM'],
        );

        $city = City::query()->firstOrCreate(
            ['slug' => 'milano'],
            ['name' => 'Milano', 'region_id' => $region->id, 'province' => 'MI'],
        );

        $profession = Profession::query()->firstOrCreate(
            ['slug' => 'consulente'],
            ['name' => 'Consulente', 'is_active' => true],
        );

        $nameParts = preg_split('/\s+/', trim($user->name), 2);

        return array_merge([
            'first_name' => $nameParts[0] ?? 'Test',
            'last_name' => $nameParts[1] ?? 'User',
            'email' => $user->email,
            'phone' => '3331234567',
            'profession_ids' => [$profession->id],
            'city_id' => $city->id,
            'region_id' => $region->id,
            'preferred_contact_method' => 'email',
            'intro_video_duration_minutes' => 2,
        ], $overrides);
    }
}
