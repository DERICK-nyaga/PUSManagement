<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Station;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $stations = Station::all();
        $vendors = Vendor::all();
        $types = config('payment.types');

        return view('payments.create', compact('stations', 'vendors', 'types'));
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
            'type' => 'required|in:' . implode(',', array_keys(config('payment.types'))),
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'is_recurring' => 'boolean',
            'recurrence' => 'required_if:is_recurring,true|in:weekly,monthly,yearly',
            'recurrence_ends_at' => 'nullable|date|after:due_date'
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'pending';

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('payments/attachments');
        }

        $payment = Payment::create($validated);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment created successfully!');
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
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'type' => 'required|in:' . implode(',', array_keys(config('payment.types'))),
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'is_recurring' => 'boolean',
            'recurrence' => 'required_if:is_recurring,true|in:weekly,monthly,yearly',
            'recurrence_ends_at' => 'nullable|date|after:due_date'
        ]);

        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
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

    // Additional actions
    public function approve(Payment $payment)
    {
        $payment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
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
    // app/Models/Payment.php
public function isOverdue()
{
    return $this->due_date->isPast() && $this->status != 'paid';
}
public function statusBadgeColor()
{
    return match($this->status) {
        'pending' => 'warning',
        'approved' => 'info',
        'paid' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}
}
