<?php

namespace App\Observers;

use App\Models\Department;
use App\Models\User;

class DepartmentObserver
{
    /**
     * Handle the Department "saved" event.
     */
    public function saved(Department $department): void
    {
        if ($department->wasChanged('head_id')) {
            $this->syncRoles($department->getOriginal('head_id'), $department->head_id);
        }
    }

    /**
     * Handle the Department "created" event.
     */
    public function created(Department $department): void
    {
        if ($department->head_id) {
            $this->syncRoles(null, $department->head_id);
        }
    }

    /**
     * Handle the Department "deleted" event.
     */
    public function deleted(Department $department): void
    {
        if ($department->head_id) {
            $this->syncRoles($department->head_id, null);
        }
    }

    private function syncRoles(?int $oldHeadId, ?int $newHeadId): void
    {
        // If there was an old head, check if they are head of any other department. If not, remove role.
        if ($oldHeadId) {
            $stillHead = Department::where('head_id', $oldHeadId)->exists();
            if (!$stillHead) {
                $oldHeadUser = User::find($oldHeadId);
                if ($oldHeadUser) {
                    $oldHeadUser->removeRole('Department Head');
                }
            }
        }

        // Grant role to the new head
        if ($newHeadId) {
            $newHeadUser = User::find($newHeadId);
            if ($newHeadUser) {
                $newHeadUser->assignRole('Department Head');
            }
        }
    }
}
