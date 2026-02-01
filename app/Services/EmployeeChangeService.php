<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\EmployeeChangeLog;
use App\Models\PendingApproval;
use App\Models\User;
use App\Traits\ChangeServiceHelpers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeChangeService
{
        use ChangeServiceHelpers;
    public function logChange(EmployeeProfile $employee, array $oldData, array $newData, string $changeType, $notes = null): EmployeeChangeLog
    {
        $changedFields = $this->getChangedFields($oldData, $newData);

        return EmployeeChangeLog::create([
            'employee_id' => $employee->id,
            'changed_by' => Auth::id(),
            'old_data' => $oldData,
            'new_data' => $newData,
            'changed_fields' => $changedFields,
            'change_type' => $changeType,
            'status' => 'pending',
            'notes' => $notes,
        ]);
    }

    public function requestApproval(EmployeeChangeLog $changeLog, User $approver): PendingApproval
    {
        return PendingApproval::create([
            'approvable_type' => EmployeeChangeLog::class,
            'approvable_id' => $changeLog->id,
            'type' => 'employee_update',
            'data' => [
                'employee_id' => $changeLog->employee_id,
                'change_type' => $changeLog->change_type,
                'changed_fields' => $changeLog->changed_fields,
            ],
            'requested_by' => Auth::id(),
            'approver_id' => $approver->id,
            'status' => 'pending',
        ]);
    }

    public function approveChange(EmployeeChangeLog $changeLog, User $approver): bool
    {
        return DB::transaction(function () use ($changeLog, $approver) {
            // Update the employee with new data
            $employee = $changeLog->employee;
            $employee->fill($changeLog->new_data);
            $employee->save();

            // Update change log
            $changeLog->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Update pending approval
            $changeLog->pendingApproval?->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return true;
        });
    }

    public function rejectChange(EmployeeChangeLog $changeLog, User $approver, string $reason): bool
    {
        return DB::transaction(function () use ($changeLog, $approver, $reason) {
            $changeLog->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'rejected_at' => now(),
            ]);

            $changeLog->pendingApproval?->update([
                'status' => 'rejected',
                'comments' => $reason,
                'rejected_at' => now(),
            ]);

            return true;
        });
    }

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

    public function getEmployeeChangeHistory(EmployeeProfile $employee, array $filters = [])
    {
        $query = $employee->changeLogs()
            ->with(['changer', 'approver'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['change_type'])) {
            $query->where('change_type', $filters['change_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate(20);
    }
}
