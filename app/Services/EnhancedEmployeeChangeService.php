<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\EmployeeChangeLog;
use App\Models\PendingApproval;
use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\{Auth, DB, Mail};
use Carbon\Carbon;

class EnhancedEmployeeChangeService
{
    // Helper methods
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

    // Add the missing method
    private function requestSingleApproval(EmployeeChangeLog $changeLog): void
    {
        // Get the default approver (HR Manager or department head)
        $approver = $this->getDefaultApprover($changeLog);

        if ($approver) {
            PendingApproval::create([
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
                'deadline' => now()->addHours(24), // 24-hour deadline
            ]);
        }
    }

    private function getDefaultApprover(EmployeeChangeLog $changeLog): ?User
    {
        // First try to get HR manager
        $hrManager = User::whereHas('roles', function ($query) {
            $query->where('name', 'hr_manager');
        })->first();

        if ($hrManager) {
            return $hrManager;
        }

        // If no HR manager, get the employee's supervisor
        $employee = $changeLog->employee;
        if ($employee && $employee->reporting_to) {
            $supervisor = User::find($employee->reporting_to);
            if ($supervisor) {
                return $supervisor;
            }
        }

        // Fallback to admin user
        return User::where('email', 'admin@company.com')->first();
    }

    // Other methods from your original implementation
    private function determineApprovalFlow(EmployeeProfile $employee, array $changes, string $changeType): ?array
    {
        // Simple implementation - adjust based on your needs
        if ($this->isSensitiveChange($changes)) {
            return [
                [
                    'level' => 1,
                    'approvers' => [
                        [
                            'user_id' => $employee->reporting_to ?? 1,
                            'name' => 'Supervisor',
                            'role' => 'supervisor',
                        ]
                    ],
                    'required' => true,
                    'deadline_hours' => 24,
                ],
                [
                    'level' => 2,
                    'approvers' => [
                        [
                            'user_id' => 1, // HR Manager ID
                            'name' => 'HR Manager',
                            'role' => 'hr_manager',
                        ]
                    ],
                    'required' => true,
                    'deadline_hours' => 48,
                ]
            ];
        }

        return null;
    }

    private function initiateMultiLevelApproval(EmployeeChangeLog $changeLog): void
    {
        $approvalFlow = $changeLog->approval_flow;
        $firstLevel = $approvalFlow[0] ?? null;

        if ($firstLevel && !empty($firstLevel['approvers'])) {
            foreach ($firstLevel['approvers'] as $approver) {
                $this->createPendingApproval($changeLog, $approver['user_id'], 1, $firstLevel);
            }
        }
    }

    private function createPendingApproval(
        EmployeeChangeLog $changeLog,
        int $approverId,
        int $level,
        array $levelConfig
    ): PendingApproval {

        $deadline = now()->addHours($levelConfig['deadline_hours'] ?? 24);

        return PendingApproval::create([
            'approvable_type' => EmployeeChangeLog::class,
            'approvable_id' => $changeLog->id,
            'type' => 'employee_update',
            'data' => [
                'employee_id' => $changeLog->employee_id,
                'change_type' => $changeLog->change_type,
                'changed_fields' => $changeLog->changed_fields,
                'approval_level' => $level,
            ],
            'requested_by' => Auth::id(),
            'approver_id' => $approverId,
            'status' => 'pending',
            'approval_level' => $level,
            'deadline' => $deadline,
        ]);
    }

    public function logChange(
        EmployeeProfile $employee,
        array $oldData,
        array $newData,
        string $changeType,
        $notes = null,
        bool $requiresMultiLevel = false
    ): EmployeeChangeLog {

        $changedFields = $this->getChangedFields($oldData, $newData);

        $approvalFlow = $requiresMultiLevel
            ? $this->determineApprovalFlow($employee, $changedFields, $changeType)
            : null;

        $changeLog = EmployeeChangeLog::create([
            'employee_id' => $employee->id,
            'changed_by' => Auth::id(),
            'old_data' => $oldData,
            'new_data' => $newData,
            'changed_fields' => $changedFields,
            'change_type' => $changeType,
            'status' => 'pending',
            'notes' => $notes,
            'approval_flow' => $approvalFlow,
            'requires_multilevel_approval' => $requiresMultiLevel,
            'current_approval_level' => 1,
            'approval_history' => [],
        ]);

        if ($requiresMultiLevel && $approvalFlow) {
            $this->initiateMultiLevelApproval($changeLog);
        } else {
            $this->requestSingleApproval($changeLog);
        }

        return $changeLog;
    }

    // Add other methods you need (approveChange, rejectChange, etc.)
    public function approveChange(EmployeeChangeLog $changeLog, User $approver, ?string $comments = null): bool
    {
        return DB::transaction(function () use ($changeLog, $approver, $comments) {

            // Get current approval history
            $history = $changeLog->approval_history ?? [];
            $history[] = [
                'level' => $changeLog->current_approval_level,
                'approver_id' => $approver->id,
                'approver_name' => $approver->name,
                'approved_at' => now()->toDateTimeString(),
                'comments' => $comments,
            ];

            $changeLog->update([
                'approval_history' => $history,
            ]);

            // Mark pending approval as approved
            $pendingApproval = $changeLog->pendingApprovals()
                ->where('approver_id', $approver->id)
                ->where('status', 'pending')
                ->first();

            if ($pendingApproval) {
                $pendingApproval->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'comments' => $comments,
                ]);
            }

            // Check if all approvals for current level are complete
            if ($this->isApprovalLevelComplete($changeLog)) {
                if ($this->hasNextApprovalLevel($changeLog)) {
                    $this->moveToNextApprovalLevel($changeLog);
                } else {
                    // All levels approved - apply changes
                    $this->applyChanges($changeLog);
                    $changeLog->update([
                        'status' => 'approved',
                        'approved_by' => $approver->id,
                        'approved_at' => now(),
                    ]);
                }
            }

            return true;
        });
    }

    private function isApprovalLevelComplete(EmployeeChangeLog $changeLog): bool
    {
        $currentLevel = $changeLog->current_approval_level;
        $approvalFlow = $changeLog->approval_flow[$currentLevel - 1] ?? null;

        if (!$approvalFlow) return true;

        $requiredApprovals = $approvalFlow['required'] ?? true;

        if (!$requiredApprovals) {
            // This level is optional, check if any approval is received
            return collect($changeLog->approval_history)
                ->where('level', $currentLevel)
                ->isNotEmpty();
        }

        // Count pending approvals for this level
        $pendingCount = $changeLog->pendingApprovals()
            ->where('approval_level', $currentLevel)
            ->where('status', 'pending')
            ->count();

        return $pendingCount === 0;
    }

    private function hasNextApprovalLevel(EmployeeChangeLog $changeLog): bool
    {
        return isset($changeLog->approval_flow[$changeLog->current_approval_level]);
    }

    private function moveToNextApprovalLevel(EmployeeChangeLog $changeLog): void
    {
        $nextLevel = $changeLog->current_approval_level + 1;
        $levelConfig = $changeLog->approval_flow[$nextLevel - 1] ?? null;

        if ($levelConfig && !empty($levelConfig['approvers'])) {
            $changeLog->update(['current_approval_level' => $nextLevel]);

            foreach ($levelConfig['approvers'] as $approver) {
                $this->createPendingApproval($changeLog, $approver['user_id'], $nextLevel, $levelConfig);
            }
        }
    }

    private function applyChanges(EmployeeChangeLog $changeLog): void
    {
        $employee = $changeLog->employee;
        $employee->fill($changeLog->new_data);
        $employee->save();
    }

    public function rejectChange(EmployeeChangeLog $changeLog, User $approver, string $reason): bool
    {
        return DB::transaction(function () use ($changeLog, $approver, $reason) {
            $changeLog->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'rejected_at' => now(),
            ]);

            $changeLog->pendingApprovals()->update([
                'status' => 'rejected',
                'comments' => $reason,
                'rejected_at' => now(),
            ]);

            return true;
        });
    }

    // Add other missing methods as needed
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
