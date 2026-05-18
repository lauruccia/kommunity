<?php

namespace App\Filament\Resources\MemberProfiles\Pages;

use App\Filament\Resources\MemberProfiles\MemberProfileResource;
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
        $previousChapterId = $record->active_chapter_id;

        MemberProfile::$adminOverrideLimit = true;

        try {
            $record->update($data);
        } finally {
            MemberProfile::$adminOverrideLimit = false;
        }

        $this->syncActiveChapterMembership($record, $previousChapterId);

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
