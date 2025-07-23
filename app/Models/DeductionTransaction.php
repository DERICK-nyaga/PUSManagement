<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class DeductionTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'amount',
        'type',
        'reason',
        'notes',
        'order_number',
        'transaction_date',
        // 'user_id'
    ];

    protected $dates = ['transaction_date'];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public const TYPES = [
        'lose' => 'Lost item',
        'past SLA' => 'breached delivery date',
        'absence' => 'Absence',
        'loan' => 'Loan Deduction',
        'other' => 'Other Deduction'
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getTypeLabelAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
