<?php

// namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InternetPaymentCollection;
use App\Http\Resources\InternetPaymentResource;
use App\Models\InternetPayment;
use App\Models\Station;
use App\Models\InternetProvider;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InternetPaymentController extends Controller
{
    /**
     * Get internet payments due summary (overdue and due soon)
     *
     * @OA\Get(
     *     path="/api/internet-payments/due-summary",
     *     tags={"Internet Payments"},
     *     summary="Get internet payments due summary",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="station_id",
     *         in="query",
     *         description="Filter by station ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="provider_id",
     *         in="query",
     *         description="Filter by provider ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="stats", type="object",
     *                     @OA\Property(property="overdue_count", type="integer", example=5),
     *                     @OA\Property(property="overdue_amount", type="number", format="float", example=12500.00),
     *                     @OA\Property(property="due_soon_count", type="integer", example=10),
     *                     @OA\Property(property="due_soon_amount", type="number", format="float", example=25000.00),
     *                     @OA\Property(property="total_due", type="number", format="float", example=37500.00),
     *                     @OA\Property(property="overdue_by_provider", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="due_soon_by_station", type="array", @OA\Items(type="object"))
     *                 ),
     *                 @OA\Property(property="overdue", type="array", @OA\Items(ref="#/components/schemas/InternetPaymentResource")),
     *                 @OA\Property(property="due_soon_grouped", type="object",
     *                     @OA\Property(property="Today", type="array", @OA\Items(ref="#/components/schemas/InternetPaymentResource")),
     *                     @OA\Property(property="Tomorrow", type="array", @OA\Items(ref="#/components/schemas/InternetPaymentResource")),
     *                     @OA\Property(property="This Week", type="array", @OA\Items(ref="#/components/schemas/InternetPaymentResource"))
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function dueSummary(Request $request)
    {
        $today = Carbon::today();
        $endOfWeek = $today->copy()->endOfWeek();
        $sevenDaysFromNow = $today->copy()->addDays(7);

        // Base query for overdue payments
        $overdueQuery = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '<', $today->format('Y-m-d'));

        // Base query for due soon payments
        $dueSoonQuery = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '>=', $today->format('Y-m-d'))
            ->where('due_date', '<=', $sevenDaysFromNow->format('Y-m-d'));

        // Apply filters if provided
        if ($request->has('station_id')) {
            $stationId = $request->station_id;
            $overdueQuery->where('station_id', $stationId);
            $dueSoonQuery->where('station_id', $stationId);
        }

        if ($request->has('provider_id')) {
            $providerId = $request->provider_id;
            $overdueQuery->where('vendor_id', $providerId);
            $dueSoonQuery->where('vendor_id', $providerId);
        }

        // Get overdue payments
        $overdue = $overdueQuery->orderBy('due_date')->get();

        // Get due soon payments
        $dueSoon = $dueSoonQuery->orderBy('due_date')->get();

        // Group due soon payments by timeframe
        $dueSoonGrouped = $dueSoon->groupBy(function ($payment) use ($today) {
            $dueDate = Carbon::parse($payment->due_date);

            if ($dueDate->isToday()) {
                return 'Today';
            } elseif ($dueDate->isTomorrow()) {
                return 'Tomorrow';
            } elseif ($dueDate->lte($today->copy()->endOfWeek())) {
                return 'This Week';
            } else {
                return 'Next Week';
            }
        });

        // Calculate statistics
        $stats = [
            'overdue_count' => $overdue->count(),
            'overdue_amount' => (float) $overdue->sum('total_due') ?? 0,
            'due_soon_count' => $dueSoon->count(),
            'due_soon_amount' => (float) $dueSoon->sum('total_due') ?? 0,
            'total_due' => (float) ($overdue->sum('total_due') + $dueSoon->sum('total_due')) ?? 0,
            'overdue_by_provider' => $overdue->groupBy('vendor_id')->map(function ($payments, $providerId) {
                $provider = InternetProvider::find($providerId);
                return [
                    'provider_id' => $providerId,
                    'provider_name' => $provider ? $provider->name : 'Unknown',
                    'count' => $payments->count(),
                    'total_amount' => $payments->sum('total_due'),
                ];
            })->values(),
            'due_soon_by_station' => $dueSoon->groupBy('station_id')->map(function ($payments, $stationId) {
                $station = Station::find($stationId);
                return [
                    'station_id' => $stationId,
                    'station_name' => $station ? $station->name : 'Unknown',
                    'count' => $payments->count(),
                    'total_amount' => $payments->sum('total_due'),
                ];
            })->values(),
        ];

        return response()->json([
            'data' => [
                'stats' => $stats,
                'overdue' => InternetPaymentResource::collection($overdue),
                'due_soon_grouped' => $dueSoonGrouped->map(function ($payments) {
                    return InternetPaymentResource::collection($payments);
                }),
            ]
        ]);
    }

    /**
     * Get only overdue internet payments
     */
    public function overdue(Request $request)
    {
        $query = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '<', Carbon::today()->format('Y-m-d'))
            ->orderBy('due_date');

        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->has('provider_id')) {
            $query->where('vendor_id', $request->provider_id);
        }

        $perPage = $request->get('per_page', 20);
        $payments = $query->paginate($perPage);

        return new InternetPaymentCollection($payments);
    }

    /**
     * Get only due soon internet payments (next 7 days)
     */
    public function dueSoon(Request $request)
    {
        $today = Carbon::today();
        $sevenDaysFromNow = $today->copy()->addDays(7);

        $query = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '>=', $today->format('Y-m-d'))
            ->where('due_date', '<=', $sevenDaysFromNow->format('Y-m-d'))
            ->orderBy('due_date');

        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->has('provider_id')) {
            $query->where('vendor_id', $request->provider_id);
        }

        $perPage = $request->get('per_page', 20);
        $payments = $query->paginate($perPage);

        return new InternetPaymentCollection($payments);
    }

    /**
     * Display a listing of all internet payments
     */
    public function index(Request $request)
    {
        $query = InternetPayment::with(['station', 'provider'])
            ->orderBy('due_date', 'desc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->has('provider_id')) {
            $query->where('vendor_id', $request->provider_id);
        }

        if ($request->has('month')) {
            $query->where('billing_month', 'like', $request->month . '%');
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('mpesa_receipt', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $payments = $query->paginate($perPage);

        return new InternetPaymentCollection($payments);
    }

    /**
     * Store a newly created internet payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'vendor_id' => 'required|exists:internet_providers,vendor_id',
            'account_number' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
            'billing_month' => 'required|date',
            'due_date' => 'required|date|after_or_equal:today',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'mpesa_receipt' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
            'invoice_notes' => 'nullable|string',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
        ]);

        $payment = InternetPayment::create($validated);

        return response()->json([
            'message' => 'Internet payment created successfully',
            'data' => new InternetPaymentResource($payment->load(['station', 'provider']))
        ], 201);
    }

    /**
     * Display the specified internet payment
     */
    public function show(InternetPayment $internetPayment)
    {
        $internetPayment->load(['station', 'provider']);
        return new InternetPaymentResource($internetPayment);
    }

    /**
     * Update the specified internet payment
     */
    public function update(Request $request, InternetPayment $internetPayment)
    {
        $validated = $request->validate([
            'station_id' => 'sometimes|required|exists:stations,station_id',
            'vendor_id' => 'sometimes|required|exists:internet_providers,vendor_id',
            'account_number' => 'sometimes|required|string|max:100',
            'amount' => 'sometimes|required|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
            'billing_month' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date',
            'payment_date' => 'nullable|date',
            'status' => 'sometimes|required|in:pending,paid,overdue,cancelled',
            'mpesa_receipt' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
            'invoice_notes' => 'nullable|string',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
        ]);

        $internetPayment->update($validated);

        return response()->json([
            'message' => 'Internet payment updated successfully',
            'data' => new InternetPaymentResource($internetPayment->load(['station', 'provider']))
        ]);
    }

    /**
     * Remove the specified internet payment
     */
    public function destroy(InternetPayment $internetPayment)
    {
        $internetPayment->delete();

        return response()->json([
            'message' => 'Internet payment deleted successfully'
        ]);
    }

    /**
     * Mark internet payment as paid
     */
    public function markPaid(Request $request, InternetPayment $internetPayment)
    {
        $validated = $request->validate([
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
            'mpesa_receipt' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $internetPayment->update([
            'status' => 'paid',
            'payment_date' => $validated['payment_date'] ?? Carbon::today()->format('Y-m-d'),
            'payment_method' => $validated['payment_method'] ?? 'M-Pesa',
            'mpesa_receipt' => $validated['mpesa_receipt'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Internet payment marked as paid',
            'data' => new InternetPaymentResource($internetPayment->load(['station', 'provider']))
        ]);
    }

    /**
     * Send reminder for internet payment
     */
    public function sendReminder(InternetPayment $internetPayment)
    {
        // Implement your reminder logic here
        // This could send email, SMS, or create notification

        // $message = "Reminder: Internet bill for {$internetPayment->station->name} is due on "
        //     . Carbon::parse($internetPayment->due_date)->format('M d, Y')
        //     . ". Amount: KES " . number_format($internetPayment->total_due, 2);

            $totalDue = (float) ($internetPayment->total_due ?? 0);
    $formattedAmount = number_format($totalDue, 2);

    $message = "Reminder: Internet bill for {$internetPayment->station->name} is due on "
        . Carbon::parse($internetPayment->due_date)->format('M d, Y')
        . ". Amount: KES " . $formattedAmount;
        // Log the reminder (you can implement actual notification sending)
        activity()
            ->performedOn($internetPayment)
            ->withProperties(['reminder_sent_at' => now()])
            ->log('Payment reminder sent');

        return response()->json([
            'message' => 'Reminder sent successfully',
            'reminder_message' => $message,
            'sent_at' => now()->toDateTimeString()
        ]);
    }

    /**
     * Bulk mark payments as paid
     */
    public function bulkMarkPaid(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:internet_payments,id',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
        ]);

        $count = InternetPayment::whereIn('id', $request->payment_ids)
            ->update([
                'status' => 'paid',
                'payment_date' => $request->payment_date ?? Carbon::today()->format('Y-m-d'),
                'payment_method' => $request->payment_method ?? 'M-Pesa',
            ]);

        return response()->json([
            'message' => "{$count} payments marked as paid",
            'updated_count' => $count
        ]);
    }

    /**
     * Bulk send reminders
     */
public function bulkSendReminders(Request $request)
{
    $request->validate([
        'payment_ids' => 'required|array',
        'payment_ids.*' => 'exists:internet_payments,id',
    ]);

    $payments = InternetPayment::with('station')
        ->whereIn('id', $request->payment_ids)
        ->get();

    $reminders = [];
    foreach ($payments as $payment) {
        // Safely format total_due
        $totalDue = (float) ($payment->total_due ?? 0);
        $formattedAmount = number_format($totalDue, 2);

        $message = "Reminder: Internet bill for {$payment->station->name} is due on "
            . Carbon::parse($payment->due_date)->format('M d, Y')
            . ". Amount: KES " . $formattedAmount; // Fixed line

        $reminders[] = [
            'payment_id' => $payment->id,
            'station_name' => $payment->station->name,
            'amount' => $totalDue,
            'formatted_amount' => $formattedAmount,
            'due_date' => $payment->due_date,
            'reminder_message' => $message,
            'sent_at' => now()->toDateTimeString()
        ];
    }

    // Log bulk reminder activity
    activity()
        ->withProperties([
            'payment_ids' => $request->payment_ids,
            'reminders_sent' => count($reminders),
            'sent_at' => now()
        ])
        ->log('Bulk payment reminders sent');

    return response()->json([
        'message' => "Reminders sent for " . count($reminders) . " payments",
        'reminders' => $reminders
    ]);
}
    /**
     * Get internet due payments for a specific station
     */
    public function stationInternetDue($stationId, Request $request)
    {
        $station = Station::findOrFail($stationId);

        $today = Carbon::today();
        $sevenDaysFromNow = $today->copy()->addDays(7);

        // Overdue payments for this station
        $overdue = InternetPayment::with('provider')
            ->where('station_id', $stationId)
            ->where('status', 'pending')
            ->where('due_date', '<', $today->format('Y-m-d'))
            ->orderBy('due_date')
            ->get();

        // Due soon payments for this station
        $dueSoon = InternetPayment::with('provider')
            ->where('station_id', $stationId)
            ->where('status', 'pending')
            ->where('due_date', '>=', $today->format('Y-m-d'))
            ->where('due_date', '<=', $sevenDaysFromNow->format('Y-m-d'))
            ->orderBy('due_date')
            ->get();

        // Group due soon payments
        $dueSoonGrouped = $dueSoon->groupBy(function ($payment) use ($today) {
            $dueDate = Carbon::parse($payment->due_date);

            if ($dueDate->isToday()) {
                return 'Today';
            } elseif ($dueDate->isTomorrow()) {
                return 'Tomorrow';
            } elseif ($dueDate->lte($today->copy()->addDays(3))) {
                return 'Within 3 Days';
            } else {
                return 'This Week';
            }
        });

        $stats = [
            'station' => [
                'id' => $station->id,
                'name' => $station->name,
                'code' => $station->code,
            ],
            'overdue_count' => $overdue->count(),
            'overdue_amount' => (float) $overdue->sum('total_due') ?? 0,
            'due_soon_count' => $dueSoon->count(),
            'due_soon_amount' => (float) $dueSoon->sum('total_due') ?? 0,
            'total_due' => (float) ($overdue->sum('total_due') + $dueSoon->sum('total_due')) ?? 0,
        ];

        return response()->json([
            'data' => [
                'station' => $station,
                'stats' => $stats,
                'overdue' => InternetPaymentResource::collection($overdue),
                'due_soon_grouped' => $dueSoonGrouped->map(function ($payments) {
                    return InternetPaymentResource::collection($payments);
                }),
            ]
        ]);
    }
}
