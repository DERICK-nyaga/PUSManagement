<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;


class EmployeeChangeLog extends Model
{
    protected $fillable = [
        'employee_id',
        'changed_by',
        'old_data',
        'new_data',
        'changed_fields',
        'change_type',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'rejected_at',
        'notes',
        'approval_flow',
        'current_approval_level',
        'approval_history',
        'requires_multilevel_approval',
        'escalated_at',
        'escalated_to',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'changed_fields' => 'array',
        'approval_flow' => 'array',
        // 'approval_history' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    // FIXED: Return Collection instead of array
    public function addApprovalHistoryRecord(array $record): void
    {
        // Debug: Check what type we have
        $history = $this->approval_history;
        Log::info('History type: ' . gettype($history));
        Log::info('Is array?', ['is_array' => is_array($history)]);
        Log::info('Is Collection?', ['is_collection' => $history instanceof Collection]);
        Log::info('History value:', ['value' => $history]);

        // Convert to Collection if it's an array
        if (is_array($history)) {
            $history = collect($history);
        }

        // Now we can use push()
        $history->push($record);

        // Save back (mutator will handle conversion)
        $this->approval_history = $history;
        $this->save();
    }
    // FIXED: Handle both array and Collection
    public function setApprovalHistoryAttribute($value): void
    {
        if ($value instanceof Collection) {
            $value = $value->toArray();
        } elseif (is_array($value)) {
            // Already an array
        } else {
            $value = [];
        }

        $this->attributes['approval_history'] = json_encode($value);
    }

    // FIXED: Use proper Collection methods
    // public function addApprovalHistoryRecord(array $record): void
    // {
    //     $history = $this->approval_history; // This now returns a Collection
    //     $history->push($record); // Works because $history is a Collection
    //     $this->approval_history = $history;
    //     $this->save();
    // }

    // Alternative: Simpler method without using push()
    public function addApprovalRecord(array $record): void
    {
        $history = $this->approval_history ?? collect([]);
        $history = $history->toArray(); // Convert to array
        $history[] = $record; // Add to array
        $this->approval_history = $history; // Setter will convert to JSON
        $this->save();
    }

    // Helper method to get approvers from history
    public function getApproversFromHistory(): Collection
    {
        return $this->approval_history->pluck('approver_id')->filter();
    }

    // Helper method to check if user has approved
    public function hasUserApproved($userId): bool
    {
        return $this->approval_history->contains('approver_id', $userId);
    }

    // Helper to get approvals at a specific level
    public function getApprovalsAtLevel(int $level): Collection
    {
        return $this->approval_history->where('level', $level);
    }

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pendingApproval()
    {
        return $this->morphOne(PendingApproval::class, 'approvable');
    }

    public function pendingApprovals()
    {
        return $this->morphMany(PendingApproval::class, 'approvable');
    }

    public function scopeWhereApprover($query, $userId)
    {
        return $query->whereJsonContains('approval_history', [['approver_id' => $userId]]);
    }

    public function scopeWhereApprovalLevel($query, $level)
    {
        return $query->whereJsonContains('approval_history', [['level' => $level]]);
    }
}
