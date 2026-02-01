<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InternetPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dueDate = $this->due_date ? \Carbon\Carbon::parse($this->due_date) : null;
        $today = \Carbon\Carbon::today();

        // Safely handle total_due which might be null
        $totalDue = (float) ($this->total_due ?? 0);

        return [
            'id' => $this->id,
            'account_number' => $this->account_number,
            'amount' => (float) ($this->amount ?? 0),
            'previous_balance' => (float) ($this->previous_balance ?? 0),
            'total_due' => $totalDue,
            'formatted_total_due' => 'KES ' . number_format($totalDue, 2), // Fixed line
            'billing_month' => $this->billing_month,
            'formatted_billing_month' => $this->billing_month ? \Carbon\Carbon::parse($this->billing_month)->format('M Y') : null,
            'due_date' => $this->due_date,
            'formatted_due_date' => $dueDate ? $dueDate->format('M d, Y') : null,
            'payment_date' => $this->payment_date,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'mpesa_receipt' => $this->mpesa_receipt,
            'transaction_id' => $this->transaction_id,
            'invoice_notes' => $this->invoice_notes,
            'is_overdue' => $dueDate ? $dueDate->lt($today) && $this->status === 'pending' : false,
            'days_overdue' => $dueDate && $dueDate->lt($today) && $this->status === 'pending'
                ? $today->diffInDays($dueDate)
                : 0,
            'days_until_due' => $dueDate && $dueDate->gte($today) && $this->status === 'pending'
                ? $today->diffInDays($dueDate)
                : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Relationships
            'station' => $this->whenLoaded('station', function () {
                return [
                    'id' => $this->station->id,
                    'name' => $this->station->name,
                    'code' => $this->station->code,
                    'contact_person' => $this->station->contact_person,
                    'contact_phone' => $this->station->contact_phone,
                    'contact_email' => $this->station->contact_email,
                ];
            }),

            'provider' => $this->whenLoaded('provider', function () {
                if (!$this->provider) return null;

                return [
                    'id' => $this->provider->id,
                    'name' => $this->provider->name,
                    'paybill_number' => $this->provider->paybill_number,
                    'account_number' => $this->provider->account_number,
                    'support_contact' => $this->provider->support_contact,
                    'billing_email' => $this->provider->billing_email,
                ];
            }),

            // Links
            'links' => [
                'show' => route('api.internet-payments.show', $this->id),
                'update' => route('api.internet-payments.update', $this->id),
                'mark_paid' => route('api.internet-payments.mark-paid', $this->id),
                'send_reminder' => route('api.internet-payments.send-reminder', $this->id),
            ]
        ];
    }
}
