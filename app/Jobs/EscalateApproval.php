<?php

namespace App\Jobs;

use App\Models\EmployeeChangeLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use App\Services\EnhancedEmployeeChangeService;

class EscalateApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $changeLog;

    public function __construct(EmployeeChangeLog $changeLog)
    {
        $this->changeLog = $changeLog;
    }

    public function handle(EnhancedEmployeeChangeService $service): void
    {
        // Check if still pending
        if ($this->changeLog->status === 'pending') {
            $service->escalateApproval($this->changeLog);
        }
    }
}
