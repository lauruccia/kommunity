<?php

namespace App\Filament\Resources\MemberProfiles\Pages;

use App\Filament\Resources\MemberProfiles\MemberProfileResource;
use App\Models\MemberOnepage;
use App\Models\MemberProfile;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EditMemberProfile extends EditRecord
{
    protected static string $resource = MemberProfileResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // cover_image appartiene a MemberOnepage, non a MemberProfile:
        // la estraiamo prima di aggiornare il record principale.
        $coverImage = array_key_exists('cover_image', $data) ? $data['cover_image'] : false;
        unset($data['cover_image']);

        $previousChapterId = $record->active_chapter_id;

        MemberProfile::$adminOverrideLimit = true;

        try {
            $record->update($data);
        } finally {
            MemberProfile::$adminOverrideLimit = false;
        }

        $this->syncActiveChapterMembership($record, $previousChapterId);

        // Salva il banner sulla pagina personale del membro (MemberOnepage)
        if ($coverImage !== false && $record->user_id) {
            $onepage = MemberOnepage::firstOrNew(['user_id' => $record->user_id]);
            $onepage->cover_image = $coverImage ?: null;
            // Valori minimi obbligatori se il record viene creato ora
            if (! $onepage->exists) {
                $onepage->slug = 'membro-' . $record->user_id;
                $onepage->visibility = 'members_only';
                $onepage->is_active = false;
            }
            $onepage->save();
        }

        return $record;
    }

    private function syncActiveChapterMembership(Model $record, mixed $previousChapterId): void
    {
        $chapterId = $record->active_chapter_id;

        if ($chapterId && $record->user_id) {
            DB::table('chapter_members')->updateOrInsert(
                [
                    'chapter_id' => $chapterId,
                    'user_id' => $record->user_id,
                ],
                [
                    'status' => 'active',
                    'joined_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        Cache::forget('directory.random_ids.planet.all');

        foreach (array_filter([(string) $previousChapterId, (string) $chapterId]) as $id) {
            Cache::forget('directory.random_ids.planet.' . $id);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
