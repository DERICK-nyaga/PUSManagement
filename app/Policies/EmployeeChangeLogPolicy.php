<?php

namespace App\Policies;

use App\Models\EmployeeChangeLog;
use App\Models\User;

class EmployeeChangeLogPolicy
{
    public function approve(User $user, EmployeeChangeLog $changeLog): bool
    {
        // Only HR managers and department heads can approve changes
        return $user->hasRole(['hr_manager', 'department_head'])
            || $user->id === $changeLog->employee->reporting_to;
    }

    public function view(User $user, EmployeeChangeLog $changeLog): bool
    {
        // Users can view changes they made or if they're HR/management
        return $user->id === $changeLog->changed_by
            || $user->hasRole(['hr_manager', 'department_head'])
            || $user->id === $changeLog->employee->reporting_to;
    }
}
