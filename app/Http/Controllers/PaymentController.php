<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Station;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\InternetPayment;
use App\Models\AirtimePayment;
use App\Models\PaymentSchedule;
use App\Models\InternetProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureIsAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['station', 'vendor'])
            ->upcoming()
            ->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $types = config('payment.types');

        return view('payments.create', [
            'stations' => Station::all(),
            'vendors' => Vendor::all(),
            'default_due_date' => now()->addDays(30)->format('Y-m-d'),
            'statuses' => ['pending', 'approved', 'paid', 'rejected']
        ], compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'status' => 'required|in:pending,approved,paid,rejected',
            'type' => 'required|in:utility,service,product,other',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'is_recurring' => 'required|boolean',
            'recurrence' => 'required_if:is_recurring,true|in:weekly,monthly,yearly',
            'recurrence_ends_at' => 'nullable|date|after:due_date'
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('payment-attachments');
        }

        $validated['created_by'] = Auth::id();
        if(Auth::user()->role === 'admin' && $request->status === 'approved'){
            $validated['approved_by'] = Auth::id();
            $validated['approved_at'] = now();
        }

        try {
            $payment = Payment::create($validated);
            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating payment: '.$e->getMessage());
        }
    }

    public function show(Payment $payment)
    {
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $stations = Station::all();
        $vendors = Vendor::all();
        $types = config('payment.types');

        return view('payments.edit', compact('payment', 'stations', 'vendors', 'types'));
    }

    public function update(Request $request, Payment $payment)
    {
        $user = Auth::user();
        abort_unless($user !== null , 403, 'Unauthorized access');
        abort_unless(Gate::allows('update', $payment), 403, 'You are not authorized to update this payment');
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,approved,paid,rejected',
            'type' => 'required|in:' . implode(',', array_keys(config('payment.types'))),
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'is_recurring' => 'boolean',
            'recurrence' => 'required_if:is_recurring,true|in:weekly,monthly,yearly',
            'recurrence_ends_at' => 'nullable|date|after:due_date'
        ]);

        if ($request->hasFile('attachment')) {
            if ($payment->attachment_path) {
                Storage::delete($payment->attachment_path);
            }
            $validated['attachment_path'] = $request->file('attachment')->store('payments/attachments');
        }

        $payment->update($validated);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment updated successfully!');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->attachment_path) {
            Storage::delete($payment->attachment_path);
        }

        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully!');
    }

    public function approve(Payment $payment)
    {
        $payment->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return back()->with('success', 'Payment approved!');
    }

    public function markAsPaid(Payment $payment)
    {
        $payment->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return back()->with('success', 'Payment marked as paid!');
    }

      public function stationPayments($stationId)
    {
        $station = Station::with(['internetPayments.provider', 'airtimePayments'])->findOrFail($stationId);

        return view('payments.station', compact('station'));
    }

    public function indexInternetPayments(Request $request)
    {
        $query = InternetPayment::with(['station', 'provider']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->filled('month')) {
            // Parse the month and get the first day
            $month = Carbon::parse($request->month . '-01')->format('Y-m-d');
            $query->where('billing_month', $month);
        }

        // Search by account number
        if ($request->filled('search')) {
            $query->where('account_number', 'like', '%' . $request->search . '%');
        }

        $payments = $query->orderBy('due_date', 'desc')->paginate(20);

        // Get stations and providers for filters
        $stations = Station::all();
        $providers = InternetProvider::all();

        return view('payments.internet.index', compact('payments', 'stations', 'providers'));
    }

    // Internet Payment CRUD
    public function createInternetPayment()
    {
        $stations = Station::all();
        $providers = InternetProvider::all();
        return view('payments.internet.create', compact('stations', 'providers'));
    }

    // Generate payment reminder for specific payment
    public function sendReminder($paymentId)
    {
        $payment = InternetPayment::with(['station', 'provider'])->findOrFail($paymentId);

        // Send email if you have the Mail class set up
        // Mail::to($payment->station->contact_email)
        //     ->cc($payment->provider->billing_email)

        //     ->send(new InternetPaymentReminder($payment));

        // Send SMS if configured
        if (config('services.sms.enabled')) {
            $this->sendSmsReminder($payment);
        }

        return redirect()->back()
            ->with('success', 'Reminder sent successfully!');
    }

    private function sendSmsReminder(InternetPayment $payment)
    {
        $message = $payment->generateReminderMessage();

        // This would integrate with your SMS gateway
        // Example: SMSProvider::send($payment->station->contact_phone, $message);
    }

    //all payments due soon
    public function getDueSoonPayments()
    {
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addWeek();

        $dueSoon = InternetPayment::whereBetween('due_date', [$today->format('Y-m-d'), $nextWeek->format('Y-m-d')])
            ->where('status', 'pending')
            ->with(['station', 'provider'])
            ->orderBy('due_date')
            ->get()
            ->map(function($payment) use ($today) {
                // Use Carbon instance for date operations
                $dueDate = $payment->due_date instanceof Carbon ? $payment->due_date : Carbon::parse($payment->due_date);

                return [
                    'id' => $payment->id,
                    'station' => $payment->station->name,
                    'provider' => $payment->provider->name,
                    'account_number' => $payment->account_number,
                    'amount_due' => $payment->total_due,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'formatted_due_date' => $dueDate->format('d/m/Y'),
                    'contact_person' => $payment->station->contact_person,
                    'contact_phone' => $payment->station->contact_phone,
                    'contact_email' => $payment->station->contact_email,
                    'paybill_number' => $payment->provider->paybill_number,
                    'support_contact' => $payment->provider->support_contact,
                    'is_due_today' => $dueDate->isToday(),
                    'days_until_due' => $today->diffInDays($dueDate),
                    'reminder_message' => $payment->generateReminderMessage()
                ];
            });

        return view('payments.due-soon', compact('dueSoon'));
    }

        public function upcomingPayments()
    {
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addWeek();

        $upcomingInternet = InternetPayment::whereBetween('due_date', [$today->format('Y-m-d'), $nextWeek->format('Y-m-d')])
            ->where('status', 'pending')
            ->with(['station', 'provider'])
            ->orderBy('due_date')
            ->get();

        $upcomingAirtime = AirtimePayment::whereBetween('expected_expiry', [$today->format('Y-m-d'), $nextWeek->format('Y-m-d')])
            ->where('status', 'active')
            ->with('station')
            ->orderBy('expected_expiry')
            ->get();

        return view('payments.upcoming', compact('upcomingInternet', 'upcomingAirtime'));
    }

    public function overduePayments()
    {
        $today = Carbon::today();

        $overdueInternet = InternetPayment::where('due_date', '<', $today->format('Y-m-d'))
            ->whereIn('status', ['pending', 'overdue'])
            ->with(['station', 'provider'])
            ->orderBy('due_date')
            ->get();

        $overdueAirtime = AirtimePayment::where('expected_expiry', '<', $today->format('Y-m-d'))
            ->where('status', 'active')
            ->with('station')
            ->orderBy('expected_expiry')
            ->get();

        return view('payments.overdue', compact('overdueInternet', 'overdueAirtime'));
    }
    public function editInternetPayment($id)
    {
        $payment = InternetPayment::findOrFail($id);
        $stations = Station::all();
        $providers = InternetProvider::all();

        return view('payments.internet.edit', compact('payment', 'stations', 'providers'));
    }

    public function updateInternetPayment(Request $request, $id)
    {
        $payment = InternetPayment::findOrFail($id);

        $validated = $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'vendor_id' => 'required|exists:internet_providers,vendor_id',
            'account_number' => 'required|unique:internet_payments,account_number,' . $id,
            'amount' => 'required|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
            'billing_month' => 'required|date',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'mpesa_receipt' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'invoice_notes' => 'nullable|string',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque'
        ]);

        // Ensure numeric values
        $amount = (float) $validated['amount'];
        $previousBalance = isset($validated['previous_balance']) ? (float) $validated['previous_balance'] : 0.00;
        // DO NOT calculate total_due - let the database handle it
        // $totalDue = $amount + $previousBalance;

        $payment->update([
            'station_id' => $validated['station_id'],
            'vendor_id' => $validated['vendor_id'],
            'account_number' => $validated['account_number'],
            'amount' => $amount,
            'previous_balance' => $previousBalance,
            // 'total_due' => $totalDue, // REMOVE THIS LINE
            'billing_month' => Carbon::parse($validated['billing_month'])->format('Y-m-d'),
            'due_date' => Carbon::parse($validated['due_date'])->format('Y-m-d'),
            'payment_date' => $validated['payment_date'] ? Carbon::parse($validated['payment_date'])->format('Y-m-d') : null,
            'status' => $validated['status'],
            'mpesa_receipt' => $validated['mpesa_receipt'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'invoice_notes' => $validated['invoice_notes'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null
        ]);

        return redirect()->route('payments.internet.index')
            ->with('success', 'Internet payment updated successfully!');
    }

    public function destroyInternetPayment($id)
    {
        $payment = InternetPayment::findOrFail($id);
        $payment->delete();

        return redirect()->route('payments.internet.index')
            ->with('success', 'Internet payment deleted successfully!');
    }

    public function showPaymentDetails($id)
    {
        $payment = InternetPayment::with(['station', 'provider'])->findOrFail($id);

        if (request()->ajax()) {
            return view('payments.internet.partials.details', compact('payment'))->render();
        }

        return view('payments.internet.show', compact('payment'));
    }
    public function createAirtimePayment()
    {
        $stations = Station::all();

        // stats for the sidebar
        $activeTopups = AirtimePayment::where('status', 'active')->count();
        $expiringSoon = AirtimePayment::where('status', 'active')
            ->where('expected_expiry', '<=', Carbon::now()->addDays(3))
            ->count();
        $monthlyTotal = AirtimePayment::whereMonth('topup_date', Carbon::now()->month)
            ->sum('amount');

        // recent payments
        $recentPayments = AirtimePayment::with('station')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        //recent mobile numbers for suggestions
        $recentNumbers = AirtimePayment::select('mobile_number')
            ->distinct()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('mobile_number');

        return view('payments.airtime.create', compact(
            'stations',
            'activeTopups',
            'expiringSoon',
            'monthlyTotal',
            'recentPayments',
            'recentNumbers'
        ));
    }

    public function storeAirtimePayment(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'mobile_number' => 'required|string|max:15',
            'amount' => 'required|numeric|min:0',
            'topup_date' => 'required|date',
            'network_provider' => 'required|string|in:Safaricom,Airtel,Telkom,Faiba',
            'transaction_id' => 'nullable|string|unique:airtime_payments',
            'expected_expiry' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'auto_renew' => 'nullable|boolean',
            'send_notification' => 'nullable|boolean',
        ]);

        // Generate transaction ID
        if (empty($validated['transaction_id'])) {
            $validated['transaction_id'] = 'TXN' . date('YmdHis') . rand(100, 999);
        }

        // Set expected expiry
        if (empty($validated['expected_expiry'])) {
            $validated['expected_expiry'] = Carbon::parse($validated['topup_date'])->addDays(30);
        }

        // Set auto-renew and notification defaults
        $validated['auto_renew'] = $request->has('auto_renew');
        $validated['send_notification'] = $request->has('send_notification', true);

        $airtimePayment = AirtimePayment::create($validated);

        // Send notification
        if ($validated['send_notification']) {
            // e.g., send SMS or email to station contact
        }

        // Redirect based on action
        if ($request->input('action') === 'save_and_new') {
            return redirect()->route('payments.airtime.create')
                ->with('success', 'Airtime payment saved successfully!')
                ->with('old_data', $validated);
        }

        return redirect()->route('payments.airtime.index')
            ->with('success', 'Airtime payment saved successfully!');
    }
    public function storeInternetPayment(Request $request)
    {
        Log::info('=== STORE INTERNET PAYMENT DEBUG ===');
        Log::info('Request data:', $request->all());

        $validated = $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'vendor_id' => 'required|exists:internet_providers,vendor_id',
            'account_number' => 'required',
            'amount' => 'required|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
            'billing_month' => 'required|date',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'mpesa_receipt' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'invoice_notes' => 'nullable|string',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'create_schedule' => 'nullable|boolean'
        ]);

        Log::info('Validated data:', $validated);

        // Remove total_due in case it got into the request
        if (array_key_exists('total_due', $validated)) {
            Log::warning('total_due found in validated data! Removing it.');
            unset($validated['total_due']);
        }

        $provider = InternetProvider::find($validated['vendor_id']);

        $paymentData = [
            'station_id' => $validated['station_id'],
            'vendor_id' => $validated['vendor_id'],
            'account_number' => $validated['account_number'],
            'amount' => (float) $validated['amount'],
            'previous_balance' => isset($validated['previous_balance']) ? (float) $validated['previous_balance'] : 0.00,
            'billing_month' => Carbon::parse($validated['billing_month'])->format('Y-m-d'),
            'due_date' => Carbon::parse($validated['due_date'])->format('Y-m-d'),
            'payment_date' => $validated['payment_date'] ? Carbon::parse($validated['payment_date'])->format('Y-m-d') : null,
            'mpesa_receipt' => $validated['mpesa_receipt'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'status' => $validated['status'],
            'invoice_notes' => $validated['invoice_notes'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null
        ];

        Log::info('Payment data being saved (without total_due):', $paymentData);

        try {
            $payment = InternetPayment::create($paymentData);
            Log::info('Payment created successfully. ID: ' . $payment->id);

            $savedPayment = InternetPayment::find($payment->id);
            Log::info('Saved payment data:', [
                'id' => $savedPayment->id,
                'amount' => $savedPayment->amount,
                'previous_balance' => $savedPayment->previous_balance,
                'total_due' => $savedPayment->total_due,
                'calculated' => $savedPayment->amount + $savedPayment->previous_balance
            ]);

            if ($request->boolean('create_schedule')) {
                PaymentSchedule::create([
                    'station_id' => $validated['station_id'],
                    'payment_type' => 'internet',
                    'scheduled_date' => Carbon::parse($validated['due_date'])->subDays(7)->format('Y-m-d'),
                    'scheduled_amount' => $validated['amount'],
                    'frequency' => 'monthly',
                    'is_recurring' => true,
                    'description' => "Monthly internet payment to {$provider->name} for account {$validated['account_number']}",
                    'vendor_id' => $provider->vendor_id
                ]);
            }

            return redirect()->route('payments.internet.index')
                ->with('success', 'Internet payment recorded successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating payment: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function indexAirtimePayments(Request $request)
    {
        // update expired payments before first
        $this->updateExpiredAirtimePayments();

        $query = AirtimePayment::with('station');

        // filters
        if ($request->filled('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->filled('network_provider')) {
            $query->where('network_provider', $request->network_provider);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $month = $request->month;
            $query->whereYear('topup_date', substr($month, 0, 4))
                ->whereMonth('topup_date', substr($month, 5, 2));
        }

        // Search by mobile number
        if ($request->filled('search')) {
            $query->where('mobile_number', 'like', '%' . $request->search . '%');
        }

        $payments = $query->orderBy('expected_expiry', 'desc')->paginate(20);

        //stations for filters
        $stations = Station::all();

        return view('payments.airtime.index', compact('payments', 'stations'));
    }

    public function showAirtimeDetails($id)
    {
        $payment = AirtimePayment::with('station')->findOrFail($id);

        $payment->updateStatusIfExpired();
        if ($payment->isDirty()) {
            $payment->save();
        }

        if (request()->ajax()) {
            return view('payments.airtime.partials.details', compact('payment'))->render();
        }

        return view('payments.airtime.show', compact('payment'));
    }

    private function updateExpiredAirtimePayments()
    {
        $today = Carbon::today();

        // Find all active payments where expected_expiry is in the past
        $expiredPayments = AirtimePayment::where('status', 'active')
            ->where('expected_expiry', '<', $today->format('Y-m-d'))
            ->get();

        foreach ($expiredPayments as $payment) {
            $payment->status = 'expired';
            $payment->save();
        }

        if ($expiredPayments->count() > 0) {
            Log::info('Updated ' . $expiredPayments->count() . ' airtime payments to expired status.');
        }
    }

    public function renewAirtimePayment($id)
    {
        $payment = AirtimePayment::findOrFail($id);

        // Redirect to create page with pre-filled data
        return redirect()->route('payments.airtime.create')
            ->with('old_data', [
                'station_id' => $payment->station_id,
                'mobile_number' => $payment->mobile_number,
                'network_provider' => $payment->network_provider,
                'notes' => "Renewal of previous payment #{$payment->id}"
            ]);
    }

    public function destroyAirtimePayment($id)
    {
        $payment = AirtimePayment::findOrFail($id);

        // Only allow deletion of expired payments
        if ($payment->status != 'expired') {
            return back()->with('error', 'Only expired payments can be deleted.');
        }

        $payment->delete();

        return redirect()->route('payments.airtime.index')
            ->with('success', 'Expired payment deleted successfully.');
    }

    // Payment Schedules
    public function indexSchedules()
    {
        $schedules = PaymentSchedule::with('station')
            ->orderBy('scheduled_date')
            ->paginate(20);

        return view('payments.schedules.index', compact('schedules'));
    }

    public function createSchedule()
    {
        $stations = Station::all();
        return view('payments.schedules.create', compact('stations'));
    }

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'payment_type' => 'required|in:internet,airtime',
            'scheduled_date' => 'required|date',
            'scheduled_amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,yearly,custom',
            'is_recurring' => 'boolean',
            'auto_pay' => 'boolean',
            'description' => 'nullable|string'
        ]);

        PaymentSchedule::create($validated);

        return redirect()->route('payments.schedules.index')
            ->with('success', 'Payment schedule created successfully!');
    }
}
