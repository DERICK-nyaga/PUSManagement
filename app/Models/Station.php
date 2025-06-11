<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    protected $fillable = ['name', 'location', 'monthly_loss', 'deductions'];

    // Define the relationship with employees
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Define the relationship with payments
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}