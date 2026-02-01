<?php

// app/Jobs/SendApprovalNotification.php
namespace App\Jobs;

use App\Models\{PendingApproval, EmailTemplate};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Mail;
use App\Mail\ApprovalRequestMail;

class SendApprovalNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $pendingApproval;

    public function __construct(PendingApproval $pendingApproval)
    {
        $this->pendingApproval = $pendingApproval;
    }

    public function handle(): void
    {
        $approver = $this->pendingApproval->approver;
        $changeLog = $this->pendingApproval->approvable;
        $employee = $changeLog->employee;
        $requester = $this->pendingApproval->requester;

        // Get email template
        $template = EmailTemplate::where('type', 'approval_request')
            ->where('is_active', true)
            ->first();

        if ($template) {
            $data = [
                'approver_name' => $approver->name,
                'requester_name' => $requester->name,
                'employee_name' => $employee->full_name,
                'employee_id' => $employee->employee_id,
                'change_type' => $changeLog->change_type,
                'changed_fields' => implode(', ', array_keys($changeLog->changed_fields)),
                'approval_link' => route('approvals.pending'),
                'deadline' => $this->pendingApproval->deadline->format('Y-m-d H:i'),
                'approval_level' => $this->pendingApproval->approval_level,
            ];

            $parsed = $template->parseTemplate($data);

            Mail::to($approver->email)
                ->send(new ApprovalRequestMail($parsed['subject'], $parsed['body']));
        }

        // Also send in-app notification
        $approver->notify(new \App\Notifications\ApprovalRequested($this->pendingApproval));
    }
}


