<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     * Super Admin has unrestricted access to everything.
     * Admin permissions are granted by Super Admin (not automatic bypass).
     * Principal has full access to student management.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Super Admin has unrestricted access
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Principal has full access to student management
        if ($user->hasRole('Principal')) {
            return true;
        }

        // Admin permissions are set by Super Admin - fall through to specific checks
        return null;
    }

    /**
     * Determine whether the user can view any students.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Teacher', 'Registrar', 'Counselor']);
    }

    /**
     * Determine whether the user can view the student.
     */
    public function view(User $user, Student $student): bool
    {
        // Teachers can view students in their assigned sections
        if ($user->hasRole('Teacher')) {
            return $this->isTeacherOfStudent($user, $student);
        }

        // Parents can view their own children
        if ($user->hasRole('Parent')) {
            return $this->isParentOfStudent($user, $student);
        }

        // Students can view themselves
        if ($user->hasRole('Student')) {
            return $user->id === $student->user_id;
        }

        return $user->hasAnyRole(['Registrar', 'Counselor']);
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Registrar']);
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        return $user->hasAnyRole(['Admin', 'Registrar']);
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        // Admin can soft delete, Super Admin handled by before()
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can restore the student.
     */
    public function restore(User $user, Student $student): bool
    {
        return false; // Only admins via before()
    }

    /**
     * Determine whether the user can permanently delete the student.
     */
    public function forceDelete(User $user, Student $student): bool
    {
        return false; // Only super admins via before()
    }

    /**
     * Determine if the user can view student grades.
     */
    public function viewGrades(User $user, Student $student): bool
    {
        if ($user->hasRole('Teacher')) {
            return $this->isTeacherOfStudent($user, $student);
        }

        if ($user->hasRole('Parent')) {
            return $this->isParentOfStudent($user, $student);
        }

        if ($user->hasRole('Student')) {
            return $user->id === $student->user_id;
        }

        return $user->hasAnyRole(['Registrar', 'Counselor']);
    }

    /**
     * Determine if user can edit student grades.
     */
    public function editGrades(User $user, Student $student): bool
    {
        if ($user->hasRole('Teacher')) {
            return $this->isTeacherOfStudent($user, $student);
        }

        return false;
    }

    /**
     * Check if teacher is assigned to a section containing this student.
     */
    private function isTeacherOfStudent(User $user, Student $student): bool
    {
        // Get current enrollment section
        $enrollment = $student->enrollments()
            ->whereNull('end_date')
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return false;
        }

        // Check if teacher is assigned to this section
        // This assumes a teacher_assignments table or similar
        // For now, we'll use a simplified check
        return \DB::table('teacher_assignments')
            ->where('user_id', $user->id)
            ->where('section_id', $enrollment->section_id)
            ->exists();
    }

    /**
     * Check if user is a parent/guardian of the student.
     */
    private function isParentOfStudent(User $user, Student $student): bool
    {
        return $student->guardians()
            ->where('email', $user->email)
            ->exists();
    }
}
