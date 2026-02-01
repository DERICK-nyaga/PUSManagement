<?php

namespace App\Traits;

use App\Models\EmployeeChangeLog;
use App\Models\User;
trait ChangeServiceHelpers
{
    private function getChangedFields(array $oldData, array $newData): array
    {
        $changed = [];
        foreach ($newData as $key => $value) {
            if (!array_key_exists($key, $oldData) || $oldData[$key] != $value) {
                $changed[$key] = [
                    'old' => $oldData[$key] ?? null,
                    'new' => $value,
                ];
            }
        }
        return $changed;
    }

    private function isSensitiveChange(array $changes): bool
    {
        $sensitiveFields = ['basic_salary', 'job_title', 'department', 'status', 'employment_type'];
        return !empty(array_intersect(array_keys($changes), $sensitiveFields));
    }

    private function canApprove(EmployeeChangeLog $changeLog, User $user): bool
    {
        // Your approval logic here
        return $user->hasRole(['hr_manager', 'department_head'])
            || $user->id === $changeLog->employee->reporting_to;
    }
}
