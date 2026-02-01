<?php

namespace App\Http\Controllers;

use App\Models\{PendingApproval, EmployeeChangeLog, EmployeeProfile};
use App\Services\EnhancedEmployeeChangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected $changeService;

    public function __construct(EnhancedEmployeeChangeService $changeService)
    {
        $this->changeService = $changeService;
    }

    public function pending(Request $request)
    {
        $query = PendingApproval::where('status', 'pending')
            ->where('approver_id', Auth::id())
            ->with(['approvable.employee', 'requester'])
            ->orderBy('created_at', 'desc');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('priority')) {
            $query->where('deadline', '<=', now()->addHours(12));
        }

        $pendingApprovals = $query->paginate(20);

        return view('approvals.pending', compact('pendingApprovals'));
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'change_log_ids' => 'required|array',
            'change_log_ids.*' => 'exists:employee_change_logs,id',
            'comments' => 'nullable|string|max:500',
        ]);

        $results = $this->changeService->bulkApproveChanges(
            $request->change_log_ids,
            Auth::user(),
            $request->comments
        );

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => 'Bulk approval completed',
        ]);
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'change_log_ids' => 'required|array',
            'change_log_ids.*' => 'exists:employee_change_logs,id',
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        $results = $this->changeService->bulkRejectChanges(
            $request->change_log_ids,
            Auth::user(),
            $request->rejection_reason
        );

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => 'Bulk rejection completed',
        ]);
    }

    public function reports(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'change_type', 'department']);

        $report = $this->changeService->generateApprovalReport($filters);

        $departments = EmployeeProfile::distinct('department')->pluck('department');
        $changeTypes = EmployeeChangeLog::distinct('change_type')->pluck('change_type');

        return view('approvals.reports', compact('report', 'departments', 'changeTypes', 'filters'));
    }

    public function exportReport(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'change_type', 'department']);
        $report = $this->changeService->generateApprovalReport($filters);

        return Excel::download(new ApprovalReportExport($report), 'approval-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function dashboard()
    {
        $stats = [
            'pending_my_approval' => PendingApproval::where('approver_id', Auth::id())
                ->where('status', 'pending')
                ->count(),

            'overdue_approvals' => PendingApproval::where('approver_id', Auth::id())
                ->where('status', 'pending')
                ->where('deadline', '<', now())
                ->count(),

            'recently_approved' => EmployeeChangeLog::whereHas('approval_history', function ($query) {
                $query->where('approver_id', Auth::id());
            })
            ->where('status', 'approved')
            ->where('approved_at', '>', now()->subDays(7))
            ->count(),

            'avg_approval_time' => $this->changeService->calculateAverageApprovalTime(
                EmployeeChangeLog::where('status', 'approved')
                    ->where('approved_at', '>', now()->subDays(30))
                    ->get()
            ),
        ];

        $upcomingDeadlines = PendingApproval::where('approver_id', Auth::id())
            ->where('status', 'pending')
            ->where('deadline', '>', now())
            ->where('deadline', '<=', now()->addHours(24))
            ->with('approvable.employee')
            ->orderBy('deadline')
            ->limit(10)
            ->get();

        return view('pending-approvals-dashboard', compact('stats', 'upcomingDeadlines'));
    }
}
