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

        // admin_planets è un campo virtuale gestito separatamente
        $adminPlanets = $this->data['admin_planets'] ?? null;
        unset($data['admin_planets']);

        $previousChapterId = $record->active_chapter_id;

        /** @var MemberProfile $record */
        $record->updateWithAdminOverride($data);

        $this->syncActiveChapterMembership($record, $previousChapterId);

        // Sincronizza i pianeti iscritti (chapter_members)
        if ($adminPlanets !== null && $record->user_id) {
            $this->syncAdminPlanets($record, array_map('intval', (array) $adminPlanets));
        }

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

    /**
     * Sincronizza chapter_members in base alla selezione admin.
     * Aggiunge i nuovi pianeti, rimuove quelli deselezionati.
     * Non rimuove mai il pianeta attivo principale.
     */
    private function syncAdminPlanets(Model $record, array $selectedIds): void
    {
        $userId = $record->user_id;
        $activePlanetId = $record->active_chapter_id;

        // Garantisce che il pianeta attivo sia sempre incluso
        if ($activePlanetId && ! in_array($activePlanetId, $selectedIds, true)) {
            $selectedIds[] = $activePlanetId;
        }

        $existing = DB::table('chapter_members')
            ->where('user_id', $userId)
            ->pluck('chapter_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $toAdd    = array_diff($selectedIds, $existing);
        $toRemove = array_diff($existing, $selectedIds);

        foreach ($toAdd as $chapterId) {
            DB::table('chapter_members')->insertOrIgnore([
                'chapter_id' => $chapterId,
                'user_id'    => $userId,
                'status'     => 'active',
                'joined_at'  => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! empty($toRemove)) {
            DB::table('chapter_members')
                ->where('user_id', $userId)
                ->whereIn('chapter_id', $toRemove)
                ->delete();
        }

        Cache::forget('directory.random_ids.planet.all');
        foreach (array_unique(array_merge($toAdd, $toRemove)) as $id) {
            Cache::forget('directory.random_ids.planet.' . $id);
        }
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
