<?php

namespace App\Jobs;

use App\Models\EmployeeChangeLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Mail;

class SendApprovalReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $changeLog;

    public function __construct(EmployeeChangeLog $changeLog)
    {
        $this->changeLog = $changeLog;
    }

    public function handle(): void
    {
        $pendingApprovals = $this->changeLog->pendingApprovals()
            ->where('status', 'pending')
            ->where('reminder_sent_at', null)
            ->get();

        foreach ($pendingApprovals as $approval) {
            $approval->update([
                'reminder_sent_at' => now(),
                'reminder_count' => $approval->reminder_count + 1,
            ]);

            // Send reminder email
            Mail::to($approval->approver->email)
                ->send(new \App\Mail\ApprovalReminderMail($approval));
        }
    }
}
