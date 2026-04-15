<?php

namespace App\Observers;

use App\Models\Section;
use App\Models\User;

class SectionObserver
{
    /**
     * Handle the Section "saved" event.
     * This catches both created and updated events when homeroom_teacher_id changes.
     */
    public function saved(Section $section): void
    {
        // If the homeroom teacher was changed
        if ($section->wasChanged('homeroom_teacher_id')) {
            $this->syncRoles($section->getOriginal('homeroom_teacher_id'), $section->homeroom_teacher_id);
        }
    }

    /**
     * Handle the Section "created" event.
     */
    public function created(Section $section): void
    {
        if ($section->homeroom_teacher_id) {
            $this->syncRoles(null, $section->homeroom_teacher_id);
        }
    }

    /**
     * Handle the Section "deleted" event.
     */
    public function deleted(Section $section): void
    {
        if ($section->homeroom_teacher_id) {
            $this->syncRoles($section->homeroom_teacher_id, null);
        }
    }

    /**
     * Sync the Homeroom Teacher role for the old and new teacher
     */
    private function syncRoles(?int $oldTeacherId, ?int $newTeacherId): void
    {
        // If there was an old teacher, check if they still have other homerooms. If not, remove role.
        if ($oldTeacherId) {
            $oldTeacherStillHasHomerooms = Section::where('homeroom_teacher_id', $oldTeacherId)->exists();
            if (!$oldTeacherStillHasHomerooms) {
                $oldTeacherUser = User::find($oldTeacherId);
                if ($oldTeacherUser) {
                    $oldTeacherUser->removeRole('Homeroom Teacher');
                }
            }
        }

        // Grant role to the new teacher
        if ($newTeacherId) {
            $newTeacherUser = User::find($newTeacherId);
            if ($newTeacherUser) {
                $newTeacherUser->assignRole('Homeroom Teacher');
            }
        }
    }
}
