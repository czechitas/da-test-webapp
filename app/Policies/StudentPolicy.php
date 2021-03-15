<?php

declare(strict_types=1);

namespace CzechitasApp\Policies;

use CzechitasApp\Models\Student;
use CzechitasApp\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of students.
     */
    public function parentList(User $user): bool
    {
        return $user->isRoleParent();
    }

    /**
     * Determine whether the user can view the list of students.
     */
    public function list(User $user): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can view the student.
     */
    public function view(User $user, Student $student): bool
    {
        if ($user->isAdminOrMore()) {
            return true;
        }

        return $user->isRoleParent() && $user->id === $student->parent_id && $student->isViewable();
    }

    /**
     * Determine whether the user can view the email history
     */
    public function sendEmails(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->isAdminOrMore() || $user->isRoleParent();
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        return $user->isAdminOrMore() || ($this->view($user, $student) && $student->isEditable());
    }

    /**
     * Determine whether the user can log out the student.
     */
    public function logout(User $user, Student $student): bool
    {
        return false;
        // return $user->isAdminOrMore() || ($this->view($user, $student) && $student->isPossibleLogOut());
    }

    /**
     * Determine whether the user can cancel the student.
     */
    public function cancel(User $user, Student $student): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can add payments of the student.
     */
    public function addPayment(User $user, Student $student): bool
    {
        return $user->isRoleMaster();
    }

    /**
     * Determine whether the user can download Login certificate
     */
    public function certificateLogin(User $user, Student $student): bool
    {
        return !$student->canceled && $this->view($user, $student);
    }

    /**
     * Determine whether the user can download Payment certificate
     */
    public function certificatePayment(User $user, Student $student): bool
    {
        return $this->certificateLogin($user, $student)
            && $student->price_to_pay <= 0;
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        return false;
    }
}
