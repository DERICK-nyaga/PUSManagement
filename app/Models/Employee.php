<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Station;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'station_id',
        'employee_id',
        'position',
        'salary',
        'hire_date',
        'status',
        'deduction_balance'
    ];
    public function station()
    {
        return $this->belongsTo(Station::class);
    }
    public function deductionTransactions()
    {
        return $this->hasMany(DeductionTransaction::class)->orderBy('transaction_date', 'desc');
    }
    public function deductionBalance()
    {
        return $this->hasOne(DeductionBalance::class);
    }
    public function getCurrentBalanceAttribute()
    {
        return $this->deductionBalance()->firstOrCreate([], ['balance' => 0])->balance;
    }
    public function updateBalance()
    {
        $balance = $this->deductionTransactions()->sum('amount');

        $this->deductionBalance()->updateOrCreate(
            ['employee_id' => $this->id],
            ['balance' => $balance]
        );

        return $balance;
    }
}
