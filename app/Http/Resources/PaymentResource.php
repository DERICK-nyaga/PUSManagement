<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'amount' => (float) $this->amount,
            'formatted_amount' => number_format($this->amount, 2),
            'due_date' => $this->due_date->toDateString(),
            'formatted_due_date' => $this->due_date->format('M d, Y'),
            'status' => $this->status,
            'type' => $this->type,
            'description' => $this->description,
            'reference_number' => $this->reference_number,
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at?->toDateString(),
            'formatted_paid_at' => $this->paid_at?->format('M d, Y'),
            'is_overdue' => $this->isOverdue(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Relationships
            'station' => $this->whenLoaded('station', function () {
                return [
                    'id' => $this->station->id,
                    'name' => $this->station->name,
                    'code' => $this->station->code,
                ];
            }),

            'vendor' => $this->whenLoaded('vendor', function () {
                if (!$this->vendor) return null;

                return [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->name,
                    'contact_person' => $this->vendor->contact_person,
                    'phone' => $this->vendor->phone,
                    'email' => $this->vendor->email,
                ];
            }),

            // Links
            'links' => [
                'show' => route('api.payments.show', $this->id),
                'update' => route('api.payments.update', $this->id),
                'destroy' => route('api.payments.destroy', $this->id),
                'mark_as_paid' => route('api.payments.mark-as-paid', $this->id),
                'mark_as_cancelled' => route('api.payments.mark-as-cancelled', $this->id),
            ]
        ];
    }
}
